-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Pon 23. úno 2026, 22:21
-- Verze serveru: 10.4.28-MariaDB
-- Verze PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Databáze: `admin`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `access_logs`
--

CREATE TABLE `access_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `type` enum('403','404','warning') NOT NULL,
  `path` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `diff` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `user_email`, `action`, `entity`, `entity_id`, `diff`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 3, 'admin@local.cz', 'update', 'users', 4, '{\"active\":{\"from\":0,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-20 21:06:35'),
(2, 2, 3, 'admin@local.cz', 'update', 'users', 4, '{\"active\":{\"from\":1,\"to\":0}}', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-20 21:07:04'),
(3, 15, 46, 'novak@novak.cz', 'insert', 'companies', 16, '{\"slug\":{\"from\":null,\"to\":\"novak-sro-1\"},\"db_name\":{\"from\":null,\"to\":\"work\"},\"name\":{\"from\":null,\"to\":\"Nov\\u00e1k s.r.o.\"},\"ico\":{\"from\":null,\"to\":\"77777779\"},\"active\":{\"from\":null,\"to\":1},\"activated_at\":{\"from\":null,\"to\":\"2026-02-22 11:26:50\"}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/6.8.3 Chrome/122.0.0.0 Safari/537.36', '2026-02-22 11:26:50'),
(4, 15, 46, 'novak@novak.cz', 'insert', 'users', 47, '{\"email\":{\"from\":null,\"to\":\"jan@novak.sk\"},\"employee_number\":{\"from\":null,\"to\":\"admin\"},\"first_name\":{\"from\":null,\"to\":\"Nov\\u00e1k\"},\"last_name\":{\"from\":null,\"to\":\"Jan\"},\"global_role\":{\"from\":null,\"to\":\"admin\"},\"domain_admin\":{\"from\":null,\"to\":1},\"active\":{\"from\":null,\"to\":1},\"company_id\":{\"from\":null,\"to\":16}}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) QtWebEngine/6.8.3 Chrome/122.0.0.0 Safari/537.36', '2026-02-22 11:26:50'),
(5, 2, 3, 'admin@local.cz', 'update', 'users', 4, '{\"global_role\":{\"from\":\"mistr\",\"to\":\"predak\"},\"active\":{\"from\":0,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:14:07');

-- --------------------------------------------------------

--
-- Struktura tabulky `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `type` enum('activate','reset_password') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_created` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL,
  `db_name` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ico` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `activated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `companies`
--

INSERT INTO `companies` (`id`, `slug`, `db_name`, `name`, `ico`, `active`, `created_at`, `activated_at`) VALUES
(1, 'acme', 'work', 'ACME Servis s.r.o.', '12345678', 1, '2026-02-18 12:40:59', '2026-02-18 12:40:59'),
(2, 'beta', 'work', 'Beta Montáže a.s.', '23456789', 1, '2026-02-18 12:40:59', '2026-02-18 12:40:59'),
(6, 'vanek-servisni-sro', 'work', 'Vaněk Servisní s.r.o.', '12589631', 1, '2026-02-21 23:23:29', '2026-02-21 23:23:29'),
(7, 'pirat-as', 'work', 'pirát a.s.', '99999999', 1, '2026-02-22 00:39:52', '2026-02-22 00:39:52'),
(8, 'novak-obuv-sro', 'work', 'Novák Obuv s.r.o.', '77777777', 1, '2026-02-22 01:01:40', '2026-02-22 01:01:40'),
(9, 'laska', 'work', 'laska', '22669988', 1, '2026-02-22 01:25:56', '2026-02-22 01:25:56'),
(12, 'nekde-sroubuji', 'work', 'nekde sroubují', '77777778', 1, '2026-02-22 01:31:26', '2026-02-22 01:31:26'),
(13, 'honzova-hlavni-scasceh', 'work', 'honzova hlavní ščáščěh', '32145698', 1, '2026-02-22 01:37:19', '2026-02-22 01:37:19'),
(14, 'novak-sro', 'work', 'novák s.r.o.', '12378965', 1, '2026-02-22 10:11:04', '2026-02-22 10:11:04'),
(15, 'novak', 'work', 'Novák', '11445577', 1, '2026-02-22 10:28:37', '2026-02-22 10:28:37'),
(16, 'novak-sro-1', 'work', 'Novák s.r.o.', '77777779', 1, '2026-02-22 11:26:50', '2026-02-22 11:26:50'),
(17, 'novak-sro-2', 'work', 'novak s.r.o.', '77776666', 1, '2026-02-22 11:34:38', '2026-02-22 11:34:38');

-- --------------------------------------------------------

--
-- Struktura tabulky `registration_requests`
--

CREATE TABLE `registration_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` char(7) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `teams`
--

INSERT INTO `teams` (`id`, `company_id`, `name`, `color`, `active`) VALUES
(1, 1, 'Elektro tým', '#e74c3c', 1),
(2, 1, 'Servisní tým', '#3498db', 1),
(3, 2, 'Montážní skupina', '#27ae60', 1),
(4, 2, 'Výjezdová jednotka', '#f39c12', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `team_memberships`
--

CREATE TABLE `team_memberships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `role_in_team` varchar(50) DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `team_memberships`
--

