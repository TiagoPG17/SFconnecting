# SFconnecting — Estado del proyecto

> Última actualización: 2026-05-22

---

## ✅ COMPLETADO

### Backend — Dominio
- [x] Migraciones: sf_maestros_comerciales, sf_pipeline_estados, sf_prospectos, sf_negocios, sf_auditoria_pipeline
- [x] Seeders: PipelineComercialSeeder (34 maestros + 11 estados), RolesPermisosSeeder
- [x] Domain/Maestros: MaestroComercial model, MaestroRepository, MaestroRepositoryInterface
- [x] Domain/Pipeline: PipelineEstado model
- [x] Domain/Prospectos: Model, DTOs (Crear/Actualizar/Convertir), Repository, Service, Policy, Exceptions
- [x] Domain/Negocios: Model, AuditoriaPipeline, DTOs, Repository, Service, Policy, Exceptions
- [x] Domain/Clientes: Cliente + Contacto models, DTOs, Repository, Service, Policy, Exceptions
- [x] Domain/Seguimientos: Model, DTO, Repository (+ paginar()), Service, Policy, Exceptions
- [x] Domain/Dashboard: Repository (5 métodos KPI), Service
- [x] Domain/Reportes: Repository (6 métodos), Interface
- [x] Domain/ERP: ContiflexERPRepository, FakeERPRepository, ERPRepositoryInterface
- [x] Domain/Shared: ValidationBusinessException

### Backend — HTTP API
- [x] AuthController (login, logout, me)
- [x] ClienteController (CRUD + restore)
- [x] ContactoController (nested index/store + shallow show/update/destroy)
- [x] SeguimientoController (index, store, show, update, destroy, proximos)
- [x] ProspectoController (CRUD + kanban + convertir)
- [x] NegocioController (CRUD + kanban + forecast)
- [x] MaestroController (todos, porTipo, pipelineEstados)
- [x] ReporteController (clientes, seguimientos, prospectos, negocios, forecast, conversion)
- [x] DashboardController (kpis)
- [x] ERPController (estado, buscarPorNit)
- [x] Form Requests: todos los módulos (12 requests)
- [x] Resources JSON: Cliente, Contacto, Seguimiento, Prospecto, Negocio
- [x] ApiResponse unificado

### Backend — Web Controllers
- [x] AuthWebController (login, logout — crea token Sanctum en sesión)
- [x] DashboardWebController
- [x] ProspectoWebController (index, show, create, edit, kanban)
- [x] NegocioWebController (index, show, create, edit, kanban, forecast)
- [x] ClienteWebController (index, show, create, edit)
- [x] SeguimientoWebController (index con filtros)
- [x] MaestroWebController (index — solo lectura)
- [x] ReporteWebController (index — 4 tipos de reporte)

### Frontend — Layout & Componentes
- [x] Layout app.blade.php (sidebar slate-900, topbar, toast Alpine, meta api-token)
- [x] Layout guest.blade.php (split decorativo / formulario)
- [x] Componente: nav-item, icon (20 iconos), card, badge, button (5 variantes)
- [x] Componente: input, select, stat-card (5 colores + trend), modal, empty-state, progress
- [x] Tailwind v4 + Alpine.js 3 + @alpinejs/sort + @alpinejs/focus
- [x] Store Alpine: $store.toast (success, error, warning)
- [x] Magic Alpine: $api (fetch con token Sanctum desde meta tag)

### Frontend — Vistas
- [x] auth/login.blade.php
- [x] dashboard/index.blade.php (8 widgets: KPIs, pipeline stats, conversión, próximos cierres, top asesores)
- [x] prospectos/index, show, create, edit, kanban
- [x] negocios/index, show, create, edit, kanban, forecast
- [x] clientes/index, show, create, edit
- [x] seguimientos/index
- [x] maestros/index (solo lectura)
- [x] reportes/index + 4 partials
- [x] usuarios/index, create, edit (CRUD + toggle activo, admin only)

