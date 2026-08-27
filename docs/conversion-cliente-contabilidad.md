# Módulo: Conversión Prospecto → Cliente con revisión de Contabilidad

## Contexto

Rework del flujo de conversión de prospecto a cliente. Antes era instantáneo: un modal pedía razón social/NIT y creaba el `Cliente` de una vez.

Ahora, cuando un comercial convierte un prospecto, primero carga un archivo plano (CSV/TXT) con información de la empresa/contactos, la revisa y corrige en pantalla, y solo entonces guarda. El cliente se crea de inmediato — **no bloquea** al comercial, que puede seguir operando negocios/solicitudes de crédito de una vez — pero queda visible para un nuevo rol de **Contabilidad**, separado por compañía (Formacol / Contiflex). Un auxiliar contable pertenece exclusivamente a una de las dos.

Contabilidad usa la pantalla nueva **solo para consultar**: se guía con esa información para registrar el cliente y asignar el comercial directamente en SIESA (fuera de nuestro sistema — no hay integración de escritura hacia el ERP). Al terminar, marcan "Ya registrado en SIESA" para sacarlo de su lista de pendientes.

## Decisiones tomadas con el usuario

| Pregunta | Decisión |
|---|---|
| Contenido del archivo plano | Definido: 19 campos de perfil de cliente (ver abajo). Cada fila del archivo es la ficha completa de un cliente, no una lista de empleados |
| Integración con SIESA | Ninguna escritura automática; es solo informativo, contabilidad registra manualmente en SIESA |
| Asignación del comercial | La hace contabilidad directamente en SIESA; nuestro sistema solo muestra la info, no asigna nada |
| Bloqueo de negocios/créditos mientras está pendiente | No se bloquea — el comercial puede operar el cliente de inmediato |
| Alcance del rol de Contabilidad | Exclusivo de una compañía por usuario (Formacol o Contiflex, nunca ambas) |
| Roles vs. Spatie "teams" | Se usaron dos roles discretos (`contabilidad_formacol`, `contabilidad_contiflex`) en vez de activar "teams" de Spatie (estaba deshabilitado y hubiera tocado todo el sistema de permisos) |

## Qué se construyó

### Campos del archivo plano (`config/cliente_datos_carga_campos.php`)

Lista definitiva (dada por el jefe del usuario): ejecutivo comercial a cargo, razón social, NIT (sin dígito de verificación), teléfono corporativo, extensión, e-mail, dirección/ciudad de correspondencia, dirección/ciudad de despacho, gran contribuyente (Sí/No), autorretenedor (Sí/No), contactos y teléfonos de pago, fecha de cierre de facturación, lapso de entrega (días mín./máx.), horario de recepción de mercancía, correo para facturación electrónica.

Si el archivo trae varias filas (varios clientes en un mismo cargue), la pantalla de conversión (`prospectos/convertir.blade.php`) y la de Contabilidad (`contabilidad/show.blade.php`) muestran cada fila como una **pestaña** ("Cliente 1", "Cliente 2"... o la razón social si ya está diligenciada), con botones Anterior/Siguiente para moverse una por una — en vez de una tabla ancha de 19 columnas.

El archivo de config se llamaba `empleados_campos.php`; se renombró porque los campos describen el perfil de UN cliente/empresa, no una lista de empleados.

### Base de datos
- `database/migrations/2026_07_14_000001_add_revision_contabilidad_to_clientes_table.php` — añade a `clientes`: `datos_carga` (json, snapshot de las filas revisadas del archivo plano), `revisado_contabilidad_en` (timestamp nullable), `revisado_contabilidad_por` (FK a `users`, nullable).

### Modelo `Cliente` (`app/Domain/Clientes/Models/Cliente.php`)
- Nuevos campos en `$fillable`/casts (`datos_carga` como `array`, `revisado_contabilidad_en` como `datetime`).
- Relación `revisadoContabilidadPor()` → `User`.
- Helper `pendienteContabilidad()` y scope `scopePendientesContabilidad($compania)`.
- Se agregó `companiaNombre()` (Formacol/Contiflex), que ya existía en `Negocio` pero no en `Cliente`.

### Flujo de conversión (comercial)
- El modal de `prospectos/show.blade.php` fue reemplazado por una página completa: `GET /prospectos/{prospecto}/convertir` (`ProspectoWebController::convertir`).
- Vista nueva `resources/views/prospectos/convertir.blade.php`: input de archivo (`.csv`/`.txt`), parseo **en el navegador** con `FileReader` (primera línea = encabezados, separador `,` o `;` autodetectado), tabla editable con los datos parseados. No se sube el archivo al backend — se evitó multipart porque el helper `$api` global siempre manda JSON.
- Al guardar, se envía `datos_carga` (arreglo de filas) junto con los campos existentes al mismo endpoint de siempre: `POST /api/prospectos/{id}/convertir`.
- Cambios mínimos aguas abajo para transportar el dato: `ConvertirProspectoRequest` (regla `datos_carga` array), `ConvertirProspectoDTO`, `CrearClienteDTO`, `ProspectoService::convertirEnCliente()`.

### Roles y permisos (`database/seeders/RolesPermisosSeeder.php`)
- Nuevo permiso `clientes.revisar_contabilidad`.
- Roles `contabilidad_formacol` y `contabilidad_contiflex`, cada uno con `clientes.ver` + `clientes.revisar_contabilidad`.
- La compañía de un auxiliar se deriva de cuál de los dos roles tiene.

