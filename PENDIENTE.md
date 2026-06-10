# SFconnecting — Tareas pendientes

## 🔴 Prioridad alta

### Ejecutar migración de auditoría
MySQL estaba apagado cuando se creó. Al levantar XAMPP:
```bash
php artisan migrate
```
Crea la tabla `actividad_log` que registra toda la actividad de los usuarios.

### Subir a GitHub
El repo local ya está inicializado. Falta:
```bash
git remote add origin https://github.com/TU-USUARIO/sfconnecting-crm.git
git branch -M main
git push -u origin main
```

### Commit de cambios recientes
Varios archivos cambiaron después del primer commit (rate limiting, auditoría, mapeo vendedores, presupuestos). Hacer commit:
```bash
git add .
git commit -m "feat: rate limiting, auditoría, presupuestos y mapeo vendedores"
```

---

## 🟡 Prioridad media

### Filtros del dashboard gerencial
Los botones **Mes / Trimestre / Año** en `/gerencial` son solo visuales.
Solo se implementaron en `/mi-desempeno`. Falta replicar la lógica en:
- `DashboardGerencialRepository` — aceptar array de meses
- `DashboardGerencialService` — pasarlos desde el controlador
- `DashboardWebController@gerencial` — calcular meses según período
- Vista `dashboards/gerencial.blade.php` — convertir botones en links

### Contraseñas más fuertes
Actualmente solo se exige mínimo 8 caracteres. Para producción:
```php
// app/Http/Requests/Usuarios/CrearUsuarioRequest.php
use Illuminate\Validation\Rules\Password;

'password' => ['required', Password::min(8)->mixedCase()->numbers()]
```
Aplicar también en `ActualizarUsuarioRequest.php`.

### Vista de auditoría para admin
La tabla `actividad_log` se llena automáticamente pero solo es consultable
desde phpMyAdmin. Se puede crear una vista en `/auditoria` (solo admin)
con filtros por usuario, módulo, acción y fecha.

---

## 🟢 Prioridad baja

### Tests de los módulos nuevos
Siguiendo el ciclo TDD del CLAUDE.md, faltan tests para:
- `DashboardVendedorService`
- `DashboardGerencialService`
- `PresupuestoRepository`
- `VendedorEquivalenciaRepository`
- `RegistrarActividad` (middleware)

### Headers de seguridad HTTP
Para producción agregar en el servidor (`.htaccess` o Nginx):
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
```
O via middleware Laravel.

### Forzar HTTPS en producción
```php
// app/Providers/AppServiceProvider.php
if (app()->isProduction()) {
    URL::forceScheme('https');
}
```

---

## 📋 Datos operativos (no es código — lo hace el jefe/admin)

| Qué | Dónde | Estado |
|---|---|---|
| Cargar presupuestos reales 2026 | `/presupuestos` | Pendiente |
| Mapear asesores reales a SIESA | `/mapeo-vendedores` | Pendiente |
| Crear usuarios reales (asesores) | `/usuarios` | Pendiente |
| Arreglar `COD_VENDEDOR` en años anteriores de la vista SIESA | SQL Server del jefe | Pendiente |

---

## ✅ Completado (referencia)

- CRM completo: Clientes, Contactos, Prospectos, Negocios, Seguimientos
- Pipeline Kanban + Forecast
- Dashboard con inteligencia ERP (Contiflex)
- Dashboard "Mi desempeño" con filtros Mes/Trimestre/Año
- Dashboard "Visión gerencial"
- Roles: admin, gerente, comercial — con filtrado automático por asesor
- CRUD Presupuestos
- CRUD Mapeo vendedores SIESA
- Rate limiting en login (5 intentos/minuto)
- Auditoría automática en tabla `actividad_log`
- Git inicializado + `.gitignore` + `.env.example` + `README.md`
- Seeders demo para instalación limpia
