# Módulo: Solicitudes de Crédito

## Contexto

El comercial cierra un acuerdo en `Negocio`, radica una solicitud de crédito para el cliente de ese negocio, y gerencia revisa el expediente completo del cliente (cupo, cartera, mora, calificación — todo desde SIESA/Contiflex) para aprobar o rechazar.

Se exploró en vivo la base de datos del ERP (conexión `erp_contiflex`, SQL Server) y se confirmó que SIESA ya trae todo lo necesario:
- `dbo.clientes`: perfil de crédito (`CUPO_CREDITO`, `CALIFICACION_CLIENTE`, bloqueos, condición de pago).
- `dbo.vw_CRM_Cartera_CxC`: cartera por documento con aging ya calculado (`SALDO`, `DIAS_VENCIDO`, `TRAMO_AGING`).

La **IA** que motivó la conversación inicial queda **fuera de esta entrega** (decisión explícita) — el módulo se construyó con datos y reglas duras del ERP. Queda la puerta abierta a añadir una capa de IA después sin tocar el esquema base (por ejemplo, para redactar el resumen del dossier a partir de las señales ya calculadas, no para decidir a ciegas).

## Decisiones tomadas con el usuario

| Pregunta | Decisión |
|---|---|
| Flujo de estados | Pipeline completo reutilizando `PipelineEstado` con `tipo = 'solicitud_credito'` (no un simple string) |
| Datos del ERP al radicar | Snapshot JSON en el momento de la radicación — gerencia ve exactamente lo que vio el comercial, sin importar cambios posteriores en el ERP |
| Origen de la solicitud | Siempre nace de un `Negocio` existente (`negocio_id` requerido); el cliente se deriva del negocio |
| ERP caído al radicar | Se bloquea la creación — no se permite un dossier incompleto |

## Hechos de esquema verificados antes de migrar

- `sf_pipeline_estados.tipo` y `sf_auditoria_pipeline.evento` son **enums reales** en MySQL → requirieron migraciones `ALTER ... ->change()`, no solo datos de seeder.
- `->change()` sobre `enum` funciona nativo en Laravel 12 + MySQL, sin `doctrine/dbal`.
- `slug` en `sf_pipeline_estados` tiene índice único **global** (no por tipo) → los nuevos slugs se prefijaron `credito-*`.

## Qué se construyó

### Base de datos
- `database/migrations/2026_07_02_072608_alter_sf_pipeline_estados_tipo_add_solicitud_credito.php` — añade `solicitud_credito` al enum `tipo`.
- `database/migrations/2026_07_02_072617_alter_sf_auditoria_pipeline_evento_add_credito_eventos.php` — añade `solicitud_credito_aprobada`, `solicitud_credito_aprobada_condiciones`, `solicitud_credito_rechazada` al enum `evento`.
- `database/migrations/2026_07_02_072618_create_sf_solicitudes_credito_table.php` — tabla principal: `negocio_id`, `cliente_id`, `pipeline_estado_id`, `asesor_id`, `revisado_por` (nullable), `monto_solicitado`, `plazo_solicitado_dias`, `justificacion`, `dossier_erp` (json), `comentario_revision`, `condiciones`, `revisado_en`, soft deletes.
- `database/seeders/PipelineComercialSeeder.php` — 5 nuevos estados `tipo = 'solicitud_credito'`: `credito-radicada`, `credito-en-revision`, `credito-aprobada`, `credito-aprobada-condiciones`, `credito-rechazada`.

### Capa ERP (`app/Domain/ERP/`)
- Nuevo método `carteraPorNit(string $nit): array` en `ERPRepositoryInterface`, implementado en `ContiflexERPRepository` (consulta `dbo.vw_CRM_Cartera_CxC`) y en `FakeERPRepository` (+ helper de test `agregarCartera()`).
- `clientePorNit()` (ya existente) se reutiliza tal cual — ya trae todos los campos de crédito.

### Dominio `app/Domain/SolicitudesCredito/`
- `DTOs/`: `CrearSolicitudCreditoDTO`, `DecidirSolicitudCreditoDTO`.
- `Exceptions/SolicitudCreditoException.php`: `negocioSinCliente()`, `erpNoDisponible()`, `clienteNoEncontradoEnErp()`, `solicitudActivaExistente()`, `yaFinalizada()`, `condicionesRequeridas()`, `decisionInvalida()`.
- `Models/SolicitudCredito.php`: relaciones a `Negocio`, `Cliente`, `PipelineEstado`, `asesor`/`revisor` (User), y `auditoria()` reutilizando `AuditoriaPipeline` (sin tabla nueva).
- `Repositories/SolicitudCreditoRepositoryInterface` + `SolicitudCreditoRepository`: `crear`, `decidir`, `buscarPorId`, `paginar`, `porNegocio`, `tieneSolicitudActiva`.
- `Policies/SolicitudCreditoPolicy.php`: `viewAny`, `view` (comercial solo ve las suyas), `create`, `delete` (solo admin), `revisar` (gerencia/admin).
- `Services/SolicitudCreditoService.php`:
  - `crear()`: valida que el negocio tenga cliente, que no haya otra solicitud activa para ese negocio, construye el dossier ERP (`construirDossierErp()` — combina `clientePorNit()` + `carteraPorNit()`, calcula `cupo_disponible = cupo_credito - saldo_total`), crea el registro en estado "Radicada", audita.
  - `decidir()`: valida que no esté ya finalizada, exige `condiciones` si la decisión es "aprobada con condiciones", actualiza el estado y audita con el evento específico.

