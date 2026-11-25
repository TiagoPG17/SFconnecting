# Rótulo de salida de materia prima por costal — Contexto completo

> Documento de traspaso. Explica cómo funciona hoy el rótulo en SGP, qué piden cambiar y cómo se va a construir la solución **aislada de SGP**.
> Fecha: 2026-11-25.

---

## 1. Resumen

- **Qué es:** la etiqueta que imprime bodega cuando entrega materia prima a producción (documentos **SLI**).
- **Problema:** SGP imprime **una etiqueta por material** con el **peso total**. Bodega entrega el material en **costales** y necesita **una etiqueta por costal**, numerada `1/N … N/N` y con **el peso de ese costal**.
- **Decisión:** **no modificar SGP.** Se construye una herramienta aparte que lee la base de datos de SGP (solo lectura), arma las etiquetas y las envía directo a la impresora Zebra.
- **Ya validado:** una consulta SQL que reproduce **exactamente** los datos de la etiqueta actual (sección 4).

---

## 2. Contexto del sistema SGP

### Stack
- Django 4.2, **Python 3.12** en producción, servido con **waitress** (sin recarga automática: cualquier cambio de código exige reiniciar).
- Base de datos **SQL Server anterior a 2017**: no existen `STRING_AGG` ni `TRIM`. Usar `STUFF(... FOR XML PATH(''))` y `LTRIM(RTRIM())`.
- Las impresoras son **Zebra** y reciben **ZPL crudo por TCP en el puerto 9100**.

### Servidor de producción
- SGP corre en `172.20.1.5:8500`. Ruta: `C:\ambientes\dj42p310\sgpgitlab\sgp\`.
- **No hay código fuente `.py` de las apps, solo `.pyc` sin fuente.** No hay git.
- El código local (copia de desarrollo) **no coincide** con lo desplegado: tiene cambios que nunca se subieron y le faltan parches que sí están en producción.
- Cambiar lógica en producción implica recompilar con Python 3.12 desde un fuente idéntico a lo desplegado, lo cual hoy no es confiable. Además obliga a reiniciar SGP y sacar a todos los usuarios.
- El PowerShell del servidor es la versión 4.0 (los bloques con here-strings pegados han fallado).
- El proyecto en general está desordenado: vistas de miles de líneas, formatos ZPL copiados y pegados, JS compartido por unas 20 pantallas y sin control de caché.

**Por todo esto la solución se construye por fuera de SGP.**

---

## 3. Cómo funciona hoy el rótulo de salida de MP en SGP

### Flujo
```
Pantalla de captura de inventario (botón imprimir, radio "Imprimir Rótulo: Sí")
  → JS impr_salida()            inventar/static/inventar/js/invcrd.js (~línea 3035)
  → GET /inventar/listneedmat/?numdoc=<SLI>&improt=si&impre=<IP impresora>
  → vista vwinv_mostrarimpr_saldoc   inventar/views.py (~línea 3601)
       1. Lee el documento (inv_movto) y sus renglones (inv_movtodet)
       2. Busca la orden de producción, el cliente y la fórmula
       3. Calcula preparaciones (no se usa para la etiqueta)
       4. Por cada renglón arma un ZPL y lo envía a la Zebra (~líneas 3975-4069)
