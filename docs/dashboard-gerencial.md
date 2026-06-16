# Dashboard Gerencial — Guía de datos

> Referencia rápida de dónde sale cada número, qué fórmula usa y qué significa.
> Base de datos CRM = MySQL (`sfconnecting`). ERP = SQL Server (`erp_contiflex`).

---

## Filtros globales

| Filtro | Valores | Efecto |
|--------|---------|--------|
| **Periodo** | Mes / Trimestre / Año | Restringe el rango de fechas en todos los cálculos que dependen de facturación ERP |
| **Año** | Año calendario | Año base para presupuesto y facturación |

La compañía activa se toma de `config('crm.compania')` en `.env` → clave `CRM_COMPANIA`.

---

## KPIs superiores (4 tarjetas)

### Presupuesto total
- **Fuente:** tabla `sf_presupuestos` (CRM MySQL)
- **Fórmula:** `SUM(presupuesto)` de todos los asesores del año seleccionado
- **Qué significa:** cuánto se fijó como meta de ventas para el año

### Logrado (mes / trimestre / año)
- **Fuente:** vista ERP `vw_CRM_Ventas_Vendedor_Periodo` (SQL Server Contiflex)
- **Fórmula:** `SUM(VLR_NETO_FACTURADO)` filtrado por compañía, año y meses del periodo
- **Qué significa:** lo que ya facturaron en el periodo seleccionado, según el ERP

### Cumplimiento
- **Fuente:** calculado en PHP, no hay consulta extra
- **Fórmula:** `(Logrado / Presupuesto) × 100`
- **Qué significa:** qué porcentaje de la meta se ha cumplido

### Pipeline activo
- **Fuente:** tabla `sf_negocios` (CRM MySQL)
- **Fórmula:** `SUM(valor_estimado)` de negocios activos que **no** están en estado final (no ganados ni perdidos)
- **Qué significa:** cuánto dinero hay en juego ahora mismo en el CRM, sumando todos los negocios abiertos a valor completo

---

## Presupuesto por vendedor

- **Fuente principal:** `sf_presupuestos` + `sf_vendedor_equivalencia` (CRM) cruzados con `vw_CRM_Ventas_Vendedor_Periodo` (ERP)
- **Lógica:**
  1. Se toman los presupuestos por asesor del CRM
  2. Se busca el código de vendedor SIESA de cada asesor en `sf_vendedor_equivalencia`
  3. Con ese código se consulta el ERP para traer lo facturado
  4. `Cumpl. % = (Logrado / Presupuesto) × 100`
- **Semáforo:** verde ≥ 80 %, amarillo ≥ 50 %, rojo < 50 %
- **Ojo:** si un asesor no tiene código en `sf_vendedor_equivalencia`, su logrado aparece en 0

---

## Salud de cartera

- **Fuente:** vista ERP `vw_CRM_Clientes_Prioritarios` (SQL Server Contiflex)
- **Fórmula:** agrupa clientes por días desde la última compra (`DIAS_DESDE_ULTIMA_COMPRA`)

| Banda | Criterio | Color |
|-------|----------|-------|
| Activo | ≤ 90 días sin comprar | Verde |
| Tibio | 91–180 días | Azul |
| En riesgo | 181–365 días | Naranja/Amarillo |
| Inactivo | > 365 días | Rojo |

- **Valor en riesgo:** suma del facturado de clientes en bandas Tibio + En riesgo + Inactivo
- **Qué significa:** qué tan "viva" está la cartera de clientes según cuándo compraron por última vez

---

## Motivos de pérdida

- **Fuente:** tabla `sf_negocios` + `sf_maestros_comerciales` (CRM MySQL)
- **Fórmula:** cuenta negocios con estado `es_perdido = true` en el periodo, agrupados por motivo
- **Qué significa:** por qué se están perdiendo los negocios. Los motivos los configura el administrador en maestros comerciales

---

## Ciclo de venta promedio

- **Fuente:** tabla `sf_negocios` (CRM MySQL)
- **Fórmula:** `AVG( DATEDIFF(fecha_cierre_real, fecha_creacion) )` por asesor, solo negocios ganados en el periodo
- **Unidad:** días
- **Qué significa:** cuántos días tarda en promedio cada asesor en cerrar un negocio ganado

