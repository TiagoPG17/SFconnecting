# Notificación automática — Gestión de Cartera (n8n)

Flujo que revisa a diario los pedidos que el equipo marcó como "Notificar" desde
`/gestion-cartera` y avisa 3 días antes de `FechaInicioCobro` — una fecha que
**el usuario elige manualmente** al notificar un pedido (no viene del ERP), y
que representa cuándo debería empezar el aviso de cobro. `FechaCumplimiento`
(la fecha de entrega comprometida del pedido) se guarda solo como referencia
informativa, ya no se usa para disparar el aviso.

Fuente de datos: tabla `dbo.CRM_Notificaciones_Cartera` en el SQL Server del ERP
(mismo servidor/credenciales que usa Laravel en `.env` con el prefijo
`ERP_CONTIFLEX_*`, host `172.20.3.1`, base `Contiflex`). La columna
`FechaInicioCobro` (date, nullable) se agregó con:
```sql
ALTER TABLE dbo.CRM_Notificaciones_Cartera ADD FechaInicioCobro date NULL;
```

## Importar el workflow

1. En n8n: **Workflows → Import from File** y selecciona
   `docs/n8n/gestion-cartera-notificacion.workflow.json`.
2. Crea (o reutiliza) una credencial de tipo **Microsoft SQL Server** con los
   mismos datos del `.env` del proyecto (`ERP_CONTIFLEX_HOST`,
   `ERP_CONTIFLEX_DATABASE=Contiflex`, `ERP_CONTIFLEX_USERNAME`,
   `ERP_CONTIFLEX_PASSWORD`) y asígnala en los dos nodos SQL
   ("Leer pedidos a 3 días de FechaInicioCobro" y "Marcar como notificado").
3. En el nodo **"Enviar correo (Gmail)"** conecta tu credencial OAuth2 de Gmail.
   Los campos disponibles por pedido son: `Compania`, `NroDocumento`, `Cliente`,
   `Vendedor`, `FechaPedido`, `FechaCumplimiento`, `FechaInicioCobro`,
   `SubtotalPendiente`.
4. **Pendiente por definir**: el `sendTo` del nodo Gmail quedó con un correo
   fijo de ejemplo (`cartera@tudominio.com`). Falta decidir el destinatario
   real — ¿un solo correo del área de cartera, o el correo de cada vendedor?
   Si es lo segundo, hace falta una tabla/mapa que traduzca `Vendedor` (nombre
   en el ERP) a su correo, porque el ERP no guarda ese dato.
5. Activa el workflow cuando esté probado.

## Lógica

1. **Cada día 8:00am** — cron `0 8 * * *` (ajustable en el nodo Schedule Trigger).
2. **Leer pedidos a 3 días de FechaInicioCobro**:
   ```sql
   SELECT Compania, NroDocumento, Cliente, Vendedor,
          FechaPedido, FechaCumplimiento, FechaInicioCobro, SubtotalPendiente
   FROM dbo.CRM_Notificaciones_Cartera
   WHERE Notificado = 0
     AND FechaInicioCobro IS NOT NULL
     AND CAST(FechaInicioCobro AS date) = CAST(DATEADD(day, 3, GETDATE()) AS date)
   ```
   Trae los pedidos sin notificar (`Notificado = 0`) cuya `FechaInicioCobro`
   (la que el usuario eligió en la pantalla al darle "Notificar") cae
   exactamente 3 días adelante de hoy. Si prefieren "3 días o menos" en vez de
   "exactamente 3 días", cambien el `=` por `<=`.

   Nota: `FechaInicioCobro` puede venir `NULL` en pedidos viejos que se
   notificaron antes de que existiera este campo — el `IS NOT NULL` los
   excluye para que no rompan la comparación de fechas.
3. **Armar tabla HTML** — junta todos los pedidos que trajo el paso anterior en
   un solo correo con una tabla (zebra striping, 1000px de ancho), en vez de
   mandar un correo separado por cada pedido.
4. **Enviar correo (Gmail)** — un solo envío con la tabla completa.
5. **Marcar como notificado**:
   ```sql
   UPDATE dbo.CRM_Notificaciones_Cartera
   SET Notificado = 1, FechaNotificacion = GETDATE()
   WHERE Compania = {{$json["Compania"]}}
     AND NroDocumento = '{{$json["NroDocumento"]}}'
   ```
   Este paso corre en paralelo al de "Armar tabla HTML" (ambos cuelgan
   directo del nodo SQL de lectura), para no depender de que el correo se
   arme primero.

## Nota de seguridad

El `UPDATE` arma el `WHERE` con expresiones de n8n en vez de parámetros
enlazados. El riesgo es bajo porque `NroDocumento` sale de una fila que la
propia tabla `CRM_Notificaciones_Cartera` ya tiene guardada (no es input libre
de un usuario), pero si tu versión de n8n soporta parámetros de consulta en el
nodo Microsoft SQL (`@0`, `@1`, …), es preferible usarlos en vez de interpolar
el valor directo en el texto del query.

## Pendiente (según se acordó)

Por ahora n8n se conecta directo al SQL Server del ERP con las mismas
credenciales que usa Laravel. Cuando haya tiempo, reemplazar los dos nodos SQL
por llamadas a un endpoint API de Laravel (ej. `GET /api/cartera/pendientes` y
`PATCH /api/cartera/{compania}/{nroDocumento}/notificar`) protegido por token,
para no compartir el acceso directo a la base completa del ERP con n8n.