```
- La usan dos pantallas: `ppalcapinv.html` (captura) y `ppalordinv.html` (órdenes, botón `btnprintb`).
- La misma vista devuelve los datos para llenar la pantalla **y además** imprime.

### Comportamiento actual
- **Una etiqueta por renglón** (material) del documento. `ROT: x/y` significa "renglón x de y renglones". No cuenta costales ni copias.
- `CANTIDAD` es el **total** del renglón.
- Cada etiqueta se envía con `^PQ1`, es decir, una sola copia.
- **No existe ningún campo** para indicar cuántas etiquetas imprimir.
- **No guarda** cuántas se imprimieron (`inv_movtodet.bolimpreso` queda en 0; lo marca otra vista aparte).
- Cada clic en imprimir reimprime todo desde cero. Si bodega necesita 3 etiquetas, da 3 clics y salen 3 idénticas con `ROT: 1/1`.
- **SGP no tiene ningún dato de empaque** (costales, kg por saco) ni en `inv_articulo`, ni en lotes, ni en saldos. Solo maneja peso o unidad y lote.

### Envío a la impresora en SGP (`inicio/modsecurity.py`, función `ipimpzpl2`)
```python
s = socket.socket(AF_INET, SOCK_STREAM)
s.connect((ip, 9100)); time.sleep(1)
s.send(texto.encode('cp850')); time.sleep(1)   # en producción es cp850; en local aún dice iso-8859-1
s.close()
```
- Son unos **2 segundos por etiqueta**, abriendo una conexión por etiqueta.
- **cp850** es la codificación correcta: las Zebra usan `^CI0`, y con cp850 imprimen bien Á, É, Í, Ó, Ú y Ñ. Esto se comprobó en producción.

---

## 4. Datos de la etiqueta y consulta validada

### De dónde sale cada campo
| Campo en la etiqueta | Origen |
|---|---|
| PEDIDO | `inv_movtodet.docref` |
| DOCUMENTO | `inv_movtodet.iddocto` |
| FORMULA | `frm_formula.codformula`, buscada por `idparte` e `idproducto` de la orden, con `idestado = '2'` (GENERADA) |
| Cliente (renglón 3) | `vw_clientes.razonsocial`, cruzando `vw_clientes.id = pcc_orden_produccion.nit` |
| COLOR / MATERIAL | `pcc_orden_produccion.colorpart` / `materialpart` |
| PROCESO | `pcc_operaciones.descripcion`, por `inv_movtodet.idproceso_ruta` |
| CAPA | `ini_tipos_datos.descriptipo`, por `inv_movtodet.capa_id` |
| REF | `inv_movtodet.articulo` |
| Nombre del material | `inv_articulo.descripart` |
| CANTIDAD + unidad | `inv_movtodet.cantidad_art` + `inv_movtodet.unidad` |
| LOTE | últimos 10 caracteres de `inv_movtodet.parte_lote` |
| Fecha | fecha y hora de impresión (`dd/mm/aaaa`) |

- Cómo se encuentra la orden: `inv_movto.descrip` contiene el `nroorden` de `pcc_orden_produccion`. El cruce con el documento es `inv_movto.nrodoc = inv_movtodet.iddocto`.
- Orden de los renglones: `capa_id DESC, articulo`.

### Consulta SQL (validada)
```sql
DECLARE @doc VARCHAR(16) = 'SLI299428';

SELECT d.id_row,
       d.docref                 AS pedido,
       d.iddocto                AS documento,
       f.formula,
       c.razonsocial            AS cliente,
       o.colorpart              AS color,
       o.materialpart           AS material,
       op.descripcion           AS proceso,
       t.descriptipo            AS capa,
       d.articulo               AS ref,
       a.descripart             AS nombre_material,
       d.cantidad_art           AS cantidad,
       d.unidad,
       RIGHT(d.parte_lote, 10)  AS lote
FROM inv_movtodet d
JOIN inv_movto m                  ON m.nrodoc = d.iddocto
LEFT JOIN pcc_orden_produccion o  ON o.nroorden = m.descrip
LEFT JOIN vw_clientes c           ON c.id = o.nit
LEFT JOIN pcc_operaciones op      ON op.id_row = d.idproceso_ruta
LEFT JOIN ini_tipos_datos t       ON t.id_row = d.capa_id
LEFT JOIN inv_articulo a          ON a.codarticulo = d.articulo
OUTER APPLY (SELECT MAX(ff.codformula) AS formula
             FROM frm_formula ff
             WHERE ff.idparte = o.idparte
               AND ff.idproducto = o.idproducto
               AND ff.idestado = '2') f
