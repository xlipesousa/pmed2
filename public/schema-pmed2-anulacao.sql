/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pmed2
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

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

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `chave` varchar(255) NOT NULL,
  `valor` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuracoes_chave_unique` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

--
-- Table structure for table `glosas`
--

DROP TABLE IF EXISTS `glosas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `glosas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pacote_id` bigint(20) unsigned NOT NULL,
  `motivo_glosa_id` bigint(20) unsigned DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor_recursado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_deferido` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `glosas_pacote_id_foreign` (`pacote_id`),
  KEY `glosas_motivo_glosa_id_foreign` (`motivo_glosa_id`),
  CONSTRAINT `glosas_motivo_glosa_id_foreign` FOREIGN KEY (`motivo_glosa_id`) REFERENCES `motivos_glosa` (`id`),
  CONSTRAINT `glosas_pacote_id_foreign` FOREIGN KEY (`pacote_id`) REFERENCES `pacotes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

--
-- Table structure for table `mapa_pacote`
--

DROP TABLE IF EXISTS `mapa_pacote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapa_pacote` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mapa_id` bigint(20) unsigned NOT NULL,
  `pacote_id` bigint(20) unsigned NOT NULL,
  `valor_parcial` decimal(10,2) NOT NULL,
  `empenho` varchar(255) DEFAULT NULL,
  `data_empenho` date DEFAULT NULL,
  `nota_fiscal` varchar(255) DEFAULT NULL,
  `data_nota_fiscal` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mapa_pacote_mapa_id_pacote_id_unique` (`mapa_id`,`pacote_id`),
  KEY `mapa_pacote_pacote_id_foreign` (`pacote_id`),
  CONSTRAINT `mapa_pacote_mapa_id_foreign` FOREIGN KEY (`mapa_id`) REFERENCES `mapas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mapa_pacote_pacote_id_foreign` FOREIGN KEY (`pacote_id`) REFERENCES `pacotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mapas`
--