---

## Actividad del equipo

- **Fuente:** tabla `seguimientos` (CRM MySQL)
- **Fórmula:** `COUNT(*)` por vendedor y tipo de actividad en el periodo
- **Tipos:** Llamada, Visita, Email
- **Qué significa:** cuántas actividades de seguimiento registró cada comercial. Si el número es bajo, o no están registrando o no están trabajando

---

## Top Asesores *(al fondo del dashboard)*

- **Fuente:** tabla `sf_negocios` + `users` (CRM MySQL)
- **Fórmula:**
  - `negocios_ganados` = COUNT de negocios con estado `es_ganado = true` (sin filtro de periodo — es histórico)
  - `valor_ganado` = SUM del `valor_estimado` de esos negocios
- **Ordenado por:** número de negocios ganados, de mayor a menor
- **Límite:** 5 asesores por defecto
- **Ojo:** es acumulado histórico, no filtra por año ni periodo

---

## Oportunidades de Venta Integral *(al fondo del dashboard)*

- **Fuente:** vista ERP `vw_CRM_Clientes_Prioritarios` + CTE sobre `CRM_Consolidado_Ventas_cliente` (SQL Server Contiflex)
- **Qué muestra:** clientes prioritarios (P1 Activo o P2 Riesgo) con su perfil de compra entre compañías
- **Columna Compañía:**

| Valor en BD | Lo que muestra | Color |
|------------|----------------|-------|
| `COMPANIA = 1` | **Formacol** | Rojo claro |
| `COMPANIA = 2` | **Contiflex** | Azul claro |
| Compra en ambas | **Ambas** | Morado |

- **Columna Tipo:**
  - `P1 Activo` = cliente con presupuesto vigente en el ERP
  - `P2 Riesgo` = cliente con presupuesto próximo a vencer o en riesgo
- **Límite:** 50 registros, ordenados por `FACTURADO_ANIO_ACTUAL DESC`
- **Potencial cross-selling:** clientes que solo compran en una compañía — son candidatos a venderles también en la otra

---

## Panorama Gerencial — Clasificación de cartera por vendedor *(al fondo del dashboard)*

- **Fuente:** vista ERP `vw_CRM_Clientes_Prioritarios` (SQL Server Contiflex)
- **Fórmula:** agrupa por vendedor y clasifica sus clientes según `HORIZONTE_PRESUPUESTO`

| Columna | Qué cuenta |
|---------|-----------|
| **Total** | Todos los clientes del vendedor en la vista |
| **VIP** | Clientes con `P1 - PRESUPUESTO ACTIVO` |
| **Urgente** | Clientes con `P2 - PRESUPUESTO EN RIESGO` |
| **Rescate** | Clientes con `P3 - PRESUPUESTO PASADO (RECUPERAR)` |
| **Reactiv.** | Clientes con `P4 - FUERA DE PRESUPUESTO` |
| **Facturación** | `SUM(FACTURADO_ANIO_ACTUAL)` del vendedor |
| **En riesgo** | `SUM(FACTURADO_ANIO_ACTUAL)` solo de clientes P2 y P3 |
| **% Riesgo** | `(clientes P2+P3 / total) × 100` |

- **Semáforo % Riesgo:** rojo ≥ 40 %, amarillo ≥ 20 %, gris < 20 %

---

## Archivos clave

| Qué hace | Archivo |
|----------|---------|
| Controller (recibe la request y arma la vista) | `app/Http/Controllers/Web/DashboardWebController.php` → método `gerencial()` |
| Lógica de negocio (CRM) | `app/Domain/Dashboard/Services/DashboardGerencialService.php` |
| Queries CRM (MySQL) | `app/Domain/Dashboard/Repositories/DashboardGerencialRepository.php` |
| Queries ERP (SQL Server) | `app/Domain/ERP/Repositories/ContiflexERPRepository.php` |
| Vista Blade | `resources/views/dashboards/gerencial.blade.php` |