### HTTP
- `app/Http/Requests/SolicitudesCredito/{CrearSolicitudCreditoRequest,DecidirSolicitudCreditoRequest}.php`.
- `app/Http/Controllers/Api/SolicitudCreditoController.php`: `index`, `store`, `show`, `destroy`, `decidir` (acción custom).
- `app/Http/Controllers/Web/SolicitudCreditoWebController.php`: `index`, `create`, `show` (las mutaciones van por API vía `fetch()` desde Blade).
- `app/Http/Resources/SolicitudesCredito/SolicitudCreditoResource.php`.

### Rutas
- `routes/api.php`: `POST /api/solicitudes-credito/{solicitudCredito}/decidir` + `apiResource('solicitudes-credito', ...)->only(['index','store','show','destroy'])->parameters([...])` (fue necesario mapear el parámetro explícitamente porque Laravel generaba `{solicitudes_credito}` en vez de `{solicitudCredito}` por el guion en el nombre del recurso).
- `routes/web.php`: `/solicitudes-credito`, `/solicitudes-credito/create`, `/solicitudes-credito/{solicitudCredito}`.

### Permisos (`database/seeders/RolesPermisosSeeder.php`)
- Nuevos permisos: `solicitudes_credito.ver`, `.crear`, `.eliminar`, `.revisar`.
- `admin`: todos (ya heredaba `Permission::all()`).
- `gerente`: `ver` + `revisar`.
- `comercial`: `ver` + `crear`.

### Wiring (`app/Providers/AppServiceProvider.php`)
- Bind de `SolicitudCreditoRepositoryInterface` → `SolicitudCreditoRepository`.
- Registro de `Gate::policy(SolicitudCredito::class, SolicitudCreditoPolicy::class)`.

### Vistas (`resources/views/solicitudes-credito/`)
- `index.blade.php`: listado con filtro por estado, auto-filtrado por asesor si es comercial.
- `create.blade.php`: autocomplete de negocio (reutilizable si se llega sin `negocio_id` preseleccionado), monto, plazo, justificación.
- `show.blade.php`: cabecera de la solicitud, **dossier de crédito** (calificación, cupo, cupo disponible, bloqueos), **cartera vencida** por tramo de aging, **auditoría**, y el **formulario de decisión** de gerencia (aprobar / aprobar con condiciones / rechazar), visible solo con permiso `revisar` y mientras la solicitud no esté finalizada.
- Botón "Radicar Solicitud de Crédito" añadido en `negocios/show.blade.php` cuando el negocio tiene cliente vinculado.
- Ítem "Solicitudes de Crédito" añadido al menú lateral (`components/layouts/app.blade.php`).
- Ícono `file-text` añadido al catálogo de íconos (`components/ui/icon.blade.php`).

### Tests
- `tests/Feature/SFconnecting/SolicitudesCredito/SolicitudCreditoApiTest.php` (11 tests): creación con snapshot correcto, falla si el negocio no tiene cliente, falla si el ERP está caído, no permite dos solicitudes activas por negocio, gerente aprueba / aprueba con condiciones (valida que exige `condiciones`) / rechaza, comercial no puede revisar, comercial no ve solicitud ajena, no se puede decidir una solicitud ya finalizada, 401 sin autenticación.
- `tests/Unit/SFconnecting/ERP/FakeERPRepositoryTest.php`: +3 tests para `carteraPorNit()`.

## Verificación realizada

- `php artisan migrate` aplicado sin errores sobre la base de datos real (MySQL).
- Seeders de pipeline y permisos corridos y verificados por tinker.
- `php artisan route:list` confirmó las rutas y corrigió el mismatch de nombre de parámetro en `apiResource`.
- `php artisan view:cache` confirmó que las vistas Blade compilan sin errores de sintaxis.
- Suite de tests nueva: **11/11 + 3/3 en verde**.
- Suite completa de Negocios y Policies (que dependen de los mismos enums alterados): **79 + 12 en verde**, confirmando que las migraciones `ALTER` no rompieron datos existentes.
- Suite completa del proyecto: 414 pasan / 2 fallan — los 2 fallos (`DashboardVendedorServiceTest::semaforo_es_verde...` y `ContactoApiTest::crear_contacto_principal_desmarca_anterior`) son **preexistentes y no relacionados** con este módulo (no tocan Negocios, Pipeline, ERP ni Solicitudes de Crédito).

## Pendiente / fuera de alcance

- Segundo módulo mencionado por el jefe, aún sin describir.
- Capa de IA para sugerencias/recomendaciones sobre la solicitud — deliberadamente diferida hasta tener más claridad sobre qué debe recomendar y con qué datos.
