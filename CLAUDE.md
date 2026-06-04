# SFconnecting — Guía para Claude Code

## Stack
- **Laravel 12** / PHP 8.2
- **PHPUnit 11** con SQLite en memoria para tests
- **Spatie Laravel Permission** — roles: `admin`, `gerente`, `asesor`
- **Laravel Sanctum** — autenticación API por token
- **ERP Contiflex** — SQL Server externo (nunca tocar en tests)

---

## Arquitectura obligatoria

```
app/Domain/{Módulo}/
    Models/          Eloquent models
    DTOs/            Objetos inmutables de transferencia de datos
    Repositories/    Interface + implementación Eloquent
    Services/        Lógica de negocio
    Policies/        Autorización (Gate::policy)
    Exceptions/      Excepciones de dominio
app/Http/
    Controllers/Api/ Controladores DELGADOS — solo orquestan
    Requests/        Validación + autorización (authorize())
    Resources/       Transformación JSON
    Responses/       ApiResponse (formato único)
```

## Patrones OBLIGATORIOS

- **Repository Pattern**: toda query de DB va en un Repository. Cero queries en Services, Controllers ni Policies.
- **Service Layer**: toda lógica de negocio va en el Service. Los Controllers solo llaman al Service.
- **DTOs tipados**: usar DTOs inmutables (`readonly`) para pasar datos entre capas. Nunca pasar arrays sin tipar entre Service y Repository.
- **ApiResponse**: toda respuesta JSON usa `ApiResponse::success()`, `ApiResponse::created()`, `ApiResponse::error()`. Formato: `{"success": bool, "message": "", "data": {}}`.
- **Policies registradas**: siempre registrar en `AppServiceProvider::boot()` via `Gate::policy()`. Los modelos de `app/Domain/` no se auto-descubren.

## Patrones PROHIBIDOS

- `NO` queries fuera de Repositories (ni en Services, ni en Controllers)
- `NO` lógica de negocio en Controllers
- `NO` acceder al ERP directamente desde Controllers
- `NO` usar el ERP real en tests — siempre `FakeERPRepository`
- `NO` generar código sin tests primero
- `NO` controllers que no sean delgados

---

## TDD — Ciclo obligatorio

```
RED   → escribir el test que falla
GREEN → escribir el mínimo código para que pase
REFACTOR → limpiar sin romper
```

### Orden dentro de cada módulo
1. Unit: `{Módulo}RepositoryTest` — prueba el repositorio con `RefreshDatabase`
2. Unit: `{Módulo}ServiceTest` — prueba el servicio con **mocks** del repositorio
3. Unit: `{Módulo}PolicyTest` — 100 % de cobertura de ramas
4. Feature: `{Módulo}ApiTest` — ciclo HTTP completo con SQLite en memoria

### Cobertura mínima por capa
| Capa | Mínimo |
|---|---|
| Services | 90 % |
| Repositories | 85 % |
| Controllers | 80 % |
| ERP / integración | 95 % |
| Policies | 100 % |
| Requests | 100 % |

---

## ERP (Contiflex)

- **Nunca** instanciar `ContiflexERPRepository` en tests.
- El `AppServiceProvider` inyecta automáticamente `FakeERPRepository` cuando `APP_ENV=testing`.
- Para simular escenarios en tests: `$this->app->instance(ERPRepositoryInterface::class, $fake)` tras configurar el fake.

```php
// Configurar en el test
$fake = new FakeERPRepository();
$fake->agregarCliente(['nit' => '123456789', 'razon_social' => 'Empresa SA']);
$this->app->instance(ERPRepositoryInterface::class, $fake);
```

---

## Respuestas API

```php
// 200
ApiResponse::success($data, 'Mensaje opcional');

// 201
ApiResponse::created($resource, 'Recurso creado.');

// 422 validación / negocio
ApiResponse::error('Mensaje', $errors, 422);

// 404
ApiResponse::notFound();

// 403
ApiResponse::forbidden();
```

---

## Roles y permisos

| Permiso | admin | gerente | asesor |
|---|---|---|---|
| clientes.* | ✓ | ✓ | ver/crear/editar |
| contactos.* | ✓ | ✓ | ver/crear/editar |
| seguimientos.* | ✓ | ✓ | ver/crear/editar |
| dashboard.kpis | ✓ | ✓ | ver (solo propios) |
| reportes.* | ✓ | ✓ | — |
| erp.consultar | ✓ | ✓ | ✓ |
| usuarios.gestionar | ✓ | — | — |
| roles.gestionar | ✓ | — | — |

---

## Comandos frecuentes

```bash
php artisan test                          # suite completa
php artisan test --filter NombreTest      # test específico
php artisan test --stop-on-failure        # detener al primer fallo
./vendor/bin/pint                         # formatear código (PSR-12)
./vendor/bin/pint --test                  # verificar sin modificar (CI)
php artisan db:seed --class=RolesPermisosSeeder
```

---

## Convenciones

- `declare(strict_types=1)` en todos los archivos PHP.
- Nombres en **español** para métodos de dominio: `crear`, `actualizar`, `buscarPorId`, `porAsesor`.
- Nombres en **inglés** para infraestructura Laravel: `index`, `store`, `show`, `update`, `destroy`.
- Tests: método `test_snake_case_en_espanol()`.
- Un test = una aserción principal (puede haber auxiliares de estado).
