-- =====================================================================
-- EXPLORACIÓN TABLAS CRM — MySQL / phpMyAdmin
-- Ejecuta cada bloque por separado (selecciona y presiona Ctrl+Enter)
-- =====================================================================

-- ----------------------------------------
-- PASO 1 — Tablas que existen y cuántas filas tienen
-- ----------------------------------------
SELECT
    TABLE_NAME        AS tabla,
    TABLE_ROWS        AS filas_aprox,
    CREATE_TIME       AS creada
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'users','clientes','contactos','seguimientos',
      'sf_prospectos','sf_negocios','sf_pipeline_estados',
      'sf_maestros_comerciales','sf_auditoria_pipeline',
      'permissions','roles','model_has_roles'
  )
ORDER BY TABLE_NAME;


-- ----------------------------------------
-- PASO 2 — Estructura completa de cada tabla
-- ----------------------------------------
SELECT
    TABLE_NAME                                        AS tabla,
    ORDINAL_POSITION                                  AS pos,
    COLUMN_NAME                                       AS campo,
    DATA_TYPE                                         AS tipo,
    CASE
        WHEN DATA_TYPE IN ('char','varchar','tinytext','text','mediumtext','longtext')
            THEN CAST(CHARACTER_MAXIMUM_LENGTH AS CHAR)
        WHEN DATA_TYPE IN ('decimal','numeric')
            THEN CONCAT(NUMERIC_PRECISION, ',', NUMERIC_SCALE)
        ELSE ''
    END                                               AS longitud,
    IS_NULLABLE                                       AS acepta_null,
    COLUMN_DEFAULT                                    AS valor_defecto,
    EXTRA                                             AS extra
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
      'users','clientes','contactos','seguimientos',
      'sf_prospectos','sf_negocios','sf_pipeline_estados',
      'sf_maestros_comerciales','sf_auditoria_pipeline',
      'roles','model_has_roles'
  )
ORDER BY TABLE_NAME, ORDINAL_POSITION;


-- ----------------------------------------
-- PASO 3A — Muestra de datos: users
-- ----------------------------------------
SELECT id, name, email, created_at FROM users LIMIT 5;


-- ----------------------------------------
-- PASO 3B — Muestra de datos: clientes CRM
-- ----------------------------------------
SELECT * FROM clientes LIMIT 3;


-- ----------------------------------------
-- PASO 3C — Muestra de datos: sf_negocios
-- ----------------------------------------
SELECT * FROM sf_negocios LIMIT 3;


-- ----------------------------------------
-- PASO 3D — Catálogo completo: sf_pipeline_estados
-- ----------------------------------------
SELECT * FROM sf_pipeline_estados ORDER BY tipo, orden;


-- ----------------------------------------
-- PASO 3E — Catálogo completo: sf_maestros_comerciales
-- ----------------------------------------
SELECT * FROM sf_maestros_comerciales ORDER BY tipo, orden;


-- ----------------------------------------
-- PASO 3F — Muestra de datos: seguimientos
-- ----------------------------------------
SELECT * FROM seguimientos LIMIT 3;


-- ----------------------------------------
-- PASO 3G — Muestra de datos: sf_prospectos
-- ----------------------------------------
SELECT * FROM sf_prospectos LIMIT 3;


-- ----------------------------------------
-- PASO 3H — Roles del sistema
-- ----------------------------------------
SELECT * FROM roles;


-- ----------------------------------------
-- PASO 3I — Asignación roles a usuarios
-- ----------------------------------------
SELECT mhr.*, u.name, u.email, r.name AS rol
FROM model_has_roles mhr
JOIN users u  ON u.id  = mhr.model_id
JOIN roles r  ON r.id  = mhr.role_id
ORDER BY r.name;


-- ----------------------------------------
-- PASO 4 — Asesores en CRM (para cruzar con SIESA)
-- ----------------------------------------
SELECT
    u.id,
    u.name                          AS nombre_asesor,
    COUNT(c.id)                     AS clientes_asignados,
    SUM(CASE WHEN c.estado = 'activo'    THEN 1 ELSE 0 END) AS activos,
    SUM(CASE WHEN c.estado = 'inactivo'  THEN 1 ELSE 0 END) AS inactivos,
    SUM(CASE WHEN c.estado = 'prospecto' THEN 1 ELSE 0 END) AS prospectos
FROM users u
LEFT JOIN clientes c ON c.user_id = u.id
GROUP BY u.id, u.name
ORDER BY clientes_asignados DESC;