WHERE d.iddocto = @doc
ORDER BY d.capa_id DESC, d.articulo;
```

### Validación con el SLI299428 (coincide 100 % con la etiqueta impresa)
| id_row | pedido | documento | formula | cliente | color | material | proceso | capa | ref | nombre_material | cantidad | unidad | lote |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 833803 | PV37697-139852-0014111-2 | SLI299428 | 27661 | CLICK COSMETICS S.A.S. | BLANCO | PP | TRANSFORMACION | GENERAL | MP-011008 | POLIPROPILENO NO.RT100N C/R BRASKEM | 103000 | GRS | RSGHPG038E |

### Diferencias posibles con SGP en casos raros
- **Fórmula:** SGP toma la última fórmula GENERADA que devuelve la base, sin ordenar. La consulta usa `MAX(codformula)`. Si una orden tiene varias fórmulas generadas, hay que confirmar cuál debe salir.
- **Proceso:** si un renglón no tiene operación, SGP repite el proceso del renglón anterior por un error de código. La consulta deja ese campo vacío.

---

## 5. Formato ZPL actual (impresoras ZTC y S4M, etiqueta de 799 × 799 puntos)

SGP solo imprime esta etiqueta si el **nombre** de la impresora en `ini_impresoras` contiene `ZTC` o `S4M`. Existe otro formato para `QLn320`, pero el propio código lo marca como "ya no aplica, esta mala".

```
^XA~TA0~JSB^LT0^MMT^MNW^MTT^PON^PMN^LH0,0^JMA^PR4,4^MD0^JUS^LRN^CI0^XZ
^XA^LL0799
^PW799
^FT27,50 ^A0N,36,36^FH\^FDPEDIDO: {pedido}^FS
^FT27,95 ^A0N,26,26^FH\^FDDOCUMENTO: {documento}        FORMULA: {formula}^FS
^FT27,135^A0N,27,27^FH\^FD{cliente}^FS
^FT27,175^A0N,26,26^FH\^FDCOLOR: {color}   MATERIAL: {material}^FS
^FT27,215^A0N,26,26^FH\^FDPROCESO: {proceso}^FS
^FT27,260^A0N,36,36^FH\^FDCAPA: {capa}    REF:{ref}^FS
^FT27,305^A0N,36,36^FH\^FD{nombre_material}^FS
^FT27,350^A0N,36,36^FH\^FDCANTIDAD: {cantidad} {unidad}     LOTE:{lote}^FS
^FT27,390^A0N,23,23^FH\^FDROT: {n}/{total}                  {dd/mm/aaaa}^FS
^PQ1,0,1,Y^XZ
```
- SGP envía todo en una sola línea, sin saltos. Los saltos de arriba son solo para que se lea.
- La cantidad sale como `str(float(cantidad))`, por ejemplo `103000.0`.
- Por `^FH\`, una barra invertida `\` dentro de un dato se interpreta como código hexadecimal. Los caracteres `^` y `~` dentro de un dato rompen el ZPL, así que hay que limpiarlos o escaparlos.

### Impresoras (tabla `ini_impresoras`: `nomimpra`, `dirip`)
Valores conocidos (**por verificar** con `SELECT * FROM ini_impresoras`):
- Bodega: `172.20.3.245`
- CM: `172.20.3.249`
- INV: `172.20.3.248`

---

## 6. El requerimiento

Lo que dijo el área usuaria (resumen del chat):
> Esos rótulos se sacan cuando el material sale de bodega hacia producción. Si salen 3 costales, hoy se sacan 3 rótulos con la misma información. Se requiere que **cada bulto sea independiente** y que **cada ticket represente un solo bulto**: numerados `1/3, 2/3, 3/3`, y que **cada ticket tenga el peso de ese bulto, no el total**.

Ejemplo concreto: un SLI con 103.000 GRS de polipropileno entregado en costales de 25 kg debe sacar 5 etiquetas:

| ROT | CANTIDAD |
|---|---|
| 1/5 | 25000 GRS |
| 2/5 | 25000 GRS |
| 3/5 | 25000 GRS |
| 4/5 | 25000 GRS |
| 5/5 | 3000 GRS (saldo) |

La impresora no puede aportar esta información: SGP solo le envía datos y no lee nada de vuelta, y el contador interno de la Zebra es total (no distingue material ni documento).

---

## 7. Solución: herramienta aislada de SGP

### Principios
- **No se toca nada de SGP:** ni `.pyc`, ni plantillas, ni JS, ni reinicios.
- **Solo lectura** sobre la base de datos de SGP (usar un usuario de BD con permiso solo de lectura si es posible).
- La herramienta envía el ZPL directo a la Zebra por el puerto 9100.
- Si la herramienta falla, SGP sigue funcionando igual que hoy.

### Tecnología (pendiente de decidir)
- **Opción 1, n8n:** ya lo usan para generar el certificado de Belstar en paralelo a SGP. Hay que confirmar que pueda abrir una conexión TCP al puerto 9100; el nodo Code necesita que tengan habilitado el módulo `net` de Node (`NODE_FUNCTION_ALLOW_BUILTIN`).
- **Opción 2, una página pequeña en Python** (por ejemplo Flask o FastAPI con `pyodbc`), independiente de SGP.

### Flujo propuesto
1. Bodega abre la herramienta y escribe el número del SLI.
2. La herramienta ejecuta la consulta de la sección 4 y muestra los renglones: material, cantidad y unidad.
3. Por cada renglón, bodega escribe el **peso por costal**, en la **misma unidad** del renglón (mostrarla junto al campo).
4. La herramienta calcula y muestra un resumen: "Se imprimirán N rótulos: 4 de 25000 GRS y 1 de 3000 GRS".
5. Bodega confirma y escoge la impresora (o se usa la de bodega por defecto).
6. Se arma **un solo ZPL** con todas las etiquetas y se envía en **una sola conexión**.

### Cálculo de costales (opción A, recomendada)
```
T = cantidad del renglón, P = peso por costal (misma unidad)
si P <= 0            → error
si P >= T            → 1 etiqueta con T
completos = floor(T / P)
saldo     = T - completos * P
N         = completos + (1 si saldo > 0)
etiquetas i = 1..completos  → CANTIDAD = P
etiqueta  N (si hay saldo)  → CANTIDAD = saldo
ROT = i/N
```
- Usar `Decimal` para evitar errores de redondeo (`cantidad_art` tiene 5 decimales). La suma de las etiquetas debe dar exactamente T.
- Es la misma idea que ya usan los rótulos de despacho de SGP: cajas completas más una caja de saldo.

Alternativas descartadas por ahora:
- **B.** Digitar el número de costales y repartir el peso por igual (no refleja costales de peso estándar).
- **C.** Digitar el peso de cada costal uno por uno (exacto, pero lento).

### Validaciones obligatorias
- **Tope de etiquetas** por renglón y por documento (por ejemplo 100). La unidad suele ser GRS: si alguien escribe `25` pensando en kilos, saldrían 4.120 etiquetas.
- **Confirmación** con el total de etiquetas antes de enviar.
- Bloquear el botón mientras se envía, para evitar doble clic y etiquetas duplicadas.
- Limpiar `^`, `~` y `\` de los datos antes de meterlos en el ZPL.
- Codificar en **cp850**.
- Mostrar un error claro si el SLI no existe, no tiene renglones o la impresora no responde.

### Envío
```python
zpl = "".join(etiquetas)                 # cabecera de configuración + N bloques ^XA...^XZ
with socket.create_connection((ip, 9100), timeout=10) as s:
    s.sendall(zpl.encode("cp850"))
