# Runbook comercial CRM — Análisis de cartera y plan de acción

Documento ejecutable: consultas SQL organizadas por propósito, qué analizar en cada
caso, y cómo alimentar el prompt de Gemini con sus resultados.

---

## 0. El flujo completo (cómo encaja todo)

1. **Extraes** un segmento de cartera con una consulta SQL (según lo que quieras atacar).
2. **Exportas** el resultado (copiar de SSMS o a Excel/CSV).
3. **Rellenas** el prompt de Gemini con tus parámetros y pegas los datos.
4. Gemini devuelve un **plan de acción** priorizado.
5. (Opcional) **Cargas** ese plan de vuelta al CRM como `seguimientos` / `sf_negocios`.

Regla de oro de todo el runbook: **el horizonte de presupuesto manda**. Primero lo que
sostiene la meta del año (activos y en riesgo), después lo de largo plazo.

---

## ⚠️ Antes de ejecutar — alineación de columnas

La vista `vw_CRM_Clientes_Prioritarios` evolucionó. Si copiaste consultas viejas, cambia:

| Columna vieja (ya no existe) | Columna actual |
|---|---|
| `CLASIFICACION` | `PRIORIDAD_COMERCIAL` |
| `ACCION_SUGERIDA` | `ACCION_PRESUPUESTAL` |

Los valores de `PRIORIDAD_COMERCIAL` son los mismos textos ('1 - VIP ACTIVO…', etc.),
así que los filtros `IN (...)` siguen sirviendo; solo cambia el nombre de la columna.
Todas las consultas de este documento ya están corregidas.

---

## Mapa de consultas (para no perderse)

| Bloque | Consulta | Fuente | Para qué sirve |
|---|---|---|---|
| 1. Priorización | A — Atención inmediata | vw_CRM_Clientes_Prioritarios | VIP activos a cuidar/expandir |
| 1. Priorización | B — Rescate/reactivación | vw_CRM_Clientes_Prioritarios | Valiosos enfriándose o dormidos |
| 1. Priorización | C — Panorama por vendedor | vw_CRM_Clientes_Prioritarios | Distribución y % en riesgo |
| 1. Priorización | Integral — perfil cruzado | vw_CRM_Clientes_Prioritarios | Mono vs ambas compañías |
| 2. Horizonte ppto | Q1–Q5 | vw_CRM_Clientes_Prioritarios | Por horizonte presupuestal |
| 3. Pipeline | Recuperación / Expansión | CRM_Consolidado_Ventas_cliente | Crear negocios en el pipeline |
| 4. Tendencias | A–D | vw_CRM_Ventas_Mensuales_cliente | Monitoreo y variaciones |
| 4. Tendencias | Detalle por vendedor | vw_CRM_Detalle_Mensual_Vendedor | Drill-down de un vendedor/mes |

---

## BLOQUE 1 — Priorización de cartera (alimenta el prompt de Gemini)

### A) Atención inmediata — "Mis mejores clientes activos: cuidarlos"
**Qué analizar:** clientes que ya compran bien; el riesgo es perderlos por descuido.
Revisa ticket promedio y días sin comprar; son los candidatos ideales para **venta
integral cruzada** (subir ticket). → Alimenta la **Consulta A** del prompt.

```sql
SELECT
    NOMBRE_VENDEDOR, NIT, RAZON_SOCIAL, CIUDAD,
    NUM_FACTURAS,
    VLR_NETO_FACTURADO        AS facturado_historico,
    TICKET_PROMEDIO,
    ULTIMA_FACTURA,
    DIAS_DESDE_ULTIMA_COMPRA  AS dias_sin_comprar,
    PUNTAJE_PRIORIDAD,
    PRIORIDAD_COMERCIAL,
    ACCION_PRESUPUESTAL
FROM dbo.vw_CRM_Clientes_Prioritarios
WHERE PRIORIDAD_COMERCIAL IN (
        '1 - VIP ACTIVO: cuidar y expandir',
        '2 - URGENTE: cliente importante enfriándose')
ORDER BY NOMBRE_VENDEDOR, PUNTAJE_PRIORIDAD DESC, VLR_NETO_FACTURADO DESC;
```

### B) Rescate / reactivación — "Valiosos que se me escapan"
**Qué analizar:** llevan tiempo sin comprar pero tienen historia. Mira el monto histórico
para priorizar a quién rescatar primero; la venta integral puede ser el gancho que
reabra la relación. → Alimenta la **Consulta B** del prompt.

