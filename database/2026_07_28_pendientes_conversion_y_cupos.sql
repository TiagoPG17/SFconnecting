-- =====================================================================
-- SFconnecting — cambios de base de datos pendientes de aplicar
-- Generado: 2026-07-28
-- Corresponde a las migraciones sin commitear / sin aplicar a la BD real:
--
--   1. 2026_07_14_000001_add_revision_contabilidad_to_clientes_table
--   2. 2026_07_17_000001_alter_sf_maestros_comerciales_tipo_add_sector
--   3. 2026_07_17_000002_add_sector_id_to_sf_negocios_table
--   4. 2026_07_17_000003_add_compania_to_sf_prospectos_table
--   5. 2026_07_24_000001_make_negocio_id_nullable_on_sf_solicitudes_credito_table
--   6. 2026_07_24_000002_add_referencias_comerciales_to_sf_solicitudes_credito_table
--
-- IMPORTANTE:
--   - Haz un backup / snapshot antes de correrlo.
--   - MySQL no puede revertir DDL dentro de una transacción, así que
--     corre el script completo de una sola vez en DBeaver.
--   - Al final se registra cada migración en la tabla `migrations` para
--     que `php artisan migrate` no intente volver a correrlas después
--     y truene con "columna ya existe".
--   - Ajusta el nombre de la base de datos si tu conexión en DBeaver
--     no la tiene seleccionada por defecto.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1) clientes: revisión de contabilidad (módulo de conversión)
-- ---------------------------------------------------------------------
ALTER TABLE `clientes`
    ADD COLUMN `datos_carga` json DEFAULT NULL AFTER `notas`,
    ADD COLUMN `revisado_contabilidad_en` timestamp NULL DEFAULT NULL AFTER `datos_carga`,
    ADD COLUMN `revisado_contabilidad_por` bigint(20) unsigned DEFAULT NULL AFTER `revisado_contabilidad_en`;

ALTER TABLE `clientes`
    ADD CONSTRAINT `clientes_revisado_contabilidad_por_foreign`
        FOREIGN KEY (`revisado_contabilidad_por`) REFERENCES `users` (`id`) ON DELETE SET NULL;


-- ---------------------------------------------------------------------
-- 2) sf_maestros_comerciales: agrega 'sector' al enum `tipo`
-- ---------------------------------------------------------------------
ALTER TABLE `sf_maestros_comerciales`
    MODIFY COLUMN `tipo` enum(
        'tipo_negocio',
        'prioridad',
        'fuente_lead',
        'motivo_perdida',
        'tipo_actividad',
        'clasificacion',
        'segmento',
        'sector'
    ) NOT NULL;


-- ---------------------------------------------------------------------
-- 3) sf_negocios: sector_id (FK a sf_maestros_comerciales)
-- ---------------------------------------------------------------------
ALTER TABLE `sf_negocios`
    ADD COLUMN `sector_id` bigint(20) unsigned DEFAULT NULL AFTER `tipo_negocio_id`;

ALTER TABLE `sf_negocios`
    ADD CONSTRAINT `sf_negocios_sector_id_foreign`
        FOREIGN KEY (`sector_id`) REFERENCES `sf_maestros_comerciales` (`id`) ON DELETE SET NULL;


-- ---------------------------------------------------------------------
-- 4) sf_prospectos: compania
-- ---------------------------------------------------------------------
ALTER TABLE `sf_prospectos`
    ADD COLUMN `compania` tinyint(3) unsigned DEFAULT NULL AFTER `asesor_id`;

ALTER TABLE `sf_prospectos`
    ADD INDEX `sf_prospectos_compania_index` (`compania`);


-- ---------------------------------------------------------------------
-- 5) sf_solicitudes_credito: negocio_id pasa a ser opcional
--    (permite radicar una solicitud de cupo ligada solo al cliente,
--    sin negocio, desde la conversión de prospecto o desde la ficha
--    del cliente)
-- ---------------------------------------------------------------------
ALTER TABLE `sf_solicitudes_credito`
    DROP FOREIGN KEY `sf_solicitudes_credito_negocio_id_foreign`;

ALTER TABLE `sf_solicitudes_credito`
    MODIFY COLUMN `negocio_id` bigint(20) unsigned DEFAULT NULL;

ALTER TABLE `sf_solicitudes_credito`
    ADD CONSTRAINT `sf_solicitudes_credito_negocio_id_foreign`
        FOREIGN KEY (`negocio_id`) REFERENCES `sf_negocios` (`id`);


-- ---------------------------------------------------------------------
-- 6) sf_solicitudes_credito: referencias comerciales + consignación
-- ---------------------------------------------------------------------
ALTER TABLE `sf_solicitudes_credito`
    ADD COLUMN `referencias_comerciales` json DEFAULT NULL AFTER `justificacion`,
    ADD COLUMN `inventario_consignacion` tinyint(1) DEFAULT NULL AFTER `referencias_comerciales`;


-- ---------------------------------------------------------------------
-- Registrar las 6 migraciones para que `php artisan migrate` no las
-- vuelva a intentar correr más adelante.
-- ---------------------------------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`)
SELECT v.migration, (SELECT COALESCE(MAX(batch), 0) FROM `migrations`) + 1
FROM (
    SELECT '2026_07_14_000001_add_revision_contabilidad_to_clientes_table' AS migration
    UNION ALL SELECT '2026_07_17_000001_alter_sf_maestros_comerciales_tipo_add_sector'
    UNION ALL SELECT '2026_07_17_000002_add_sector_id_to_sf_negocios_table'
    UNION ALL SELECT '2026_07_17_000003_add_compania_to_sf_prospectos_table'
    UNION ALL SELECT '2026_07_24_000001_make_negocio_id_nullable_on_sf_solicitudes_credito_table'
    UNION ALL SELECT '2026_07_24_000002_add_referencias_comerciales_to_sf_solicitudes_credito_table'
) AS v
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations` m WHERE m.migration = v.migration
);