```
No hacen falta las esperas de 1 segundo que usa SGP.

---

## 8. Decisiones pendientes

1. **Tecnología:** n8n o página en Python.
2. **Cómo se define el peso del costal:** A (recomendada), B o C.
3. **¿Un SLI puede traer varios materiales?** Si sí, se digita el peso por costal para cada uno.
4. **Impresora:** fija (bodega `172.20.3.245`) o elegida de `ini_impresoras`.
5. **¿Se registra lo impreso?** Por ejemplo, una tabla propia de la herramienta (no de SGP) con SLI, renglón, N, usuario y fecha. Es opcional.
6. **Formato de la cantidad:** mantener `25000.0` como SGP o mostrar `25000`. ¿Agregar el total del material en letra pequeña?
7. **Fórmula** cuando una orden tiene varias GENERADAS (hoy `MAX(codformula)`).

---

## 9. Errores conocidos de la vista de SGP (para no copiarlos)

1. Si la orden **no tiene fórmula GENERADA**, la vista falla (la variable `afgrm` nunca se crea) y no imprime nada.
2. Si existe una fila en **`frm_prodtoformula`** para esa parte y producto, falla en `objtemprepform.formula_id` (se usa cuando todavía es `None`) y no imprime nada.
3. Si el SLI **no está ligado a una orden**, fallan `descli` y `afgrm` y no imprime nada.
4. Si un renglón no tiene operación, el proceso se arrastra del renglón anterior.
5. Cada etiqueta tarda unos 2 segundos (`sleep` al conectar y al enviar).

---

## 10. Plan de pruebas

1. **Impresora falsa:** un listener TCP en `127.0.0.1:9100` que guarde lo recibido en un archivo. Así se revisa el ZPL sin gastar etiquetas.
2. **Vista previa del ZPL:** con un visor de ZPL, usando **solo datos de prueba** si el visor es un servicio externo.
3. **Casos de cálculo:** exacto (100.000 / 25.000), con saldo (103.000 / 25.000), P > T, P = 0, P negativo, decimales, unidad distinta de GRS, tope superado.
4. **Casos de datos:** SLI inexistente, SLI con varios materiales, sin orden, sin fórmula, nombres con tildes y Ñ.
5. **Prueba real:** una sola etiqueta en la Zebra de bodega para comparar con la actual. Después, una tanda corta de prueba (por ejemplo 3 costales).

---

## 11. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Imprimir miles de etiquetas por un error de unidad | Tope, mostrar la unidad, confirmación con el total |
| Etiquetas duplicadas por doble clic | Bloquear el botón y enviar en una sola conexión |
| Tildes mal impresas | cp850 + `^CI0` (ya comprobado) |
| Datos distintos a los de SGP | La consulta ya fue validada; revisar los casos raros de fórmula y proceso |
| Cargar la BD de producción | Solo una consulta por documento, de solo lectura |
| Que bodega siga imprimiendo desde SGP | Comunicar el cambio. SGP sigue funcionando igual; no se rompe nada |
| Credenciales de BD | Usuario de solo lectura; no guardar credenciales en el código |

---

## 12. Glosario

- **SLI:** documento de salida de inventario (bodega → producción).
- **Costal o bulto:** saco físico en que se entrega el material. SGP no lo registra.
- **Renglón:** cada material dentro del documento (`inv_movtodet`).
- **Capa:** parte de la fórmula donde se usa el material (General, Interna, Externa…).
- **Fórmula:** receta de materiales de la parte y producto de la orden (`frm_formula`).
- **ROT x/y:** numeración de la etiqueta. Hoy es renglón/renglones; en la solución será costal/costales.
- **ZPL:** lenguaje de las impresoras Zebra. Se envía como texto plano por TCP al puerto 9100.