### Módulo Contabilidad
- `app/Http/Controllers/Web/ContabilidadWebController.php`: `index` (lista clientes pendientes filtrados por la compañía del rol del usuario, con buscador por razón social/NIT), `show` (detalle de solo lectura).
- `app/Http/Controllers/Api/ClienteController::marcarRegistradoContabilidad()`: setea `revisado_contabilidad_en`/`revisado_contabilidad_por`, valida rol (`contabilidad_formacol|contabilidad_contiflex|admin`) dentro del propio método (no vía middleware, para no depender del guard de Sanctum en rutas de API — se siguió el mismo patrón que ya usan las Policies existentes).
- Rutas: `routes/web.php` (`/contabilidad`, `/contabilidad/{cliente}`, con `middleware('role:contabilidad_formacol|contabilidad_contiflex|admin')`) y `routes/api.php` (`POST /api/clientes/{cliente}/marcar-registrado-contabilidad`).
- Vistas `resources/views/contabilidad/index.blade.php` (tabla + buscador con debounce, mismo patrón que `clientes/index.blade.php`) y `show.blade.php` (cabecera, tabla de `datos_carga`, contactos, botón "Ya registrado en SIESA").
- Ítem "Contabilidad" en el menú lateral, visible solo con `@hasanyrole('contabilidad_formacol|contabilidad_contiflex|admin')`.

### Tests
- `tests/Feature/SFconnecting/Contabilidad/ContabilidadTest.php` (6 tests): conversión persiste `datos_carga` y queda pendiente; cada rol de contabilidad solo ve clientes de su compañía; el buscador filtra por razón social/NIT; `marcarRegistrado` actualiza el cliente y lo saca de la lista; un comercial no puede marcar como registrado.

## Verificación realizada

- Migración y seeder corridos sin errores contra la base de datos real (MySQL).
- `php artisan route:list` confirmó el registro de todas las rutas nuevas.
- Suite nueva: **6/6 en verde**.
- Suite completa del proyecto corrida dos veces (con y sin los cambios, vía `git stash`): las 12 fallas en `SolicitudCreditoApiTest` **ya existían antes de este trabajo** y no están relacionadas — el resto (414 tests) pasa igual en ambos casos.

## Pendiente / fuera de alcance

- Cualquier integración de escritura hacia SIESA — confirmado explícitamente que no aplica por ahora.

## Addendum: solicitud de cupo en la conversión (2026-07-24)

Se agregó un checkbox opcional "¿Solicitar cupo de crédito para este cliente?" en `prospectos/convertir.blade.php`. No es una solicitud de crédito sobre un negocio ya negociado — es una consulta previa de cuánto cupo está dispuesta a otorgar la compañía a un cliente nuevo, antes de que exista ningún Negocio. Con esa respuesta se define después cómo se plantea la negociación.

Campos reales del formulario (dados por el usuario, no son los 3 genéricos originales):
- Referencia comercial 1 y 2 (empresa, teléfono, NIT cada una) → columna nueva `referencias_comerciales` (json, array de hasta 2 objetos)
- Condiciones de pago (días) → reutiliza la columna existente `plazo_solicitado_dias`
- Cupo mensual de crédito solicitado ($) → reutiliza la columna existente `monto_solicitado`
- Inventario en consignación (Sí/No) → columna nueva `inventario_consignacion` (boolean)

Reutiliza el módulo `SolicitudesCredito` existente (mismo flujo de aprobación de gerente) en vez de crear algo paralelo — la migración `2026_07_24_000002_add_referencias_comerciales_to_sf_solicitudes_credito_table` agrega las dos columnas nuevas, que son nullable así que la solicitud radicada desde un Negocio (flujo original, sin cambios) simplemente no las usa:

- `sf_solicitudes_credito.negocio_id` pasa a ser **nullable** (migración `2026_07_24_000001_make_negocio_id_nullable_on_sf_solicitudes_credito_table`). Una solicitud ahora puede colgar solo de `cliente_id`, sin negocio.
- `SolicitudCreditoService::crear()` se separó en dos caminos: `crearParaNegocio()` (comportamiento original, sin cambios) y `crearParaCliente()` (nuevo). En el segundo, el dossier ERP es **best-effort**: si el cliente todavía no está registrado en SIESA (lo normal recién convertido, antes de que contabilidad lo registre manualmente), la solicitud se radica igual sin ese contexto en vez de fallar.
- `ProspectoService::convertirEnCliente()` llama a `SolicitudCreditoService::crear()` después de crear el `Cliente`, dentro de la misma transacción. Si la solicitud de cupo falla por algún motivo, **no tumba la conversión** — se loguea y el comercial se queda con su cliente creado.

### Bugs preexistentes encontrados y corregidos de paso

Al tocar este módulo se encontró que **`SolicitudesCredito` nunca quedó completamente cableado**: `SolicitudCreditoPolicy` tenía las 5 reglas hardcodeadas a `hasRole('admin')` (nadie más podía crear ni revisar solicitudes), y ni `comercial` ni `gerente` tenían los permisos `solicitudes_credito.*` en `RolesPermisosSeeder`. Además, `comercial` tampoco tenía `prospectos.convertir`. Se corrigieron los tres en el seeder y la Policy — antes de este fix, `SolicitudCreditoApiTest` tenía 10/11 tests fallando en rojo.

### Tests nuevos
- `ContabilidadTest::test_convertir_prospecto_con_solicita_cupo_radica_solicitud_de_credito_sin_negocio`
- `ContabilidadTest::test_convertir_prospecto_sin_marcar_solicita_cupo_no_radica_solicitud`

### Pendiente
- Aplicar la migración nueva contra la base de datos MySQL local/real (no se pudo correr en esta sesión — MySQL de XAMPP no estaba levantado, error de permisos en `C:\xampp\mysql\data`, ajeno a este trabajo).