### Backend — Usuarios
- [x] Migration: activo (boolean) en tabla users
- [x] UsuarioController (API): index, store, show, update, toggle — 12 tests
- [x] UsuarioWebController: index, create, edit
- [x] Form Requests: CrearUsuarioRequest, ActualizarUsuarioRequest
- [x] Rutas web: /usuarios, /usuarios/create, /usuarios/{id}/edit
- [x] Rutas API: GET/POST /api/usuarios, GET/PUT /api/usuarios/{id}, PATCH /api/usuarios/{id}/toggle (forecast, prospectos, negocios, conversion)

### Tests — 334 pasando
- [x] Unit Services: Cliente, Contacto, Seguimiento, Dashboard, Prospecto, Negocio (6 archivos)
- [x] Unit Repositories: Cliente, Contacto, Seguimiento, Prospecto, Negocio, Dashboard (6 archivos)
- [x] Unit Policies: Cliente, Contacto, Seguimiento, Prospecto, Negocio (5 archivos)
- [x] Unit ERP: FakeERPRepositoryTest
- [x] Feature API: Auth, Clientes, Contactos, Seguimientos (con prospecto_id), Dashboard, Prospectos, Negocios, Reportes, Usuarios, Maestros (10 archivos)
- [x] Integration: ERPClienteIntegrationTest

---

## ❌ PENDIENTE

### Alta prioridad
- [x] **Paginación personalizada** — Vistas custom en slate/blue registradas en AppServiceProvider
- [x] **Sidebar móvil** — Hamburger + overlay + translate-x transitions en app.blade.php
- [x] **Gestión de Usuarios** — CRUD completo + toggle activo, admin only (12 tests nuevos)
- [x] **Maestros CRM con CRUD** — Crear/editar/toggle activo inline con modal Alpine, admin only (12 tests nuevos)

### Media prioridad
- [x] **Test `SeguimientoRepository::paginar()`** — 7 tests nuevos cubriendo todos los filtros
- [x] **Aging de oportunidades** — Widget en dashboard: días promedio por estado, alerta si >30 días, 5 tests en DashboardRepositoryTest
- [x] **Migración completa Lead→Cliente** — seguimientos.prospecto_id nullable, migrarACliente() en conversión, 2 tests nuevos en ProspectoServiceTest
- [x] **Seguimientos para prospectos (gaps)** — CrearSeguimientoRequest acepta prospecto_id como alternativa a cliente_id; index() bifurca por prospecto_id; prospectos/show con timeline + modal nuevo seguimiento; ProspectoFactory + PipelineEstadoFactory; 2 tests nuevos en SeguimientoApiTest

### Baja prioridad
- [ ] **Filtros con persistencia localStorage** — Solo se usa query params actualmente
- [ ] **Skeleton loaders** — Para mejorar UX de carga en kanban y dashboard

---

## Arquitectura

```
app/Domain/{Módulo}/
    Models/       Eloquent models
    DTOs/         readonly — transferencia entre capas
    Repositories/ Interface + Eloquent implementation
    Services/     Lógica de negocio
    Policies/     Gate::policy() registradas en AppServiceProvider
    Exceptions/   Excepciones de dominio

app/Http/
    Controllers/Api/  Delgados — orquestan Service
    Controllers/Web/  Delgados — orquestan Repository + pasan datos a View
    Requests/         Validación + authorize()
    Resources/        Transformación JSON
    Responses/        ApiResponse unificado
```

## Stack
- Laravel 12 / PHP 8.2
- PHPUnit 11 (SQLite en memoria)
- Spatie Laravel Permission — roles: admin, gerente, asesor
- Laravel Sanctum — API token + sesión web
- Tailwind v4 + Alpine.js 3
- ERP Contiflex (SQL Server externo — solo FakeERPRepository en tests)