```sql
SELECT
    NOMBRE_VENDEDOR, NIT, RAZON_SOCIAL, CIUDAD,
    NUM_FACTURAS,
    VLR_NETO_FACTURADO        AS facturado_historico,
    TICKET_PROMEDIO,
    ULTIMA_FACTURA,
    DIAS_DESDE_ULTIMA_COMPRA  AS dias_sin_comprar,
    PUNTAJE_PRIORIDAD,
    PRIORIDAD_COMERCIAL,
    ACCION_PRESUPUESTAL
FROM dbo.vw_CRM_Clientes_Prioritarios
WHERE PRIORIDAD_COMERCIAL IN (
        '3 - RESCATE: contactar antes de perderlo',
        '4 - REACTIVACION: cliente dormido valioso')
ORDER BY NOMBRE_VENDEDOR, PUNTAJE_PRIORIDAD DESC, VLR_NETO_FACTURADO DESC;
```

### C) Panorama gerencial — "Vista de águila por vendedor"
**Qué analizar:** qué vendedores tienen más cartera en riesgo (% en riesgo), dónde está
el valor dormido, quién necesita apoyo. → Alimenta la **Consulta C** del prompt.

```sql
SELECT
    COMPANIA, NOMBRE_VENDEDOR,
    COUNT(*) AS total_clientes,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '1 - VIP ACTIVO: cuidar y expandir'           THEN 1 ELSE 0 END) AS vip_activos,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '2 - URGENTE: cliente importante enfriándose' THEN 1 ELSE 0 END) AS urgentes,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '3 - RESCATE: contactar antes de perderlo'    THEN 1 ELSE 0 END) AS rescate,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '4 - REACTIVACION: cliente dormido valioso'   THEN 1 ELSE 0 END) AS reactivacion,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '5 - SEGUIMIENTO: cliente regular'            THEN 1 ELSE 0 END) AS seguimiento,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '6 - BAJA PRIORIDAD'                          THEN 1 ELSE 0 END) AS baja_prioridad,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL = '1 - VIP ACTIVO: cuidar y expandir' THEN VLR_NETO_FACTURADO ELSE 0 END) AS valor_vip,
    SUM(CASE WHEN PRIORIDAD_COMERCIAL IN ('2 - URGENTE: cliente importante enfriándose',
                                          '3 - RESCATE: contactar antes de perderlo')
             THEN VLR_NETO_FACTURADO ELSE 0 END) AS valor_en_riesgo,
    SUM(VLR_NETO_FACTURADO) AS facturacion_total_historica,
    CAST(SUM(CASE WHEN PRIORIDAD_COMERCIAL IN ('2 - URGENTE: cliente importante enfriándose',
                                               '3 - RESCATE: contactar antes de perderlo')
                  THEN 1.0 ELSE 0 END) * 100.0 / NULLIF(COUNT(*),0) AS decimal(5,2)) AS porc_cartera_en_riesgo
FROM dbo.vw_CRM_Clientes_Prioritarios
GROUP BY COMPANIA, NOMBRE_VENDEDOR
ORDER BY COMPANIA, facturacion_total_historica DESC;
```

### Integral — perfil cruzado (¿compra en una o en ambas compañías?)
**Qué analizar:** el `perfil_cruzado` marca quién es 'YA INTEGRAL' (proteger), quién es
'SOLO COMPAÑIA 1/2' (candidato a venta cruzada). Es la materia prima del análisis de
venta integral. → Alimenta la **Consulta A** del prompt con foco integral.

```sql
;WITH clientes_por_nit AS (
    SELECT
        NIT,
        COUNT(DISTINCT COMPANIA) AS num_companias,
        STUFF((
            SELECT ', ' + CAST(c2.COMPANIA AS varchar(10))
            FROM dbo.CRM_Consolidado_Ventas_cliente c2
            WHERE c2.NIT = c1.NIT
            GROUP BY c2.COMPANIA
            ORDER BY c2.COMPANIA
            FOR XML PATH(''), TYPE).value('.', 'nvarchar(max)'), 1, 2, '') AS companias_donde_compra
    FROM dbo.CRM_Consolidado_Ventas_cliente c1
    WHERE NIT IS NOT NULL
    GROUP BY NIT
)
SELECT TOP 30
    cp.COMPANIA, cp.NIT, cp.RAZON_SOCIAL, cp.NOMBRE_VENDEDOR, cp.CIUDAD,
    cp.NUM_FACTURAS, cp.VLR_NETO_FACTURADO, cp.TICKET_PROMEDIO,
    cp.ULTIMA_FACTURA, cp.DIAS_DESDE_ULTIMA_COMPRA,
    cp.PUNTAJE_PRIORIDAD, cp.PRIORIDAD_COMERCIAL,
    cn.companias_donde_compra,
    CASE
        WHEN cn.num_companias > 1 THEN 'YA INTEGRAL'
        WHEN cp.COMPANIA = 1     THEN 'SOLO COMPAÑIA 1 (plástico)'
        WHEN cp.COMPANIA = 2     THEN 'SOLO COMPAÑIA 2 (etiqueta)'
        ELSE 'OTRA'
    END AS perfil_cruzado
FROM dbo.vw_CRM_Clientes_Prioritarios cp
    INNER JOIN clientes_por_nit cn ON cn.NIT = cp.NIT
WHERE cp.PRIORIDAD_COMERCIAL IN (
        '1 - VIP ACTIVO: cuidar y expandir',
        '2 - URGENTE: cliente importante enfriándose')
ORDER BY cp.PUNTAJE_PRIORIDAD DESC, cp.VLR_NETO_FACTURADO DESC;
```

