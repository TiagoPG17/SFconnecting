# Notificación automática — Gestión de Cartera (n8n)

Flujo que revisa a diario los pedidos pendientes en `CRM_Notificaciones_Cartera`
y avisa a cartera 3 días antes de `FechaCumplimiento` (fecha en la que debe
empezar la orden de producción del pedido).

Fuente de datos: API de Laravel (`/api/cartera/*`), protegida por token. n8n
**ya no se conecta directo** al SQL Server del ERP — antes usaba las mismas
credenciales que Laravel (`ERP_CONTIFLEX_*`), con acceso de lectura/escritura a
toda la base `Contiflex`, no solo a la tabla de cartera.

## Autenticación

Cada request lleva el header `Authorization: Bearer {{N8N_API_TOKEN}}`, con el
token configurado como credencial en n8n (guardarlo como "Header Auth" o
variable de entorno del workflow, nunca en texto plano dentro de los nodos).

El valor debe coincidir con `N8N_API_TOKEN` en el `.env` de Laravel. Para
generar uno nuevo:
```
php artisan tinker --execute="echo Str::random(40);"
```

## Lógica

1. **Cada día 8:00am** — cron `0 8 * * *` (ajustable en el nodo Schedule Trigger).
2. **Leer pedidos a 3 días de FechaCumplimiento** — nodo HTTP Request:
   ```
   GET {APP_URL}/api/cartera/pendientes?fecha_cumplimiento={{ $today.plus({days:3}).toFormat('yyyy-MM-dd') }}
   Authorization: Bearer {{N8N_API_TOKEN}}
   ```
   Responde `{ "success": true, "data": [ { "Compania", "NroDocumento", "Cliente", "Vendedor", "FechaPedido", "FechaCumplimiento", "SubtotalPendiente", ... } ] }`
   con los pedidos sin notificar (`Notificado = 0`) cuya `FechaCumplimiento`
   cae exactamente en la fecha pedida. El filtrado por fecha ya lo hace la API
   (parámetro opcional `fecha_cumplimiento`); si se omite, retorna todos los
   pendientes sin filtrar por fecha (es lo que usa la pantalla de Gestión de
   Cartera del CRM).
3. **Armar tabla HTML** — junta todos los pedidos que trajo el paso anterior en
   un solo correo con una tabla (zebra striping, 1000px de ancho), en vez de
   mandar un correo separado por cada pedido.
4. **Enviar correo (Gmail)** — un solo envío con la tabla completa.
5. **Marcar como notificado** — un nodo HTTP Request por cada pedido enviado
   (o en loop), en paralelo al de "Armar tabla HTML" (ambos cuelgan directo del
   nodo que lee pendientes), para no depender de que el correo se arme primero:
   ```
   PATCH {APP_URL}/api/cartera/{{ $json["Compania"] }}/{{ $json["NroDocumento"] }}/notificar
   Authorization: Bearer {{N8N_API_TOKEN}}
   ```
   Responde `{ "success": true, "data": { "resuelto": true } }` (o `resuelto: false`
   si el pedido ya estaba marcado — la operación es idempotente, seguro
   reintentar sin duplicar nada).

## Implementación en Laravel

- `app/Http/Controllers/Api/CarteraNotificacionController.php` — expone los dos
  endpoints, reusando los mismos métodos de `ERPRepositoryInterface` que ya usa
  la pantalla de Gestión de Cartera del CRM (`notificacionesCarteraPendientes`,
  `marcarNotificacionCarteraResuelta`).
- `app/Http/Middleware/VerifyN8nToken.php` (alias `n8n.token`) — valida el
  bearer token contra `config('services.n8n.token')` (`N8N_API_TOKEN` en `.env`).
- Rutas en `routes/api.php`, fuera del grupo `auth:sanctum` (n8n no es un
  usuario de la app).
- Tests: `tests/Feature/SFconnecting/Cartera/CarteraNotificacionApiTest.php`.