INSERT INTO `team_memberships` (`id`, `company_id`, `user_id`, `team_id`, `role_in_team`, `valid_from`, `valid_to`) VALUES
(1, 2, 4, 3, 'member', '2026-02-18', '2026-02-20'),
(2, 2, 3, 3, 'member', '2026-02-18', '2026-02-20'),
(3, 2, 3, 4, 'member', '2026-02-18', '2026-02-23'),
(4, 2, 4, 4, 'leader', '2026-02-18', NULL),
(5, 2, 4, 3, 'member', '2026-02-20', '2026-02-23'),
(6, 2, 3, 3, 'member', '2026-02-20', NULL),
(7, 2, 3, 4, 'member', '2026-02-23', '2026-02-23');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `telefon` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `global_role` enum('root','admin','mistr','predak','monter') NOT NULL DEFAULT 'monter',
  `domain_admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `company_id`, `email`, `employee_number`, `telefon`, `password_hash`, `first_name`, `last_name`, `global_role`, `domain_admin`, `active`, `created_at`) VALUES
(1, 1, 'admin@local.cz', 'A001', NULL, '$2y$10$DYqd3MaL1QlWftkhnnQfLu.wHZvlCggAC6VFUWe.DJCnwfDvP1y3u', 'Petr', 'Vaněk', 'admin', 1, 1, '2026-02-18 12:41:33'),
(2, 1, 'mistr@acme.cz', 'A002', NULL, '$2y$10$DYqd3MaL1QlWftkhnnQfLu.wHZvlCggAC6VFUWe.DJCnwfDvP1y3u', 'Petr', 'Svoboda', 'mistr', 0, 0, '2026-02-18 12:41:33'),
(3, 2, 'admin@local.cz', 'B001', NULL, '$2y$10$DYqd3MaL1QlWftkhnnQfLu.wHZvlCggAC6VFUWe.DJCnwfDvP1y3u', 'Lucie', 'Dvořáková', 'admin', 1, 1, '2026-02-18 12:41:33'),
(4, 2, 'mistr@beta.cz', 'B002', NULL, '$2y$10$DYqd3MaL1QlWftkhnnQfLu.wHZvlCggAC6VFUWe.DJCnwfDvP1y3u', 'Karel', 'Černý', 'predak', 0, 1, '2026-02-18 12:41:33'),
(38, 5, 'pirati@pirati.cz', 'admin', NULL, '$2y$10$Zx4TmbndMLC0aSwl9TEdy.EiGxZ/tIhK3FlqXWjn2xBp48ImiEZea', 'Petr', 'VAněk', 'admin', 1, 1, '2026-02-21 23:00:42'),
(39, 6, 'krtek@bb.bb', 'admin', NULL, '$2y$10$ctnTgirqIPWptHxZaoGQWe7fo6nuIuv0ees9lGQXT2h.NWJkJYL82', 'yxcy', 'ycyxcyxvd', 'admin', 1, 1, '2026-02-21 23:23:29'),
(40, 7, 'honza@primula.cz', 'admin', NULL, '$2y$10$OID/lxTqoIfWDssWft4SPeqheaEMwMRgL1cEM.LNYLBn.oFyr8apS', 'pirát', 'pirátská', 'admin', 1, 1, '2026-02-22 00:39:52'),
(41, 8, 'novak@nova.cz', 'admin', NULL, '$2y$10$b6LaAYoEWU/Ds.MHHgecS.3yZjL.Odh/cgm6PmZGuQKUS0bde7FgW', 'pavel', 'novák', 'admin', 1, 1, '2026-02-22 01:01:40'),
(42, 9, 'ccghcfgh@hjt.gg', 'admin', NULL, '$2y$10$pELcIWHiYKtxLPO95GWN8uYzOygMX4PnrZNPxnJ5VHGhys0UK7YKa', 'fakt', 'jo', 'admin', 1, 1, '2026-02-22 01:25:56'),
(43, 12, 'nekde@nekdo.cz', 'admin', NULL, '$2y$10$h/3vEq/aa4NOTItCvIM68uSfSINOlet2ft7.WpIlMrGz2Qy3z8pRi', 'dfdghdf', 'ddfghdghd', 'admin', 1, 1, '2026-02-22 01:31:27'),
(44, 13, 'honza@hlavni.cz', 'admin', NULL, '$2y$10$Pb/4J4r2BdK2iFKgi9b.m.ma7tbqFxN5u10Kmg474l5I9GlpztVCO', 'honzova', 'honzova', 'admin', 1, 1, '2026-02-22 01:37:19'),
(45, 14, 'novak@novak.cz', 'admin', NULL, '$2y$10$6d3GhIkhbITm1nxBrD8McemRot.QI7FncOJ9vvDt.o.PxGhFN023O', 'Petr', 'Novák', 'admin', 1, 1, '2026-02-22 10:11:04'),
(46, 15, 'novak@novak.cz', 'admin', NULL, '$2y$10$k.LZZYT50wXRSxOzDfzh5eJCs9/DbKmGjjVPDh6Tz2JNuEMk43xAO', 'Novák', 'jan', 'admin', 1, 1, '2026-02-22 10:28:37'),
(47, 16, 'jan@novak.sk', 'admin', NULL, '$2y$10$g8qsR..UQ2ZcmrkJ.0I1mu3IUjyg/Bx4wGmmeDo.bBi7H5l/squqW', 'Novák', 'Jan', 'admin', 1, 1, '2026-02-22 11:26:50'),
(48, 17, 'novak@novak.cz', 'admin', NULL, '$2y$10$SbszqpLnGKgwDVqmcON0MORn4HGd7HrYW3CHQAvWTE7vhc6EdECoq', 'novák', 'jakolev', 'admin', 1, 1, '2026-02-22 11:34:38');

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_time` (`user_id`,`created_at`),
  ADD KEY `idx_ip_time` (`ip_address`,`created_at`),
  ADD KEY `idx_type_time` (`type`,`created_at`),
  ADD KEY `idx_access_logs_company` (`company_id`);