---

## BLOQUE 2 — Horizonte presupuestal (Q1–Q5)

Mismo origen (`vw_CRM_Clientes_Prioritarios`) pero filtrando por `HORIZONTE_PRESUPUESTO`.
Es la lectura "para no perder la meta del año".

| Query | Filtro `HORIZONTE_PRESUPUESTO` | Qué analizar |
|---|---|---|
| Q1 Críticos activos | `'P1 - PRESUPUESTO ACTIVO'` | Lo que aporta hoy: cuidarlo = no perder meta |
| Q2 En riesgo | `'P2 - PRESUPUESTO EN RIESGO'` | Estaban en el plan, llevan >90 días sin comprar |
| Q3 Recuperar | `'P3 - PRESUPUESTO PASADO (RECUPERAR)'` | Aportaron el año pasado, no este: reactivar |
| Q4 Largo plazo | `'P4 - FUERA DE PRESUPUESTO'` | >1 año sin comprar: plan separado, no urgente |
| Q5 Panorama ppto | (agregado por vendedor) | Aporte real vs año anterior y riesgo por vendedor |

Patrón Q1–Q4 (cambia solo el filtro y el `ORDER BY`):

```sql
SELECT TOP 30
    NOMBRE_VENDEDOR, NIT, RAZON_SOCIAL, CIUDAD,
    FACTURADO_ANIO_ACTUAL, FACTURADO_ANIO_ANTERIOR, VARIACION_ANUAL_PORC,
    NUM_FACTURAS_ANIO_ACTUAL, DIAS_DESDE_ULTIMA_COMPRA,
    IMPACTO_PRESUPUESTO_ESTIMADO, HORIZONTE_PRESUPUESTO, ACCION_PRESUPUESTAL
FROM dbo.vw_CRM_Clientes_Prioritarios
WHERE HORIZONTE_PRESUPUESTO = 'P1 - PRESUPUESTO ACTIVO'   -- cambia por P2/P3/P4
ORDER BY FACTURADO_ANIO_ACTUAL DESC;
```

Q5 (gerencial agregado): conteo de clientes por horizonte + valor a rescatar/recuperar
por vendedor. (Tu versión es correcta; mantenla tal cual.)

---

## BLOQUE 3 — Generación de negocios para el pipeline

Estas dos extraen candidatos para crear `sf_negocios` (negocios) en el CRM. Origen:
`CRM_Consolidado_Ventas_cliente`.

### Negocios de RECUPERACIÓN (importantes que se enfrían: 90–180 días, >$100M)
**Qué analizar:** cada fila es un "negocio de recuperación" candidato. Prioriza por
valor histórico. Convierte los top en negocios con etapa inicial y fecha de contacto.

```sql
SELECT
    NIT, RAZON_SOCIAL, NOMBRE_VENDEDOR,
    VLR_NETO_FACTURADO AS valor_historico, ULTIMA_FACTURA, DIAS_DESDE_ULTIMA_COMPRA
FROM dbo.CRM_Consolidado_Ventas_cliente
WHERE DIAS_DESDE_ULTIMA_COMPRA BETWEEN 90 AND 180
  AND VLR_NETO_FACTURADO > 100000000
ORDER BY VLR_NETO_FACTURADO DESC;
```

### Negocios de EXPANSIÓN (top activos: compraron en ≤30 días)
**Qué analizar:** clientes "calientes" para subir ticket / venta cruzada. Nota: la
"baja diversidad" real requiere dato de producto/línea, que hoy no está en el pipeline;
por ahora prioriza por valor y frecuencia.

```sql
SELECT TOP 50
    NIT, RAZON_SOCIAL, NOMBRE_VENDEDOR, VLR_NETO_FACTURADO, NUM_FACTURAS
FROM dbo.CRM_Consolidado_Ventas_cliente
WHERE DIAS_DESDE_ULTIMA_COMPRA <= 30
ORDER BY VLR_NETO_FACTURADO DESC;
```

---

## BLOQUE 4 — Tendencias y monitoreo

Origen: `vw_CRM_Ventas_Mensuales_cliente` (grano cía+cliente+mes).

