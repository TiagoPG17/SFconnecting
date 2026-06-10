# SFconnecting CRM

[![CI](https://github.com/TiagoPG17/SFconnecting/actions/workflows/ci.yml/badge.svg)](https://github.com/TiagoPG17/SFconnecting/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License](https://img.shields.io/badge/licencia-MIT-green)](LICENSE)

CRM comercial empresarial desarrollado con **Laravel 12** e integrado con el ERP **Contiflex (SIESA)** vía SQL Server. Permite gestionar el ciclo de ventas completo y visualizar datos reales del ERP en dashboards interactivos.

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 / PHP 8.2 |
| Arquitectura | Domain-Driven Design (DDD) |
| Base de datos principal | MySQL 8 |
| Base de datos analítica | SQL Server (ERP Contiflex) |
| Autenticación | Laravel Sanctum |
| Roles y permisos | Spatie Laravel Permission |
| Frontend | Blade + Alpine.js v3 + Tailwind CSS v3 |
| Gráficos | Chart.js 4 |
| Testing | PHPUnit 11 con SQLite en memoria |

---

## Módulos

- **Dashboard comercial** — KPIs, pipeline, forecast, inteligencia del ERP
- **Mi desempeño** — dashboard personal del asesor con datos reales de ventas SIESA
- **Visión gerencial** — presupuesto vs logrado, retención/churn, ciclo de venta
- **Prospectos** — pipeline kanban de prospectos
- **Negocios** — pipeline kanban de oportunidades comerciales + forecast
- **Clientes** — cartera de clientes con historial de seguimientos
- **Seguimientos** — registro de actividad comercial
- **Presupuestos** — asignación y gestión de metas anuales por asesor
- **Mapeo vendedores** — vinculación de usuarios CRM con vendedores SIESA
- **Reportes** — análisis de conversión, forecast y actividad

---

## Arquitectura

```
app/Domain/{Módulo}/
    Models/          Modelos Eloquent
    DTOs/            Objetos inmutables de transferencia
    Repositories/    Interface + implementación Eloquent
    Services/        Lógica de negocio
    Policies/        Autorización por recurso
    Exceptions/      Excepciones de dominio
app/Http/
    Controllers/Api/ API REST (Sanctum)
    Controllers/Web/ Controladores Blade
    Requests/        Validación + autorización
    Resources/       Transformación JSON
```

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/TiagoPG17/SFconnecting.git
cd sfconnecting

# 2. Instalar dependencias
composer install
npm install && npm run build

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Migraciones y seeders
php artisan migrate --seed

# 6. (Opcional) ERP Contiflex
# Configurar ERP_HOST, ERP_DATABASE, ERP_USERNAME, ERP_PASSWORD en .env
# Sin esta conexión el CRM funciona en modo degradado

# 7. Iniciar servidor
php artisan serve
```

### Usuarios demo

| Email | Contraseña | Rol |
|---|---|---|
| admin@sfconnecting.co | Admin2026* | Administrador |
| gerente@sfconnecting.co | Gerente2026* | Gerente |
| asesor@sfconnecting.co | Asesor2026* | Comercial |

---

## Integración ERP

El CRM se conecta al ERP Contiflex (SIESA) vía SQL Server en **modo solo lectura** para:
- Ventas reales YTD por vendedor
- Presupuesto vs logrado con semáforo de ritmo
- Ranking del equipo comercial
- Retención y churn de clientes por recencia
- Inteligencia comercial (clientes en fuga, en riesgo, expansión)

Sin acceso al ERP todos los módulos del CRM funcionan normalmente. Las secciones de datos ERP muestran valores vacíos con degradación elegante.

---

## Roles

| Permiso | Admin | Gerente | Comercial |
|---|---|---|---|
| Visión gerencial | ✓ | ✓ | — |
| Mi desempeño | — | — | ✓ |
| Presupuestos | ✓ | ✓ | — |
| Mapeo vendedores | ✓ | — | — |
| Clientes / Negocios | Todos | Todos | Solo los propios |

---

## Comandos útiles

```bash
php artisan test                     # Suite completa
./vendor/bin/pint                    # Formatear código PSR-12
php artisan db:seed                  # Cargar datos demo
```
