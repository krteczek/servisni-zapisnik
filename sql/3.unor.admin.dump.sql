-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Úte 03. úno 2026, 13:02
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

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

--
-- Vypisuji data pro tabulku `access_logs`
--

INSERT INTO `access_logs` (`id`, `company_id`, `user_id`, `ip_address`, `type`, `path`, `method`, `user_agent`, `created_at`) VALUES
(1, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/12/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:46:58'),
(2, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:47:11'),
(3, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:47:22'),
(4, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/mistr', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:09'),
(5, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/predak', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:10'),
(6, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/monter', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:11'),
(7, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/reset', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:13'),
(8, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/admin', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:15'),
(9, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/mistr', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:15'),
(10, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/predak', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:16'),
(11, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/monter', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:18'),
(12, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/mistr', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:26'),
(13, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/predak', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:31'),
(14, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/monter', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:33'),
(15, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/switch-role/mistr', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 07:58:35'),
(16, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/12/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 08:28:49'),
(17, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 08:29:01'),
(18, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-29 08:31:44'),
(19, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/11/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:32:24'),
(20, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/21/password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:38:14'),
(21, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/11/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:42:06'),
(22, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/11/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:42:30'),
(23, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/12/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:42:44'),
(24, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/12/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:44:17'),
(25, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/admin/audit/35', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-30 23:57:17'),
(26, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/11/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 00:05:09'),
(27, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 00:10:37'),
(28, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 00:14:39'),
(29, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/teams/13/edit', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 00:35:47'),
(30, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/21/password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 12:39:08'),
(31, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/23/send-reset-password', 'POST', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-01 02:25:08'),
(32, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/23/send-reset-password', 'POST', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-01 02:26:57'),
(33, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/23/send-reset-password', 'POST', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-01 02:31:11'),
(34, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/22', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-01 23:48:06'),
(35, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/22/send-reset-password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:02:53'),
(36, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/22/send-reset-password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:03:14'),
(37, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/22/send-reset-password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:03:20'),
(38, 0, 1, '127.0.0.1', '404', '/servisni-zapisnik/public/users/22/send-reset-password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:06:34'),
(39, 0, NULL, '127.0.0.1', '404', '/servisni-zapisnik/public/register', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 14:53:27'),
(40, 0, NULL, '127.0.0.1', '404', '/servisni-zapisnik/public/forgot-password', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 15:07:27'),
(41, 0, NULL, '127.0.0.1', '404', '/servisni-zapisnik/public/register', 'GET', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 15:07:33');

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
(1, 0, 1, NULL, 'update', 'users', 7, '{\"password\":{\"changed\":true}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-26 23:54:38'),
(2, 0, 1, NULL, 'update', 'team_memberships', 87, '{\"role_in_team\":{\"from\":\"member\",\"to\":\"leader\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 00:17:07'),
(3, 0, 1, NULL, 'update', 'team_memberships', 87, '{\"valid_to\":{\"from\":null,\"to\":\"2026-01-27\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 00:17:10'),
(4, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":2},\"team_id\":{\"from\":null,\"to\":5},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 00:17:12'),
(5, 0, 1, NULL, 'update', 'team_memberships', 94, '{\"role_in_team\":{\"from\":\"member\",\"to\":\"leader\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 00:17:19'),
(6, 0, 1, NULL, 'insert', 'teams', NULL, '{\"name\":{\"from\":null,\"to\":\"Admini\"},\"color\":{\"from\":null,\"to\":\"#408080\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:06:39'),
(7, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":21},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"leader\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:11:11'),
(8, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":23},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"leader\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:17:07'),
(9, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":24},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:17:08'),
(10, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":25},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:17:16'),
(11, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":1},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:17:19'),
(12, 0, 1, NULL, 'insert', 'users', NULL, '{\"email\":{\"from\":null,\"to\":\"nejaky@email.cz\"},\"employee_number\":{\"from\":null,\"to\":\"66666687\"},\"first_name\":{\"from\":null,\"to\":\"nějaký\"},\"last_name\":{\"from\":null,\"to\":\"zaměstnanec\"},\"global_role\":{\"from\":null,\"to\":\"monter\"},\"created_at\":{\"from\":null,\"to\":\"2026-01-27 01:34:51\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:34:51'),
(13, 0, 1, NULL, 'insert', 'teams', NULL, '{\"name\":{\"from\":null,\"to\":\"lumpové\"},\"color\":{\"from\":null,\"to\":\"#2196f3\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:35:44'),
(14, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":22},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:35:50'),
(15, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":21},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:35:52'),
(16, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":26},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:35:54'),
(17, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":23},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 01:36:02'),
(18, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":22},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:39:40'),
(19, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":25},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:39:42'),
(20, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":23},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:39:45'),
(21, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":1},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:39:50'),
(22, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":24},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:39:51'),
(23, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":21},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:54:53'),
(24, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":22},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:54:56'),
(25, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":26},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:55:00'),
(26, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":1},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:55:32'),
(27, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":23},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:55:33'),
(28, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":24},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 14:55:35'),
(29, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":21},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:15:49'),
(30, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":22},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:15:55'),
(31, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":24},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:15:56'),
(32, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":23},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:15:58'),
(33, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":26},\"team_id\":{\"from\":null,\"to\":11},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:15:59'),
(34, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":21},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:16:46'),
(35, 0, 1, NULL, 'insert', 'team_memberships', NULL, '{\"user_id\":{\"from\":null,\"to\":22},\"team_id\":{\"from\":null,\"to\":12},\"role_in_team\":{\"from\":null,\"to\":\"member\"},\"valid_from\":{\"from\":null,\"to\":\"2026-01-27\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-27 16:16:47'),
(37, 1, 1, 'admin@local.cz', 'update', 'users', 26, '{\"employee_number\":{\"from\":\"66666687\",\"to\":\"66666687 77\"},\"first_name\":{\"from\":\"n\\u011bjak\\u00fd   vbvccv\",\"to\":\"n\\u011bjak\\u00fd   vbvccv bb\"},\"global_role\":{\"from\":\"monter\",\"to\":\"predak\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 02:31:27'),
(38, 1, 1, 'admin@local.cz', 'insert', 'users', 28, '{\"email\":{\"from\":null,\"to\":\"kolotoc@kolo.cz\"},\"employee_number\":{\"from\":null,\"to\":\"zzTuZ\"},\"first_name\":{\"from\":null,\"to\":\"Email\"},\"last_name\":{\"from\":null,\"to\":\"Emailovi\\u010d\"},\"password_hash\":{\"from\":null,\"to\":\"$2y$10$B4tfkyUk8toWtUvFvRcwTeXCPkmccBjVh7k6kvMIDpdQA4xzD.PIa\"},\"global_role\":{\"from\":null,\"to\":\"predak\"},\"created_at\":{\"from\":null,\"to\":\"2026-01-31 02:33:03\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 02:33:03'),
(39, 1, 1, 'admin@local.cz', 'update', 'users', 24, '{\"first_name\":{\"from\":\"kkkjjjjhhh\",\"to\":\"kkkj\"},\"last_name\":{\"from\":\"nbnnbn ycxyx\",\"to\":\"nbnnbn ycxyx nnbgt\"},\"global_role\":{\"from\":\"predak\",\"to\":\"mistr\"},\"active\":{\"from\":0,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 02:41:03'),
(40, 1, 1, 'admin@local.cz', 'insert', 'users', 29, '{\"email\":{\"from\":null,\"to\":\"kolotoc1@kolo.cz\"},\"employee_number\":{\"from\":null,\"to\":\"jjjhzt\"},\"first_name\":{\"from\":null,\"to\":\"ertzui\"},\"last_name\":{\"from\":null,\"to\":\"bvbb\"},\"global_role\":{\"from\":null,\"to\":\"monter\"},\"company_id\":{\"from\":null,\"to\":1}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-01-31 02:42:03'),
(41, 1, 1, 'admin@local.cz', 'update', 'users', 27, '{\"email\":{\"from\":\"nekdo@nekde.cz\",\"to\":\"martinovic.martin@email.cz\"}}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:50:50');

-- --------------------------------------------------------

--
-- Struktura tabulky `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `type` enum('activate','reset_password') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_created` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Vypisuji data pro tabulku `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `company_id`, `token_hash`, `type`, `expires_at`, `used_at`, `ip_created`, `user_agent`, `created_at`) VALUES
(1, 22, 1, 'e3c6113f2f06993239e9a1f9027e3cae8252eb8cce725e53a2fed2575468e6e3', 'reset_password', '2026-02-02 09:56:02', '2026-02-02 09:45:09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:41:02'),
(2, 22, 1, '72b60779f5464e97b918399c3ccd522c222cf2742da3eba741005a39513dc2f4', 'reset_password', '2026-02-02 10:00:09', '2026-02-02 09:46:01', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:45:09'),
(3, 22, 1, 'cbf9fd122fc2cc07533bc22878416e1512b7a271641b765ec1ed884a13fa4855', 'reset_password', '2026-02-02 10:01:01', '2026-02-02 09:46:36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:46:01'),
(4, 22, 1, 'fbb25c330d5b045d82b03fbd3c141145578ed340ee4c53383f09323b81ca61d9', 'reset_password', '2026-02-02 10:01:36', '2026-02-02 09:47:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:46:36'),
(5, 22, 1, '5688d6ae07a6807ed13a7a1553afb61872a0f23f666247d826caf0069b62e396', 'reset_password', '2026-02-02 10:02:17', '2026-02-02 09:48:52', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:47:17'),
(6, 22, 1, 'be12eebcbbfe83b452cf0813dcfd8b99ad2c7665be832a1d2a6bb6a491178b12', 'reset_password', '2026-02-02 10:03:52', '2026-02-02 09:50:36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:48:52'),
(7, 22, 1, 'f1c2b3a0ca5f2f1996cbe41c611703283c8cd4af64b247c2ca81069f0c8e39ca', 'reset_password', '2026-02-02 10:05:36', '2026-02-02 09:52:36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:50:36'),
(8, 22, 1, '6e7983d666d2bc3e1b8af4d563706d3d7cd42ee979646498596f383233df27cd', 'reset_password', '2026-02-02 10:07:36', '2026-02-02 09:54:05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:52:36'),
(9, 22, 1, '96be48fa88b88fbc5db129f93c3094469dfecfbed954172da2c6bd88e926fb1b', 'reset_password', '2026-02-02 10:09:05', '2026-02-02 09:54:37', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:54:05'),
(10, 22, 1, 'f5310098b4f1614753bc98b0b765520c7d0804ef1e1ea5e414eda1aab0d90826', 'reset_password', '2026-02-02 10:09:37', '2026-02-02 09:56:11', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:54:37'),
(11, 22, 1, '31dfdad990488aa1845e4e272051cc1dcd2ee5ed2f6678476de844a5924f848c', 'reset_password', '2026-02-02 10:11:11', '2026-02-02 10:00:09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 09:56:11'),
(12, 22, 1, '70c99bb95ad9e695abdaad20294ee0a09b815076996aca9681d152e5e4739792', 'reset_password', '2026-02-02 10:15:09', '2026-02-02 10:00:55', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:00:09'),
(13, 22, 1, '89f0a4faff34e5d705f9d9f3daf1d2d7328922c71657c363570f2021f2f7b513', 'reset_password', '2026-02-02 10:15:55', '2026-02-02 10:04:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:00:55'),
(14, 22, 1, '94dc25692f7b222b8a2a890ec022c680f5508c6a8a7df55e97cc95cb242cd35f', 'reset_password', '2026-02-02 10:19:17', '2026-02-02 10:04:48', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:04:17'),
(15, 22, 1, '4ccdaf013a2a9728e0d9b906d61133744a702878fb65840ad6c50788073b3438', 'reset_password', '2026-02-02 10:19:48', '2026-02-02 10:29:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:04:48'),
(16, 22, 1, 'd4e5a1b6be5d6632ef626fcd9819c25c49964bf9dc588d70edf63d452352eb99', 'reset_password', '2026-02-02 10:44:45', '2026-02-02 10:48:40', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:29:45'),
(17, 22, 1, 'b59cf8be29ea851f3972fd66f3703770770352a09a6179077870e5ae36de37d0', 'reset_password', '2026-02-02 11:03:40', '2026-02-02 10:49:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:48:40'),
(18, 22, 1, '95230c7fc27b6ced38959d95b30b93d1855a53f1c551964e0d40f3a8c7a580c8', 'reset_password', '2026-02-02 11:04:29', '2026-02-02 10:57:15', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:49:29'),
(19, 22, 1, 'cd6a09ab1246d189216434ea91a09223287cd5ab4590cb81ff32bbdbeabb6a3c', 'reset_password', '2026-02-02 11:12:15', '2026-02-02 10:58:15', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:57:15'),
(20, 22, 1, '06ed99e7d56d0bd7e5fb21fe14fcf2d71e28812f4fd753f55701a35cf64912e7', 'reset_password', '2026-02-02 11:13:15', '2026-02-02 10:59:02', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:58:15'),
(21, 22, 1, 'eeac4bf31a5dbaf34d93eddae16152b0c478547e7e3fff38d6c09c5a5285a7a5', 'reset_password', '2026-02-02 11:14:02', '2026-02-02 10:59:09', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:59:02'),
(22, 22, 1, '14197e5bf29e6b0e1ea0047eb898c968e1aa0f92739316e4084ff098ac7b1751', 'reset_password', '2026-02-02 11:14:09', '2026-02-02 11:03:01', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 10:59:09'),
(23, 22, 1, '92b06295ad28dbdd14ca0e233a47e9895d98cb0b6c6092e5661b190fc159b06c', 'reset_password', '2026-02-02 11:18:01', '2026-02-02 11:03:38', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:03:01'),
(24, 22, 1, '9134b2930c4c0f6bc7532354ae4e4421feaa4addadd8dbad07b9b3382a048e8c', 'reset_password', '2026-02-02 11:18:38', '2026-02-02 11:04:38', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:03:38'),
(25, 22, 1, 'c1302e3852efdd9207bfb3ebb45d31ffaa85cce4f046513fb673f4b5492a22b6', 'reset_password', '2026-02-02 11:19:38', '2026-02-02 11:04:43', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:04:38'),
(26, 22, 1, '29fc3fe701bc10805136b7d2137b2ba40f2743f3dfc8ec20d887e1f6fb8e8714', 'reset_password', '2026-02-02 11:19:43', '2026-02-02 11:06:42', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:04:43'),
(27, 22, 1, '3704d9e2c02a7fce313519989a1a735dfdf11142930945ef459f0b5b98c9722a', 'reset_password', '2026-02-02 11:21:42', '2026-02-02 11:07:27', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:06:42'),
(28, 22, 1, 'd5fc184a49a0fa51a5e25832b5e379461f15929d9dae1f683c0fd8edc15e6ff3', 'reset_password', '2026-02-02 11:22:27', '2026-02-02 11:07:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:07:27'),
(29, 22, 1, 'f0fefe212bfcd49d5eb1312dd0552ac9ffecf93db8395791dfa3605c0aeac022', 'reset_password', '2026-02-02 11:22:45', '2026-02-02 11:10:44', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:07:45'),
(30, 22, 1, 'cde8484e69e4192d5ca318807d61312b5e5dd7e76df33f15f2cb7564fa32812f', 'reset_password', '2026-02-02 11:25:44', '2026-02-02 11:14:28', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:10:44'),
(31, 22, 1, '3fea170eebfbc06409aaab1cbc579081099fac823b410d342b7eb89e7900b8e2', 'reset_password', '2026-02-02 11:29:28', '2026-02-02 11:21:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:14:28'),
(32, 22, 1, 'c42e40b18009d00100e2f8ea4610e61b32cad137dda13d7012113b7322def190', 'reset_password', '2026-02-02 11:36:29', '2026-02-02 11:30:19', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:21:29'),
(33, 22, 1, 'c81042c9fef899f3dcdcb8e01fe47d4323738d6ea3848e16cadcdf6ef28e1f14', 'reset_password', '2026-02-02 11:45:19', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 11:30:19'),
(34, 27, 1, '5acbc1fd7c4ec69a26ea99131650b78d11165a8eabbb04af3e3d4e65673f684e', 'reset_password', '2026-02-02 12:20:35', '2026-02-02 12:14:57', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 12:05:35'),
(35, 27, 1, 'dcc59f3343583fd772de8a9dfa7e6724cee5d593373b0075c7c88d157f6ebfed', 'reset_password', '2026-02-02 12:29:57', '2026-02-02 12:16:54', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 12:14:57'),
(36, 27, 1, '56f813241dd97196f040d90ab9389888e0cde947a1067f16f1b10ce89149ab79', 'reset_password', '2026-02-02 12:31:54', '2026-02-02 12:42:38', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 12:16:54'),
(37, 27, 1, '79aae3e19d4beba602ab56c59abd24473463fdcd67c0c3b059c5c1df858f1b38', 'reset_password', '2026-02-02 12:57:38', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 12:42:38'),
(38, 28, 1, '4746619658e5a2fab249edd8707946cd2ba94df5d29aadb42d8e33233e481af1', 'reset_password', '2026-02-02 13:14:19', '2026-02-02 13:26:32', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 12:59:19'),
(39, 29, 1, 'ff65c248e3d9582aa3d464eeb37491f1b450ffc737d968cdc13dcf4d6d5fd584', 'reset_password', '2026-02-02 13:34:59', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 13:19:59'),
(40, 28, 1, 'b655cdd98b3c6caae5928abd422a80c3998ab2f41c94ea95a6b4fbda526b7846', 'reset_password', '2026-02-02 13:41:32', '2026-02-02 13:31:36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 13:26:32'),
(41, 28, 1, 'ef948d13efdc64f47202e0ecf35e3aa5a29682c544e10fc494cccf7ac4a713f9', 'reset_password', '2026-02-02 13:46:36', '2026-02-02 13:32:06', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 13:31:36'),
(42, 28, 1, '53ce26e87c02ceb5004e2dd1fce8b63346d4db8aa1b6a862f95b54eaf282f495', 'reset_password', '2026-02-02 13:53:18', '2026-02-02 13:55:47', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 13:38:18'),
(43, 28, 1, '8db5cc7c729d316e5a3f23db13a83c22e52be0c03417e742ff67ee636da8d8f9', 'reset_password', '2026-02-02 14:10:47', '2026-02-02 13:58:49', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-02 13:55:47');

-- --------------------------------------------------------

--
-- Struktura tabulky `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL,
  `db_name` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `companies`
--

INSERT INTO `companies` (`id`, `slug`, `db_name`, `name`, `active`, `created_at`) VALUES
(1, 'krteczek', 'work', 'Krteczek system', 1, '2026-01-26 01:26:52'),
(2, 'tomasovo', 'work', 'Tomáš s.r.o', 1, '2026-01-27 00:51:01'),
(3, 'tondovo', 'work', 'AnToníček s.r.o', 1, '2026-01-27 00:52:13');

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
(1, 0, 'Vodárna 1', '#e5a50a', 0),
(2, 0, 'E2', '#0000a0', 1),
(3, 0, 'dorry', '#000000', 0),
(4, 0, 'koksovna', '#63452c', 1),
(5, 0, 'Aplikace', '#004040', 1),
(6, 0, 'Aplikace', '#004040', 0),
(7, 0, 'Aplikace', '#004040', 0),
(8, 0, 'ggf', '#2196f3', 0),
(9, 0, 'kliná', '#ff80ff', 0),
(10, 0, 'Název týmu', '#000040', 0),
(11, 1, 'Admini', '#408080', 1),
(12, 1, 'lumpové', '#800040', 1),
(13, 1, 'máničky', '#ff0080', 1);

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
(1, 0, 1, 1, 'member', '2026-01-11', '2026-01-14'),
(4, 0, 6, 1, 'member', '2026-01-11', '2026-01-14'),
(5, 0, 1, 1, 'leader', '2026-01-11', '2026-01-14'),
(6, 0, 6, 1, 'leader', '2026-01-11', '2026-01-14'),
(7, 0, 2, 1, 'leader', '2026-01-11', '2026-01-11'),
(8, 0, 4, 1, 'leader', '2026-01-11', '2026-01-13'),
(9, 0, 1, 2, 'leader', '2026-01-11', '2026-01-11'),
(10, 0, 5, 2, 'leader', '2026-01-11', '2026-01-14'),
(11, 0, 1, 2, 'member', '2026-01-11', '2026-01-14'),
(12, 0, 0, 3, 'leader', '2026-01-13', NULL),
(13, 0, 0, 3, 'leader', '2026-01-13', NULL),
(14, 0, 0, 3, 'leader', '2026-01-13', NULL),
(15, 0, 4, 3, 'leader', '2026-01-13', '2026-01-14'),
(16, 0, 2, 3, 'member', '2026-01-13', '2026-01-14'),
(17, 0, 2, 4, 'leader', '2026-01-13', '2026-01-13'),
(18, 0, 6, 4, 'leader', '2026-01-13', '2026-01-13'),
(19, 0, 1, 1, 'leader', '2026-01-13', '2026-01-13'),
(20, 0, 2, 1, 'leader', '2026-01-13', '2026-01-13'),
(21, 0, 1, 1, 'leader', '2026-01-13', '2026-01-13'),
(22, 0, 3, 1, 'leader', '2026-01-13', '2026-01-13'),
(23, 0, 1, 1, 'leader', '2026-01-13', '2026-01-13'),
(24, 0, 3, 1, 'leader', '2026-01-13', '2026-01-13'),
(25, 0, 1, 1, 'leader', '2026-01-13', '2026-01-13'),
(26, 0, 3, 1, 'leader', '2026-01-13', '2026-01-13'),
(27, 0, 4, 1, 'leader', '2026-01-13', '2026-01-13'),
(28, 0, 5, 1, 'leader', '2026-01-13', '2026-01-13'),
(29, 0, 3, 1, 'leader', '2026-01-13', '2026-01-13'),
(30, 0, 1, 1, 'leader', '2026-01-13', '2026-01-13'),
(31, 0, 3, 1, 'leader', '2026-01-13', '2026-01-13'),
(32, 0, 4, 1, 'leader', '2026-01-13', '2026-01-13'),
(33, 0, 5, 1, 'leader', '2026-01-13', '2026-01-13'),
(34, 0, 6, 1, 'leader', '2026-01-13', '2026-01-14'),
(35, 0, 7, 1, 'member', '2026-01-13', '2026-01-14'),
(36, 0, 8, 1, 'member', '2026-01-13', '2026-01-14'),
(37, 0, 9, 1, 'member', '2026-01-13', '2026-01-14'),
(38, 0, 10, 2, 'member', '2026-01-13', '2026-01-13'),
(39, 0, 11, 2, 'leader', '2026-01-13', '2026-01-14'),
(40, 0, 12, 2, 'member', '2026-01-13', '2026-01-13'),
(41, 0, 13, 3, 'member', '2026-01-13', '2026-01-14'),
(42, 0, 14, 3, 'leader', '2026-01-13', '2026-01-14'),
(43, 0, 15, 3, 'member', '2026-01-13', '2026-01-14'),
(44, 0, 2, 2, 'leader', '2026-01-13', '2026-01-13'),
(45, 0, 15, 4, 'leader', '2026-01-14', '2026-01-21'),
(46, 0, 2, 2, 'leader', '2026-01-14', '2026-01-14'),
(47, 0, 2, 2, 'member', '2026-01-14', '2026-01-14'),
(48, 0, 6, 1, 'leader', '2026-01-14', '2026-01-14'),
(49, 0, 1, 1, 'leader', '2026-01-14', '2026-01-14'),
(50, 0, 3, 2, 'leader', '2026-01-14', '2026-01-14'),
(51, 0, 4, 1, 'leader', '2026-01-14', '2026-01-14'),
(52, 0, 3, 2, 'leader', '2026-01-14', '2026-01-14'),
(53, 0, 2, 1, 'leader', '2026-01-14', '2026-01-14'),
(54, 0, 1, 1, 'leader', '2026-01-14', '2026-01-14'),
(55, 0, 5, 1, 'member', '2026-01-14', '2026-01-14'),
(56, 0, 6, 1, 'leader', '2026-01-14', '2026-01-14'),
(57, 0, 1, 1, 'leader', '2026-01-14', '2026-01-14'),
(58, 0, 2, 1, 'leader', '2026-01-14', '2026-01-14'),
(59, 0, 15, 4, 'leader', '2026-01-14', '2026-01-21'),
(60, 0, 1, 1, 'leader', '2026-01-14', '2026-01-14'),
(61, 0, 1, 2, 'leader', '2026-01-14', NULL),
(62, 0, 2, 2, 'member', '2026-01-14', '2026-01-14'),
(63, 0, 4, 2, 'member', '2026-01-14', NULL),
(64, 0, 3, 2, 'member', '2026-01-14', NULL),
(65, 0, 6, 2, 'member', '2026-01-14', '2026-01-14'),
(66, 0, 0, 1, 'leader', '2026-01-14', NULL),
(67, 0, 0, 1, 'leader', '2026-01-14', NULL),
(68, 0, 3, 1, 'leader', '2026-01-14', '2026-01-14'),
(69, 0, 5, 1, 'member', '2026-01-14', '2026-01-14'),
(70, 0, 1, 1, 'member', '2026-01-14', '2026-01-21'),
(71, 0, 2, 1, 'guest', '2026-01-14', NULL),
(72, 0, 5, 1, 'member', '2026-01-14', NULL),
(73, 0, 4, 1, 'member', '2026-01-14', '2026-01-21'),
(74, 0, 3, 1, 'leader', '2026-01-14', '2026-01-21'),
(75, 0, 21, 1, 'leader', '2026-01-21', NULL),
(76, 0, 12, 1, 'member', '2026-01-21', NULL),
(77, 0, 8, 1, 'member', '2026-01-21', NULL),
(78, 0, 23, 1, 'member', '2026-01-21', NULL),
(79, 0, 21, 2, 'member', '2026-01-21', NULL),
(80, 0, 13, 4, 'member', '2026-01-21', NULL),
(81, 0, 19, 4, 'member', '2026-01-21', NULL),
(82, 0, 10, 4, 'member', '2026-01-21', NULL),
(83, 0, 11, 4, 'member', '2026-01-21', NULL),
(84, 0, 6, 5, 'leader', '2026-01-21', '2026-01-21'),
(85, 0, 14, 5, 'member', '2026-01-21', '2026-01-21'),
(86, 0, 24, 5, 'member', '2026-01-21', NULL),
(87, 0, 2, 5, 'leader', '2026-01-21', '2026-01-27'),
(88, 0, 21, 5, 'member', '2026-01-21', '2026-01-21'),
(89, 0, 3, 7, 'leader', '2026-01-22', '2026-01-22'),
(90, 0, 1, 7, 'member', '2026-01-22', '2026-01-22'),
(91, 0, 7, 7, 'leader', '2026-01-22', '2026-01-22'),
(92, 0, 16, 7, 'member', '2026-01-22', '2026-01-23'),
(93, 0, 2, 7, 'member', '2026-01-23', '2026-01-23'),
(94, 0, 2, 5, 'leader', '2026-01-27', NULL),
(95, 1, 21, 11, 'guest', '2026-01-27', '2026-01-27'),
(96, 1, 23, 11, 'leader', '2026-01-27', '2026-01-27'),
(97, 1, 24, 11, 'member', '2026-01-27', '2026-01-27'),
(98, 1, 25, 11, 'member', '2026-01-27', '2026-01-27'),
(99, 1, 1, 11, 'member', '2026-01-27', NULL),
(100, 1, 22, 12, 'member', '2026-01-27', '2026-01-27'),
(101, 1, 21, 12, 'member', '2026-01-27', '2026-01-27'),
(102, 1, 26, 12, 'member', '2026-01-27', '2026-01-27'),
(103, 1, 23, 12, 'leader', '2026-01-27', '2026-01-27'),
(104, 1, 22, 12, 'member', '2026-01-27', '2026-01-27'),
(105, 1, 25, 12, 'member', '2026-01-27', '2026-01-27'),
(106, 1, 23, 12, 'member', '2026-01-27', '2026-01-27'),
(107, 1, 1, 12, 'member', '2026-01-27', '2026-01-27'),
(108, 1, 24, 12, 'member', '2026-01-27', '2026-01-27'),
(109, 1, 21, 12, 'member', '2026-01-27', '2026-01-27'),
(110, 1, 22, 12, 'member', '2026-01-27', '2026-01-27'),
(111, 1, 26, 12, 'member', '2026-01-27', '2026-01-27'),
(112, 1, 1, 12, 'member', '2026-01-27', '2026-01-27'),
(113, 1, 23, 12, 'member', '2026-01-27', NULL),
(114, 1, 24, 12, 'member', '2026-01-27', NULL),
(115, 1, 21, 11, 'member', '2026-01-27', '2026-01-28'),
(116, 1, 22, 11, 'guest', '2026-01-27', '2026-01-28'),
(117, 1, 24, 11, 'member', '2026-01-27', '2026-01-27'),
(118, 1, 23, 11, 'member', '2026-01-27', '2026-01-27'),
(119, 1, 26, 11, 'member', '2026-01-27', NULL),
(120, 1, 21, 12, 'member', '2026-01-27', NULL),
(121, 1, 22, 12, 'member', '2026-01-27', NULL),
(122, 1, 23, 11, 'leader', '2026-01-28', NULL),
(123, 1, 22, 13, 'member', '2026-01-28', NULL),
(124, 1, 1, 13, 'member', '2026-01-28', NULL),
(125, 1, 24, 13, 'member', '2026-01-28', NULL),
(126, 1, 23, 13, 'member', '2026-01-28', '2026-01-28'),
(127, 1, 23, 13, 'leader', '2026-01-28', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `employee_number` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `global_role` enum('root','admin','mistr','predak','monter') NOT NULL DEFAULT 'monter',
  `domain_admin` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `company_id`, `email`, `employee_number`, `password_hash`, `first_name`, `last_name`, `global_role`, `domain_admin`, `active`, `created_at`) VALUES
(1, 1, 'admin@local.cz', 'EMP-0001', '$2y$10$TC7N3i4Z4EapM3jSNdwb9uszueVnZNri5H8/NVoST5CYP9iR/Jgze', 'Petr', 'Vaněk', 'admin', 1, 1, '2025-12-31 00:23:25'),
(2, 2, 'a@a.a', 'EMP-0002', '$2y$10$x8HveD2p0E5gIUlhIk6eMONXwArzSCKTjlr4JnhZILgDfo7.rTUw2', 'aaaa', 'a', 'monter', 0, 1, '2026-01-06 10:15:04'),
(3, 2, 's@s.s', 'EMP-0003', '$2y$10$esSu3lGnGQIa5B310hUXSOWDYKCqWVns84v7D.f2ucMyjz9A9pmpK', 's', 's', 'mistr', 0, 0, '2026-01-06 10:37:26'),
(4, 2, 'ff@ff.ff', 'fff', '$2y$10$VCdw16bA.5aVAeRM7cvxp.Py.ZNMUoa5GihSV6olt2P6KBusCVpaK', 'fff', 'fff', 'mistr', 0, 1, '2026-01-07 04:53:00'),
(5, 2, 'krteczek01@gmail.com', '56169', '$2y$10$TC7N3i4Z4EapM3jSNdwb9uszueVnZNri5H8/NVoST5CYP9iR/Jgze', 'Petr', 'Vaněk', 'admin', 1, 1, '2026-01-08 10:38:19'),
(6, 2, 'vycxvycvy@bbb.bb', '122334', '$2y$10$Ntdnz7trbwW.ZiuXSh82QuD3fU8i6VU9XFyZykjDBl7ocaOWJer5.', 'lojza', 'lojzovič', 'monter', 0, 1, '2026-01-09 15:04:24'),
(7, 2, 'u1@test.cz', 'EMP-0007', '$2y$10$OqoSHEePOExH.mVpwSBcheeSIDAtS/j54yV1fgW3lWjwKcxG/YNDW', 'Jan', 'Novák', 'monter', 0, 1, '2026-01-13 22:11:08'),
(8, 2, 'u2@test.cz', 'EMP-0008', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Karel', 'Dvořák', 'monter', 0, 1, '2026-01-13 22:11:08'),
(9, 2, 'u3c@test.cz', 'EMP-0009', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Josefovič', 'Svoboda', 'predak', 0, 1, '2026-01-13 22:11:08'),
(10, 2, 'u4@test.cz', 'EMP-0010', '$2y$10$cbh6./yVldhqoW/K2iXC6eozEMePtTpol8RDX33Rm6EUzacZUfcWa', 'Pavel', 'Král', 'predak', 0, 1, '2026-01-13 22:11:08'),
(11, 2, 'u5@test.cz', 'EMP-0011', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Martin', 'Kučera', 'mistr', 0, 1, '2026-01-13 22:11:08'),
(12, 2, 'u6@test.cz', 'EMP-0012', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Radek', 'Blažek', 'monter', 0, 1, '2026-01-13 22:11:08'),
(13, 3, 'u7@test.cz', 'EMP-0013', '$2y$10$TGU6zRUBPWmcUHwIl40QyORbgnJI8.gsC1mCkqdOGx8p975/LUN.m', 'Tomáš', 'Horák', 'monter', 0, 1, '2026-01-13 22:11:08'),
(14, 3, 'u8@test.cz', 'EMP-0014', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Lukáš', 'Marek', 'predak', 0, 1, '2026-01-13 22:11:08'),
(15, 3, 'u9@test.cz', 'EMP-0015', '$2y$10$Wzj4mH4mTQp7ZC1QZt7VnO0w7Lk9yXk0Qz1B8g0kR2n4K9z9YFj7e', 'Ondřej', 'Veselý', 'monter', 0, 1, '2026-01-13 22:11:08'),
(16, 3, 'stanislava@gmail.com', '13244', '$2y$10$EFyeopmSCqpcZ25gXbbFvOmll82w22ZIOS4bhE2bFZd/mO/68toEm', 'stanislava', 'Vaňková', 'monter', 1, 1, '2026-01-16 00:54:39'),
(17, 3, 'nekfg@bb.bb', '12432', '$2y$10$EIGXS1LUH9PoD2urR3j4De8XgJeFpWU9jBz4AxljpHiSIDJ/F08eu', 'nscfcfsfc', 'vbgcx', 'monter', 0, 1, '2026-01-16 01:00:05'),
(18, 3, 'nekffg@bb.bb', '124324', '$2y$10$E2eX.Ydr9ZJYMdwT8iPU6usHeeEfOMcYNkLkluPy6HsduoZvYFYMm', 'nscfcfsfc', 'vbgcx', 'monter', 0, 1, '2026-01-16 01:01:36'),
(19, 3, 'nnnnnnn@nn.nn', '12345', '$2y$10$cu/vrAc1RG2AQCwTOdxvHO2YtAFNLjSv6KM3UZ7UJFxFl59bWVibm', 'kotě', 'kotěcí', 'monter', 0, 1, '2026-01-19 13:03:23'),
(20, 3, 'bbbb@bb.bbb', '8877776', '$2y$10$6ioeq2off6juGk4KC/dJyeLXLlI2gnw5.IFfD4/HUs/HRz3C7O.xC', 'Uživatelé', 'Uživatelé Uživatelé', 'monter', 0, 1, '2026-01-19 13:54:14'),
(21, 1, 'barbara@barbara.cz', 'é09áíý6677', '$2y$10$MnO7QOfbnjXdJgGEw2QOmuMS363PrywtdfqYxZDY1/BMmDo4ZVhIu', 'brambora jojokk', 'bramborákovák', 'monter', 0, 0, '2026-01-19 14:46:33'),
(22, 1, 'nekdo@nekde.off', 'nekde;', '$2y$10$zpx3Uf.1aWEJa3OQMSHrbOQxz4EkdtdmH8g.cg62fRZt8Vz3Yn14O', 'nekdeghfghfg', 'nekdenn', 'monter', 0, 1, '2026-01-19 14:47:58'),
(23, 1, 'eeg@gg.gg', 'ggggg hhhtew', '$2y$10$EcPNgXdoxfYlExl3DfmCoO9rFdul1i.qU4VDCmFYf5dd0z1vq5d3u', 'ggggg vccxccfttr', 'ggg', 'monter', 0, 1, '2026-01-19 14:50:46'),
(24, 1, 'jjjjj@hjj.jj', 'fgggtggg', '$2y$10$ZvrdfvelFgoTFYKgpmGkmutyoaIcHPBmQlxv.MWXKXKo6wqiy5Ng.', 'kkkj', 'nbnnbn ycxyx nnbgt', 'mistr', 0, 1, '2026-01-21 14:02:36'),
(25, 1, 'root@krteczek.system', 'ROOT', '$2y$10$TC7N3i4Z4EapM3jSNdwb9uszueVnZNri5H8/NVoST5CYP9iR/Jgze', 'System', 'Root', 'root', 1, 1, '2026-01-26 01:29:27'),
(26, 1, 'nejaky@email.cz', '66666687 77', '$2y$10$0yrNKXTVVdnCXLi2l3QmQ.LEuY0vlJ/5JVfuszHoboVqz2laDTiB6', 'nějaký   vbvccv bb', 'zaměstnanec         vcgcffcg', 'predak', 0, 1, '2026-01-27 01:34:51'),
(27, 1, 'martinovic.martin@email.cz', 'AS112', '$2y$10$dOp/5rC2hsF25SykVkdbfu5Yk1.MIeg6iuanjhjgHKPTGnKOCAzee', 'Martin', 'Martinovička', 'monter', 0, 1, '2026-01-30 23:34:34'),
(28, 1, 'kolotoc@kolo.cz', 'zzTuZ', '$2y$10$B4tfkyUk8toWtUvFvRcwTeXCPkmccBjVh7k6kvMIDpdQA4xzD.PIa', 'Email', 'Emailovič', 'predak', 0, 1, '2026-01-31 02:33:03'),
(29, 1, 'kolotoc1@kolo.cz', 'jjjhzt', '$2y$10$vQrbPBlj6Hc5bmr2IBJ3VOeH6ZsEse4pVs1.05Q2ir9uGLK2Ub/3q', 'ertzui', 'bvbb', 'monter', 0, 1, '2026-01-31 02:42:03');

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
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexy pro tabulku `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_slug` (`slug`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pro tabulku `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pro tabulku `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pro tabulku `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pro tabulku `team_memberships`
--
ALTER TABLE `team_memberships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;