- **A) Facturación mensual total (24 meses):** clientes activos, facturado, pedidos y
  remisionado por mes. → Para ver la curva del negocio.
- **B) Comparativo por compañía y mes:** separa plástico vs etiqueta. → Para ver qué
  compañía empuja cada mes.
- **C) Tendencia de los 5 clientes top:** evolución mensual de las cuentas grandes. →
  Detecta caídas tempranas en cuentas clave.
- **D) Variación mes a mes por vendedor (`LAG`):** subidas/caídas de cada vendedor. →
  Detecta vendedores enfriándose.

(Las cuatro consultas que ya tienes son correctas; este bloque es de lectura/monitoreo,
no alimenta a Gemini directamente.)

### Drill-down de un vendedor en un mes
Para una pantalla con dropdown de vendedor + mes (`vw_CRM_Detalle_Mensual_Vendedor`):

```sql
SELECT RANKING, CLIENTE, NIT, CIUDAD, COMPANIA, NUM_FACTURAS, VALOR, PORC_DEL_MES
FROM dbo.vw_CRM_Detalle_Mensual_Vendedor
WHERE NOMBRE_VENDEDOR = @vendedor_seleccionado
  AND ANIO_MES        = @mes_seleccionado
ORDER BY RANKING;
```

---

## CÓMO USAR EL PROMPT DE GEMINI

### Qué consulta alimenta qué tipo de análisis
- **Consulta A (Atención inmediata):** usa el resultado del Bloque 1-A o el Integral.
- **Consulta B (Rescate/reactivación):** usa el resultado del Bloque 1-B.
- **Consulta C (Panorama gerencial):** usa el resultado del Bloque 1-C o Q5.

### Parámetros (rellénalos antes de pegar los datos)

| Parámetro | Ejemplo (perfil Contiflex integral) |
|---|---|
| `{{HORIZONTE_DIAS}}` | 30 días |
| `{{NOMBRE_EMPRESA}}` | Grupo Contiflex |
| `{{INDUSTRIA}}` | Empaque industrial B2B (envases plásticos + etiquetas) |
| `{{PERIODO}}` | Cierre del mes de mayo 2026 |
| `{{META_COMERCIAL}}` | $500 millones COP adicionales, mín. 5 conversiones a integral |
| `{{NOMBRE_VENDEDOR}}` | TODOS (o el nombre real) |
| `{{COMPANIA_VENDEDOR}}` | AMBAS (vendedor cross) |
| `{{CAPACIDAD_SEMANAL}}` | 15 visitas + 30 llamadas + 60 WhatsApp |
| `{{CANALES}}` | Visita con muestra física, llamada, WhatsApp, video demo |
| `{{AUTONOMIA}}` | 5% descuento, plazo hasta 45 días, pilotos hasta 10K und |
| `{{TIPO_CONSULTA}}` | A (o B, o C) |
| `{{CLIENTES_EXCLUIDOS}}` | Ninguno (o NITs separados por coma) |
| `{{BLOQUEOS}}` | Clientes con mora >60 días deben regularizar antes |
| `{{POLITICAS}}` | Toda venta integral requiere visita técnica conjunta |
| `{{PEGAR_AQUI_LOS_DATOS}}` | el resultado de la consulta del bloque elegido |

Perfil alternativo (una sola compañía, p. ej. cosméticos): cambia
`{{NOMBRE_EMPRESA}}`, `{{INDUSTRIA}}` y `{{DESCRIPCION_PORTAFOLIO}}` por los de esa
unidad y pon `{{COMPANIA_VENDEDOR}}` = 1 o 2.

### Iteraciones útiles después del primer plan (pídeselas a Gemini)
- "Para los 3 clientes 'monoempresa con alto potencial integral', dame un argumentario
  de 5 líneas para memorizar antes de la visita, con manejo de 2 objeciones típicas."
- "Convierte la agenda semanal en un plan de 4 semanas escalonado: sem 1 presentación,
  sem 2 muestras, sem 3 propuestas, sem 4 cierre/seguimiento."
- "De los 30 clientes, identifica los 3 que más se beneficiarían de una propuesta
  conjunta con descuento por volumen integral."

### Cerrar el ciclo: del plan de Gemini al CRM
Cuando el plan tenga acciones por cliente, cárgalas como registros del CRM:
- Cada acción → un `seguimiento` (tipo, descripción, `proxima_fecha`, `user_id` del asesor).
- Cada oportunidad → un `sf_negocio` (cliente, etapa inicial, `valor_estimado`,
  `fecha_estimada_cierre`, `asesor_id`).
- El plan escalonado de 4 semanas → cuatro `seguimientos` con `proxima_fecha` por etapa.

Así el plan deja de vivir en un documento y empieza a alimentar los tableros (pipeline,
próximas actividades, actividad del equipo).