--
-- Indexy pro tabulku `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_entity` (`entity`,`entity_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_audit_logs_company` (`company_id`);

--
-- Indexy pro tabulku `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_token_hash` (`token_hash`),
  ADD KEY `idx_lookup` (`token_hash`,`type`,`used_at`,`expires_at`),
  ADD KEY `idx_user_type_created` (`user_id`,`type`,`created_at`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexy pro tabulku `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_slug` (`slug`),
  ADD UNIQUE KEY `uniq_ico` (`ico`);

--
-- Indexy pro tabulku `registration_requests`
--
ALTER TABLE `registration_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD UNIQUE KEY `uniq_token_hash` (`token_hash`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexy pro tabulku `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teams_company` (`company_id`);

--
-- Indexy pro tabulku `team_memberships`
--
ALTER TABLE `team_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tm_current` (`user_id`,`valid_to`),
  ADD KEY `idx_team_memberships_company` (`company_id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_company_email` (`company_id`,`email`),
  ADD UNIQUE KEY `uniq_company_employee` (`company_id`,`employee_number`),
  ADD KEY `idx_users_company` (`company_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pro tabulku `registration_requests`
--
ALTER TABLE `registration_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT pro tabulku `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `team_memberships`
--
ALTER TABLE `team_memberships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;
