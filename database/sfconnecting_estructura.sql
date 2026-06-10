
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `actividad_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actividad_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `accion` varchar(30) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `metodo` varchar(10) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actividad_log_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `actividad_log_modulo_accion_index` (`modulo`,`accion`),
  KEY `actividad_log_created_at_index` (`created_at`),
  CONSTRAINT `actividad_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo','prospecto') NOT NULL DEFAULT 'prospecto',
  `notas` text DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_nit_unique` (`nit`),
  UNIQUE KEY `clientes_email_unique` (`email`),
  KEY `clientes_estado_ciudad_index` (`estado`,`ciudad`),
  KEY `clientes_user_id_index` (`user_id`),
  CONSTRAINT `clientes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contactos_cliente_id_index` (`cliente_id`),
  CONSTRAINT `contactos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `seguimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seguimientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) unsigned DEFAULT NULL,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `contacto_id` bigint(20) unsigned DEFAULT NULL,
  `tipo` enum('llamada','reunion','email','visita','whatsapp','otro') NOT NULL DEFAULT 'llamada',
  `resultado` enum('exitoso','no_contactado','pendiente','cancelado') NOT NULL DEFAULT 'pendiente',
  `descripcion` text NOT NULL,
  `fecha_seguimiento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `proxima_fecha` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seguimientos_contacto_id_foreign` (`contacto_id`),
  KEY `seguimientos_cliente_id_fecha_seguimiento_index` (`cliente_id`,`fecha_seguimiento`),
  KEY `seguimientos_user_id_proxima_fecha_index` (`user_id`,`proxima_fecha`),
  KEY `seguimientos_prospecto_id_foreign` (`prospecto_id`),
  CONSTRAINT `seguimientos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seguimientos_contacto_id_foreign` FOREIGN KEY (`contacto_id`) REFERENCES `contactos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seguimientos_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `sf_prospectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seguimientos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_auditoria_pipeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_auditoria_pipeline` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auditable_type` varchar(255) NOT NULL,
  `auditable_id` bigint(20) unsigned NOT NULL,
  `evento` enum('cambio_estado','cambio_probabilidad','cambio_forecast','conversion_lead','negocio_ganado','negocio_perdido','reactivacion') NOT NULL,
  `estado_anterior` varchar(100) DEFAULT NULL,
  `estado_nuevo` varchar(100) DEFAULT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `usuario_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sf_auditoria_pipeline_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `sf_auditoria_pipeline_auditable_type_auditable_id_evento_index` (`auditable_type`,`auditable_id`,`evento`),
  KEY `sf_auditoria_pipeline_usuario_id_created_at_index` (`usuario_id`,`created_at`),
  CONSTRAINT `sf_auditoria_pipeline_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_comision_reglas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_comision_reglas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `asesor_id` bigint(20) unsigned DEFAULT NULL,
  `base` enum('ganado','pipeline_ponderado','facturado') NOT NULL DEFAULT 'ganado',
  `porc_default` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vigente_desde` date DEFAULT NULL,
  `vigente_hasta` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sf_comision_reglas_asesor_id_foreign` (`asesor_id`),
  CONSTRAINT `sf_comision_reglas_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_comision_tramos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_comision_tramos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `regla_id` bigint(20) unsigned NOT NULL,
  `desde_valor` decimal(15,2) NOT NULL DEFAULT 0.00,
  `hasta_valor` decimal(15,2) DEFAULT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comision_tramos_regla_index` (`regla_id`),
  CONSTRAINT `sf_comision_tramos_regla_id_foreign` FOREIGN KEY (`regla_id`) REFERENCES `sf_comision_reglas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_maestros_comerciales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_maestros_comerciales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('tipo_negocio','prioridad','fuente_lead','motivo_perdida','tipo_actividad','clasificacion','segmento') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sf_maestros_comerciales_tipo_slug_unique` (`tipo`,`slug`),
  KEY `sf_maestros_comerciales_tipo_activo_orden_index` (`tipo`,`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_metas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_metas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asesor_id` bigint(20) unsigned NOT NULL,
  `compania` smallint(6) NOT NULL,
  `anio` smallint(5) unsigned NOT NULL,
  `mes` tinyint(3) unsigned NOT NULL,
  `meta_valor` decimal(15,2) NOT NULL DEFAULT 0.00,
  `meta_unidades` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `metas_asesor_periodo_unique` (`asesor_id`,`compania`,`anio`,`mes`),
  CONSTRAINT `sf_metas_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_negocios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_negocios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `prospecto_id` bigint(20) unsigned DEFAULT NULL,
  `cliente_id` bigint(20) unsigned DEFAULT NULL,
  `pipeline_estado_id` bigint(20) unsigned NOT NULL,
  `tipo_negocio_id` bigint(20) unsigned DEFAULT NULL,
  `nombre_negocio` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `valor_estimado` decimal(15,2) NOT NULL DEFAULT 0.00,
  `probabilidad_cierre` tinyint(3) unsigned DEFAULT NULL,
  `fecha_estimada_cierre` date DEFAULT NULL,
  `fecha_cierre_real` date DEFAULT NULL,
  `motivo_perdida_id` bigint(20) unsigned DEFAULT NULL,
  `asesor_id` bigint(20) unsigned NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sf_negocios_tipo_negocio_id_foreign` (`tipo_negocio_id`),
  KEY `sf_negocios_motivo_perdida_id_foreign` (`motivo_perdida_id`),
  KEY `sf_negocios_pipeline_estado_id_activo_index` (`pipeline_estado_id`,`activo`),
  KEY `sf_negocios_asesor_id_fecha_estimada_cierre_index` (`asesor_id`,`fecha_estimada_cierre`),
  KEY `sf_negocios_prospecto_id_index` (`prospecto_id`),
  KEY `sf_negocios_cliente_id_index` (`cliente_id`),
  CONSTRAINT `sf_negocios_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `sf_negocios_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_negocios_motivo_perdida_id_foreign` FOREIGN KEY (`motivo_perdida_id`) REFERENCES `sf_maestros_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_negocios_pipeline_estado_id_foreign` FOREIGN KEY (`pipeline_estado_id`) REFERENCES `sf_pipeline_estados` (`id`),
  CONSTRAINT `sf_negocios_prospecto_id_foreign` FOREIGN KEY (`prospecto_id`) REFERENCES `sf_prospectos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_negocios_tipo_negocio_id_foreign` FOREIGN KEY (`tipo_negocio_id`) REFERENCES `sf_maestros_comerciales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_nps_encuestas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_nps_encuestas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint(20) unsigned NOT NULL,
  `negocio_id` bigint(20) unsigned DEFAULT NULL,
  `asesor_id` bigint(20) unsigned DEFAULT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  `comentario` text DEFAULT NULL,
  `canal` enum('email','whatsapp','telefono','presencial','otro') NOT NULL DEFAULT 'email',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sf_nps_encuestas_negocio_id_foreign` (`negocio_id`),
  KEY `sf_nps_encuestas_asesor_id_foreign` (`asesor_id`),
  KEY `nps_cliente_fecha_index` (`cliente_id`,`fecha`),
  CONSTRAINT `sf_nps_encuestas_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_nps_encuestas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sf_nps_encuestas_negocio_id_foreign` FOREIGN KEY (`negocio_id`) REFERENCES `sf_negocios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_pipeline_estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_pipeline_estados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `tipo` enum('prospecto','negocio') NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#6B7280',
  `icono` varchar(50) DEFAULT NULL,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `porcentaje_cierre` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `es_final` tinyint(1) NOT NULL DEFAULT 0,
  `es_ganado` tinyint(1) NOT NULL DEFAULT 0,
  `es_perdido` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sf_pipeline_estados_slug_unique` (`slug`),
  KEY `sf_pipeline_estados_tipo_activo_orden_index` (`tipo`,`activo`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_presupuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_presupuesto` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asesor_id` bigint(20) unsigned NOT NULL,
  `compania` smallint(6) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `presupuesto` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `presupuesto_unique` (`asesor_id`,`compania`,`anio`),
  KEY `presupuesto_periodo_index` (`compania`,`anio`),
  CONSTRAINT `sf_presupuesto_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_prospectos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_prospectos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `empresa` varchar(200) NOT NULL,
  `contacto` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `origen_id` bigint(20) unsigned DEFAULT NULL,
  `estado_pipeline_id` bigint(20) unsigned NOT NULL,
  `prioridad_id` bigint(20) unsigned DEFAULT NULL,
  `valor_estimado` decimal(15,2) DEFAULT NULL,
  `probabilidad_cierre` tinyint(3) unsigned DEFAULT NULL,
  `fecha_proximo_contacto` date DEFAULT NULL,
  `asesor_id` bigint(20) unsigned NOT NULL,
  `observaciones` text DEFAULT NULL,
  `convertido_cliente_id` bigint(20) unsigned DEFAULT NULL,
  `fecha_conversion` timestamp NULL DEFAULT NULL,
  `convertido_por` bigint(20) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sf_prospectos_codigo_unique` (`codigo`),
  KEY `sf_prospectos_origen_id_foreign` (`origen_id`),
  KEY `sf_prospectos_prioridad_id_foreign` (`prioridad_id`),
  KEY `sf_prospectos_convertido_por_foreign` (`convertido_por`),
  KEY `sf_prospectos_estado_pipeline_id_activo_index` (`estado_pipeline_id`,`activo`),
  KEY `sf_prospectos_asesor_id_fecha_proximo_contacto_index` (`asesor_id`,`fecha_proximo_contacto`),
  KEY `sf_prospectos_convertido_cliente_id_index` (`convertido_cliente_id`),
  CONSTRAINT `sf_prospectos_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `sf_prospectos_convertido_cliente_id_foreign` FOREIGN KEY (`convertido_cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_prospectos_convertido_por_foreign` FOREIGN KEY (`convertido_por`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_prospectos_estado_pipeline_id_foreign` FOREIGN KEY (`estado_pipeline_id`) REFERENCES `sf_pipeline_estados` (`id`),
  CONSTRAINT `sf_prospectos_origen_id_foreign` FOREIGN KEY (`origen_id`) REFERENCES `sf_maestros_comerciales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sf_prospectos_prioridad_id_foreign` FOREIGN KEY (`prioridad_id`) REFERENCES `sf_maestros_comerciales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sf_vendedor_equivalencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sf_vendedor_equivalencia` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asesor_id` bigint(20) unsigned NOT NULL,
  `compania` smallint(6) NOT NULL,
  `cod_vendedor_siesa` varchar(20) DEFAULT NULL,
  `rowid_vendedor_siesa` varchar(30) DEFAULT NULL,
  `nombre_vendedor` varchar(200) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vend_equiv_unique` (`asesor_id`,`compania`),
  KEY `vend_equiv_siesa_index` (`compania`,`cod_vendedor_siesa`),
  KEY `vend_equiv_rowid_index` (`compania`,`rowid_vendedor_siesa`),
  CONSTRAINT `sf_vendedor_equivalencia_asesor_id_foreign` FOREIGN KEY (`asesor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(2,'gerente','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(3,'comercial','web','2026-05-22 05:18:09','2026-05-22 05:18:09');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',4),(2,'App\\Models\\User',5),(3,'App\\Models\\User',2),(3,'App\\Models\\User',3),(3,'App\\Models\\User',6);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(1,2),(1,3),(2,1),(2,3),(3,1),(3,3),(4,1),(5,1),(6,1),(6,2),(6,3),(7,1),(7,3),(8,1),(8,3),(9,1),(10,1),(10,2),(10,3),(11,1),(11,3),(12,1),(12,3),(13,1),(14,1),(14,2),(14,3),(15,1),(15,2),(16,1),(16,2),(17,1),(17,2),(18,1),(18,2),(18,3),(19,1),(19,2),(19,3),(20,1),(20,3),(21,1),(21,3),(22,1),(23,1),(24,1),(24,2),(24,3),(25,1),(25,3),(26,1),(26,3),(27,1),(28,1),(29,1),(29,2),(29,3),(30,1),(30,3),(31,1),(31,2),(31,3),(32,1),(32,3),(33,1),(34,1),(35,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'clientes.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(2,'clientes.crear','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(3,'clientes.editar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(4,'clientes.eliminar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(5,'clientes.restaurar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(6,'contactos.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(7,'contactos.crear','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(8,'contactos.editar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(9,'contactos.eliminar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(10,'seguimientos.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(11,'seguimientos.crear','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(12,'seguimientos.editar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(13,'seguimientos.eliminar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(14,'dashboard.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(15,'dashboard.kpis','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(16,'reportes.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(17,'reportes.exportar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(18,'erp.consultar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(19,'prospectos.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(20,'prospectos.crear','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(21,'prospectos.editar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(22,'prospectos.eliminar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(23,'prospectos.convertir','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(24,'negocios.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(25,'negocios.crear','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(26,'negocios.editar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(27,'negocios.eliminar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(28,'negocios.cerrar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(29,'pipeline.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(30,'pipeline.mover','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(31,'forecast.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(32,'maestros.ver','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(33,'maestros.gestionar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(34,'usuarios.gestionar','web','2026-05-22 05:18:09','2026-05-22 05:18:09'),(35,'roles.gestionar','web','2026-05-22 05:18:09','2026-05-22 05:18:09');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