DROP TABLE IF EXISTS `mapas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `numero_mapa` varchar(255) NOT NULL,
  `data_criacao` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mapas_numero_mapa_unique` (`numero_mapa`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `motivos_glosa`
--

DROP TABLE IF EXISTS `motivos_glosa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `motivos_glosa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movimentacoes`
--

DROP TABLE IF EXISTS `movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacoes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pacote_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `tipo` enum('Movimento de pacote','Edição','Criação de novo Pacote','Notificação de Existência de Glosa','Retirada de Ofício de Glosa','Aguardando Recurso de Glosa','Recurso não recebido','Recebimento de recurso de Glosa','Recurso indeferido','Recurso deferido','Pacote arquivado','Aguardando Limite de Crédito','Pagamento') NOT NULL,
  `origem` varchar(255) DEFAULT NULL,
  `destino` varchar(255) DEFAULT NULL,
  `descricao` text NOT NULL,
  `estado_geral` enum('Normal','Aguardando Limite de Crédito','Arquivado') NOT NULL,
  `estado_glosa` enum('Não identificada','Glosa identificada','Recurso pendente','Recurso deferido','Recurso indeferido') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimentacoes_pacote_id_foreign` (`pacote_id`),
  KEY `movimentacoes_user_id_foreign` (`user_id`),
  CONSTRAINT `movimentacoes_pacote_id_foreign` FOREIGN KEY (`pacote_id`) REFERENCES `pacotes` (`id`),
  CONSTRAINT `movimentacoes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movimentacoes_pacote`
--

DROP TABLE IF EXISTS `movimentacoes_pacote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimentacoes_pacote` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pacote_id` bigint(20) unsigned NOT NULL,
  `acao` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `observacao` text DEFAULT NULL,
  `localizacao_pos_acao` varchar(50) NOT NULL,
  `estado_geral` varchar(50) NOT NULL,
  `estado_glosa` varchar(50) NOT NULL,
  `usuario_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimentacoes_pacote_pacote_id_foreign` (`pacote_id`),
  KEY `movimentacoes_pacote_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `movimentacoes_pacote_pacote_id_foreign` FOREIGN KEY (`pacote_id`) REFERENCES `pacotes` (`id`),
  CONSTRAINT `movimentacoes_pacote_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3353 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ocs_psa`
--

DROP TABLE IF EXISTS `ocs_psa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ocs_psa` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `codigo_interno` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pacotes`
--

DROP TABLE IF EXISTS `pacotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pacotes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ocs_psa_id` bigint(20) unsigned NOT NULL,
  `tipo_id` bigint(20) unsigned NOT NULL,
  `tipo_conta_id` bigint(20) unsigned DEFAULT NULL,
  `motivo_glosa_id` bigint(20) unsigned DEFAULT NULL,
  `descricao_glosa` text DEFAULT NULL,
  `numero_fatura` varchar(255) NOT NULL,
  `data_entrada` date NOT NULL,
  `valor_fatura` decimal(10,2) NOT NULL,
  `valor_glosa` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_pos_lisura` decimal(10,2) DEFAULT NULL,
  `valor_pago` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_pendente` decimal(10,2) DEFAULT NULL,
  `estado_geral` varchar(50) NOT NULL,
  `estado_glosa` varchar(50) NOT NULL,
  `data_notificacao_glosa` datetime DEFAULT NULL,
  `data_limite_retirada` datetime DEFAULT NULL,
  `localizacao_atual` varchar(50) NOT NULL,
  `localizacao_fisica` varchar(255) DEFAULT NULL,
  `anulado` tinyint(1) NOT NULL DEFAULT 0,
  `data_anulacao` timestamp NULL DEFAULT NULL,
  `motivo_anulacao` text DEFAULT NULL,
  `usuario_anulacao_id` bigint(20) unsigned DEFAULT NULL,
  `localizacao_anterior` varchar(50) NOT NULL,
  `ultima_acao` varchar(255) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `data_retirada_oficio` timestamp NULL DEFAULT NULL,
  `data_recebimento_recurso` timestamp NULL DEFAULT NULL,
  `valor_recurso_glosa` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_recursado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_deferido` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `pacotes_ocs_psa_id_foreign` (`ocs_psa_id`),
  KEY `pacotes_tipo_id_foreign` (`tipo_id`),
  KEY `pacotes_tipo_conta_id_foreign` (`tipo_conta_id`),
  KEY `pacotes_usuario_anulacao_id_foreign` (`usuario_anulacao_id`),
  KEY `pacotes_anulado_index` (`anulado`),
  KEY `pacotes_anulado_localizacao_atual_index` (`anulado`,`localizacao_atual`),
  KEY `idx_anulado_localizacao` (`anulado`,`localizacao_atual`),
  KEY `idx_data_anulacao_pacotes` (`data_anulacao`),
  CONSTRAINT `pacotes_ocs_psa_id_foreign` FOREIGN KEY (`ocs_psa_id`) REFERENCES `ocs_psa` (`id`),
  CONSTRAINT `pacotes_tipo_conta_id_foreign` FOREIGN KEY (`tipo_conta_id`) REFERENCES `tipos_conta` (`id`),
  CONSTRAINT `pacotes_tipo_id_foreign` FOREIGN KEY (`tipo_id`) REFERENCES `tipos_pacote` (`id`),
  CONSTRAINT `pacotes_usuario_anulacao_id_foreign` FOREIGN KEY (`usuario_anulacao_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pacotes_anulados_audit`
--

DROP TABLE IF EXISTS `pacotes_anulados_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pacotes_anulados_audit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pacote_id` bigint(20) unsigned NOT NULL COMMENT 'ID do pacote na tabela original',
  `valor_fatura_original` decimal(10,2) NOT NULL COMMENT 'Valor original da fatura',
  `valor_pago_original` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor pago original',
  `valor_pendente_original` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor pendente original',
  `valor_glosa_original` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor glosa original',
  `valor_pos_lisura_original` decimal(10,2) DEFAULT NULL COMMENT 'Valor pós-lisura original',
  `valor_recursado_original` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor recursado original',
  `valor_deferido_original` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Valor deferido original',
  `numero_fatura` varchar(255) NOT NULL COMMENT 'Número da fatura',
  `ocs_psa_nome` varchar(255) DEFAULT NULL COMMENT 'Nome da OCS/PSA no momento',
  `tipo_pacote_nome` varchar(255) DEFAULT NULL COMMENT 'Tipo do pacote no momento',
  `tipo_conta_nome` varchar(255) DEFAULT NULL COMMENT 'Tipo da conta no momento',
  `data_entrada_original` date NOT NULL COMMENT 'Data de entrada original',
  `localizacao_no_momento` varchar(100) NOT NULL COMMENT 'Localização no momento da anulação',
  `estado_geral_no_momento` varchar(100) NOT NULL COMMENT 'Estado geral no momento da anulação',
  `estado_glosa_no_momento` varchar(100) NOT NULL COMMENT 'Estado da glosa no momento da anulação',
  `motivo_anulacao` text NOT NULL COMMENT 'Motivo detalhado da anulação',
  `data_anulacao` timestamp NOT NULL COMMENT 'Data e hora da anulação',
  `usuario_anulacao_id` bigint(20) unsigned NOT NULL COMMENT 'Usuário que executou a anulação',
  `pode_reverter` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Se a anulação pode ser revertida',
  `data_reversao` timestamp NULL DEFAULT NULL COMMENT 'Data da reversão, se houver',
  `usuario_reversao_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Usuário que reverteu',
  `motivo_reversao` text DEFAULT NULL COMMENT 'Motivo da reversão',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pacote_id` (`pacote_id`),
  KEY `idx_data_anulacao` (`data_anulacao`),
  KEY `idx_usuario_anulacao` (`usuario_anulacao_id`),
  KEY `idx_anulacao_reverter` (`data_anulacao`,`pode_reverter`),
  KEY `pacotes_anulados_audit_usuario_reversao_id_foreign` (`usuario_reversao_id`),
  CONSTRAINT `pacotes_anulados_audit_pacote_id_foreign` FOREIGN KEY (`pacote_id`) REFERENCES `pacotes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pacotes_anulados_audit_usuario_anulacao_id_foreign` FOREIGN KEY (`usuario_anulacao_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pacotes_anulados_audit_usuario_reversao_id_foreign` FOREIGN KEY (`usuario_reversao_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoria de pacotes anulados - preserva valores originais para contabilidade';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pesquisas_salvas`
--

DROP TABLE IF EXISTS `pesquisas_salvas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesquisas_salvas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `filtros` text NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pesquisas_salvas_user_id_foreign` (`user_id`),
  CONSTRAINT `pesquisas_salvas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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

--
-- Table structure for table `tipos_conta`
--

DROP TABLE IF EXISTS `tipos_conta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_conta` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tipos_pacote`
--

DROP TABLE IF EXISTS `tipos_pacote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipos_pacote` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','auditor','protocolo','lisura','sire','glosa','arquivo','pagamento') DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-18 23:25:50
