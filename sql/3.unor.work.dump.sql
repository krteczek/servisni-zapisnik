-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Úte 03. úno 2026, 13:05
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Databáze: `work`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `recurring_tasks`
--

CREATE TABLE `recurring_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `frequency_type` enum('weekly','monthly','quarterly','semiannual','yearly') NOT NULL,
  `frequency_value` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `next_due_date` date NOT NULL,
  `warning_days_before` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `work_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recurring_task_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','done','cancelled') NOT NULL DEFAULT 'open',
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `done_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `tasks`
--

INSERT INTO `tasks` (`id`, `company_id`, `parent_id`, `team_id`, `work_order_id`, `recurring_task_id`, `title`, `description`, `status`, `created_by_user_id`, `created_at`, `done_at`) VALUES
(1, 0, NULL, 1, 1, NULL, 'Kontrola zařízení', 'Vizuální kontrola a základní diagnostika', 'open', 1, '2026-01-13 22:51:13', NULL),
(2, 0, NULL, 1, 1, NULL, 'Oprava netěsnosti', 'Výměna těsnění a dotažení přírub', 'open', 1, '2026-01-13 22:51:13', NULL),
(3, 0, NULL, 2, 2, NULL, 'Revize elektro', 'Proměření rozvaděče', 'done', 4, '2026-01-13 22:51:13', NULL),
(4, 0, NULL, 2, 2, NULL, 'Výměna jističe', 'Vadný jistič nahrazen novým', 'done', 4, '2026-01-13 22:51:13', NULL),
(5, 0, NULL, 1, 3, NULL, 'Příprava pracoviště', 'Odstavení zařízení', 'done', 1, '2026-01-13 22:51:13', NULL),
(6, 0, NULL, 3, 3, NULL, 'Mechanická oprava', 'Výměna ložisek', 'open', 4, '2026-01-13 22:51:13', NULL),
(7, 0, NULL, 4, 4, NULL, 'Čištění technologie', 'Odstranění usazenin', 'open', 2, '2026-01-13 22:51:13', NULL),
(8, 0, NULL, 2, 5, NULL, 'Diagnostika závady', 'Zjištění příčiny poruchy', 'open', 5, '2026-01-13 22:51:13', NULL),
(9, 0, NULL, 2, 5, NULL, 'Návrh řešení', 'Zpracování postupu opravy', 'open', 5, '2026-01-13 22:51:13', NULL),
(10, 0, NULL, 1, 1, NULL, 'Kontrola zařízení', 'Vizuální kontrola a základní diagnostika', '', 1, '2026-01-13 23:11:31', NULL),
(11, 0, NULL, 1, 1, NULL, 'Oprava netěsnosti', 'Výměna těsnění a dotažení přírub', '', 1, '2026-01-13 23:11:31', NULL),
(12, 0, NULL, 2, 1, NULL, 'Elektrická revize', 'Kontrola kabeláže a připojení', 'done', 4, '2026-01-13 23:11:31', NULL),
(13, 0, NULL, 2, 2, NULL, 'Revize elektro', 'Proměření rozvaděče', 'done', 4, '2026-01-13 23:11:31', NULL),
(14, 0, NULL, 2, 2, NULL, 'Výměna jističe', 'Vadný jistič nahrazen novým', 'done', 4, '2026-01-13 23:11:31', NULL),
(15, 0, NULL, 3, 2, NULL, 'Úprava rozvaděče', 'Instalace nových modulů', '', 5, '2026-01-13 23:11:31', NULL),
(16, 0, NULL, 1, 3, NULL, 'Příprava pracoviště', 'Odstavení zařízení', 'done', 1, '2026-01-13 23:11:31', NULL),
(17, 0, NULL, 3, 3, NULL, 'Mechanická oprava', 'Výměna ložisek', '', 4, '2026-01-13 23:11:31', NULL),
(18, 0, NULL, 4, 3, NULL, 'Kalibrace senzorů', 'Nastavení měřících zařízení', '', 2, '2026-01-13 23:11:31', NULL),
(19, 0, NULL, 4, 4, NULL, 'Čištění technologie', 'Odstranění usazenin', '', 2, '2026-01-13 23:11:31', NULL),
(20, 0, NULL, 1, 4, NULL, 'Kontrola filtrace', 'Revize filtračních jednotek', '', 1, '2026-01-13 23:11:31', NULL),
(21, 0, NULL, 2, 5, NULL, 'Diagnostika závady', 'Zjištění příčiny poruchy', '', 5, '2026-01-13 23:11:31', NULL),
(22, 0, NULL, 2, 5, NULL, 'Návrh řešení', 'Zpracování postupu opravy', '', 5, '2026-01-13 23:11:31', NULL),
(23, 0, NULL, 3, 6, NULL, 'Preventivní údržba', 'Komplexní servis stroje', 'done', 4, '2026-01-13 23:11:31', NULL),
(24, 0, NULL, 4, 6, NULL, 'Výměna oleje', 'Výměna mazacích kapalin', '', 2, '2026-01-13 23:11:31', NULL),
(25, 0, NULL, 1, 6, NULL, 'Kontrola bezpečnosti', 'Test bezpečnostních prvků', 'done', 1, '2026-01-13 23:11:31', NULL),
(26, 0, NULL, 1, 52, NULL, 'Oprava hydrauliky', 'Oprava hydraulického okruhu', '', 1, '2026-01-13 23:11:31', NULL),
(27, 0, NULL, 2, 52, NULL, 'Elektrická diagnostika', 'Hledání zkratu v obvodu', '', 4, '2026-01-13 23:11:31', NULL),
(28, 0, NULL, 3, 52, NULL, 'Seřízení mechaniky', 'Nastavení klíčových komponent', 'done', 5, '2026-01-13 23:11:31', NULL),
(29, 0, NULL, 4, 53, NULL, 'Čištění výměníku', 'Chemické čištění tepelného výměníku', '', 2, '2026-01-13 23:11:31', NULL),
(30, 0, NULL, 1, 53, NULL, 'Tlaková zkouška', 'Test těsnosti systému', '', 1, '2026-01-13 23:11:31', NULL),
(31, 0, NULL, 2, 54, NULL, 'Výměna motoru', 'Instalace nového pohonného motoru', '', 4, '2026-01-13 23:11:31', NULL),
(32, 0, NULL, 3, 54, NULL, 'Montáž příslušenství', 'Instalace chlazení a ventilace', '', 5, '2026-01-13 23:11:31', NULL),
(33, 0, NULL, 4, 55, NULL, 'Kalibrace váhy', 'Nastavení vážicího systému', 'done', 2, '2026-01-13 23:11:31', NULL),
(34, 0, NULL, 1, 55, NULL, 'Kontrola dopravníku', 'Revize pásového dopravníku', 'cancelled', 1, '2026-01-13 23:11:31', NULL),
(35, 0, NULL, 2, 56, NULL, 'Oprava řídícího systému', 'Výměna PLC modulu', '', 4, '2026-01-13 23:11:31', NULL),
(36, 0, NULL, 3, 56, NULL, 'Aktualizace firmware', 'Upgrade řídícího software', 'done', 5, '2026-01-13 23:11:31', NULL),
(37, 0, NULL, 4, 57, NULL, 'Výměna filtrů', 'Výměna vzduchových filtrů', '', 2, '2026-01-13 23:11:31', NULL),
(38, 0, NULL, 1, 57, NULL, 'Kontrola těsnosti', 'Tlaková zkouška potrubí', '', 1, '2026-01-13 23:11:31', NULL),
(39, 0, NULL, 2, 58, NULL, 'Oprava frekvenčního měniče', 'Výměna vadného měniče', 'done', 4, '2026-01-13 23:11:31', NULL),
(40, 0, NULL, 3, 58, NULL, 'Seřízení pohonů', 'Nastavení servopohonů', '', 5, '2026-01-13 23:11:31', NULL),
(41, 0, NULL, 4, 59, NULL, 'Čištění chladicího okruhu', 'Odstranění nečistot z okruhu', '', 2, '2026-01-13 23:11:31', NULL),
(42, 0, NULL, 1, 59, NULL, 'Kontrola čerpadla', 'Test výkonu cirkulačního čerpadla', 'cancelled', 1, '2026-01-13 23:11:31', NULL),
(43, 0, NULL, 2, 60, NULL, 'Instalace senzorů', 'Montáž nových snímacích prvků', '', 4, '2026-01-13 23:11:31', NULL),
(44, 0, NULL, 3, 60, NULL, 'Kalibrace měření', 'Nastavení měřících zařízení', 'done', 5, '2026-01-13 23:11:31', NULL),
(45, 0, NULL, 4, 61, NULL, 'Výměna těsnění', 'Kompletní výměna těsnicích prvků', '', 2, '2026-01-13 23:11:31', NULL),
(46, 0, NULL, 1, 61, NULL, 'Tlaková zkouška', 'Zkouška provozního tlaku', '', 1, '2026-01-13 23:11:31', NULL),
(47, 0, NULL, 2, 62, NULL, 'Oprava rozvaděče', 'Oprava poškozeného rozvaděče', '', 4, '2026-01-13 23:11:31', NULL),
(48, 0, NULL, 3, 62, NULL, 'Montáž ochranných krytů', 'Instalace bezpečnostních krytů', 'done', 5, '2026-01-13 23:11:31', NULL),
(49, 0, NULL, 4, 63, NULL, 'Čištění zásobníků', 'Chemické čištění skladovacích nádrží', 'cancelled', 2, '2026-01-13 23:11:31', NULL),
(50, 0, NULL, 1, 63, NULL, 'Kontrola ventilů', 'Revize regulačních ventilů', '', 1, '2026-01-13 23:11:31', NULL),
(51, 0, NULL, 2, 64, NULL, 'Výměna kabeláže', 'Výměna staré kabeláže za novou', '', 4, '2026-01-13 23:11:31', NULL),
(52, 0, NULL, 3, 64, NULL, 'Instalace kabelových žlabů', 'Montáž kabelové infrastruktury', '', 5, '2026-01-13 23:11:31', NULL),
(53, 0, NULL, 4, 65, NULL, 'Kalibrace teploty', 'Nastavení teplotních čidel', 'done', 2, '2026-01-13 23:11:31', NULL),
(54, 0, NULL, 1, 65, NULL, 'Kontrola topných těles', 'Revize topných elementů', '', 1, '2026-01-13 23:11:31', NULL),
(55, 0, NULL, 2, 66, NULL, 'Oprava HMI panelu', 'Výměna dotykového displeje', '', 4, '2026-01-13 23:11:31', NULL),
(56, 0, NULL, 3, 66, NULL, 'Aktualizace rozhraní', 'Upgrade uživatelského rozhraní', '', 5, '2026-01-13 23:11:31', NULL),
(57, 0, NULL, 4, 67, NULL, 'Čištění výfukového systému', 'Odstranění sazí a nečistot', 'done', 2, '2026-01-13 23:11:31', NULL),
(58, 0, NULL, 1, 67, NULL, 'Kontrola turbíny', 'Revize rotačního zařízení', 'cancelled', 1, '2026-01-13 23:11:31', NULL),
(59, 0, NULL, 2, 68, NULL, 'Výměna svítidel', 'Instalace nového osvětlení', '', 4, '2026-01-13 23:11:31', NULL),
(60, 0, NULL, 3, 68, NULL, 'Montáž nouzového osvětlení', 'Instalace bezpečnostního osvětlení', '', 5, '2026-01-13 23:11:31', NULL),
(61, 0, NULL, 4, 69, NULL, 'Výměna maziva', 'Kompletní výměna mazacích hmot', '', 2, '2026-01-13 23:11:31', NULL),
(62, 0, NULL, 1, 69, NULL, 'Kontrola mazacího systému', 'Revize automatického mazání', 'done', 1, '2026-01-13 23:11:31', NULL),
(63, 0, NULL, 2, 70, NULL, 'Oprava komunikačního modulu', 'Výměna Ethernet modulu', '', 4, '2026-01-13 23:11:31', NULL),
(64, 0, NULL, 3, 70, NULL, 'Konfigurace sítě', 'Nastavení průmyslové sítě', '', 5, '2026-01-13 23:11:31', NULL),
(65, 0, NULL, 4, 71, NULL, 'Čištění odpadního systému', 'Odstranění usazenin z odpadu', '', 2, '2026-01-13 23:11:31', NULL),
(66, 0, NULL, 1, 71, NULL, 'Kontrola čistících trysek', 'Revize sprejovacích hlavic', 'done', 1, '2026-01-13 23:11:31', NULL),
(67, 0, NULL, 2, 72, NULL, 'Výměna pojistek', 'Kompletní výměna pojistkových bloků', 'cancelled', 4, '2026-01-13 23:11:31', NULL),
(68, 0, NULL, 3, 72, NULL, 'Instalace přepěťové ochrany', 'Montáž ochranných prvků', '', 5, '2026-01-13 23:11:31', NULL),
(69, 0, NULL, 4, 73, NULL, 'Kalibrace vlhkosti', 'Nastavení vlhkostních čidel', '', 2, '2026-01-13 23:11:31', NULL),
(70, 0, NULL, 1, 73, NULL, 'Kontrola sušičky', 'Revize vysoušecího zařízení', '', 1, '2026-01-13 23:11:31', NULL),
(71, 0, NULL, 2, 74, NULL, 'Oprava zdroje', 'Výměna napájecího zdroje', 'done', 4, '2026-01-13 23:11:31', NULL),
(72, 0, NULL, 3, 74, NULL, 'Montáž záložního zdroje', 'Instalace UPS systému', '', 5, '2026-01-13 23:11:31', NULL),
(73, 0, NULL, 4, 75, NULL, 'Výměna hadic', 'Výměna vysokotlakých hadic', '', 2, '2026-01-13 23:11:31', NULL),
(74, 0, NULL, 1, 75, NULL, 'Kontrola spojů', 'Reviza spojovacích elementů', '', 1, '2026-01-13 23:11:31', NULL),
(75, 0, NULL, 2, 76, NULL, 'Oprava snímače', 'Výměna vadného snímače', 'done', 4, '2026-01-13 23:11:31', NULL),
(76, 0, NULL, 3, 76, NULL, 'Kalibrace pozice', 'Nastavení polohovacích senzorů', 'cancelled', 5, '2026-01-13 23:11:31', NULL),
(77, 0, NULL, 4, 77, NULL, 'Čištění kompresoru', 'Servis vzduchového kompresoru', '', 2, '2026-01-13 23:11:31', NULL),
(78, 0, NULL, 1, 77, NULL, 'Kontrola vzdušníku', 'Revize tlakové nádoby', '', 1, '2026-01-13 23:11:31', NULL),
(79, 0, NULL, 2, 78, NULL, 'Výměna ventilátoru', 'Instalace nového chladicího ventilátoru', '', 4, '2026-01-13 23:11:31', NULL),
(80, 0, NULL, 3, 78, NULL, 'Montáž žaluzií', 'Instalace regulačních žaluzií', 'done', 5, '2026-01-13 23:11:31', NULL),
(81, 0, NULL, 4, 79, NULL, 'Výměna pásů', 'Výměna řemenových převodů', 'cancelled', 2, '2026-01-13 23:11:31', NULL),
(82, 0, NULL, 1, 79, NULL, 'Kontrola napínáků', 'Revize napínacích mechanismů', '', 1, '2026-01-13 23:11:31', NULL),
(83, 0, NULL, 2, 80, NULL, 'Oprava měniče', 'Výměna frekvenčního měniče', '', 4, '2026-01-13 23:11:31', NULL),
(84, 0, NULL, 3, 80, NULL, 'Programování pohonu', 'Nastavení řídicího algoritmu', '', 5, '2026-01-13 23:11:31', NULL),
(85, 0, NULL, 4, 81, NULL, 'Čištění odlučovače', 'Servis cyklónového odlučovače', 'done', 2, '2026-01-13 23:11:31', NULL),
(86, 0, NULL, 1, 81, NULL, 'Kontrola filtrů', 'Revize filtrace odpadního vzduchu', '', 1, '2026-01-13 23:11:31', NULL),
(87, 0, NULL, 2, 82, NULL, 'Výměna termostatu', 'Instalace nového regulátoru teploty', 'cancelled', 4, '2026-01-13 23:11:31', NULL),
(88, 0, NULL, 3, 82, NULL, 'Kalibrace teploty', 'Nastavení teplotních rozsahů', '', 5, '2026-01-13 23:11:31', NULL),
(89, 0, NULL, 4, 83, NULL, 'Výměna pneumatických válců', 'Výměna poškozených válců', '', 2, '2026-01-13 23:11:31', NULL),
(90, 0, NULL, 1, 83, NULL, 'Kontrola rozvodu vzduchu', 'Revize pneumatického systému', 'done', 1, '2026-01-13 23:11:31', NULL),
(91, 0, NULL, 2, 84, NULL, 'Oprava enkodéru', 'Výměva rotačního enkodéru', '', 4, '2026-01-13 23:11:31', NULL),
(92, 0, NULL, 3, 84, NULL, 'Kalibrace nuly', 'Nastavení referenční pozice', '', 5, '2026-01-13 23:11:31', NULL),
(93, 0, NULL, 4, 85, NULL, 'Čištění kondenzátoru', 'Odstranění nečistot z chladiče', '', 2, '2026-01-13 23:11:31', NULL),
(94, 0, NULL, 1, 85, NULL, 'Kontrola chladicí kapaliny', 'Analýza a doplnění chladiva', 'done', 1, '2026-01-13 23:11:31', NULL),
(95, 0, NULL, 2, 86, NULL, 'Výměna tlačítek', 'Výměna ovládacích prvků', 'cancelled', 4, '2026-01-13 23:11:31', NULL),
(96, 0, NULL, 3, 86, NULL, 'Montáž ochranných krytů', 'Instalace bezpečnostních krytů', '', 5, '2026-01-13 23:11:31', NULL),
(97, 0, NULL, 4, 87, NULL, 'Výměna ložisek', 'Výměna valivých ložisek', '', 2, '2026-01-13 23:11:31', NULL),
(98, 0, NULL, 1, 87, NULL, 'Kontrola hřídelí', 'Revize rotačních hřídelí', '', 1, '2026-01-13 23:11:31', NULL),
(99, 0, NULL, 2, 88, NULL, 'Oprava záložního systému', 'Servis záložního napájení', 'done', 4, '2026-01-13 23:11:31', NULL),
(100, 0, NULL, 3, 88, NULL, 'Test nouzového zastavení', 'Zkouška bezpečnostního stopu', '', 5, '2026-01-13 23:11:31', NULL),
(101, 0, NULL, 4, 89, NULL, 'Čištění výparníku', 'Chemické čištění výparníkové jednotky', 'cancelled', 2, '2026-01-13 23:11:31', NULL),
(102, 0, NULL, 1, 89, NULL, 'Kontrola kompresoru', 'Revize chladicího kompresoru', '', 1, '2026-01-13 23:11:31', NULL),
(103, 0, NULL, 2, 90, NULL, 'Výměna kontaktů', 'Výměna opotřebovaných kontaktů', '', 4, '2026-01-13 23:11:31', NULL),
(104, 0, NULL, 3, 90, NULL, 'Seřízení spínačů', 'Nastavení limitních spínačů', 'done', 5, '2026-01-13 23:11:31', NULL),
(105, 0, NULL, 4, 91, NULL, 'Výměna těsnění dveří', 'Výměna těsnicích profilů', '', 2, '2026-01-13 23:11:31', NULL),
(106, 0, NULL, 1, 91, NULL, 'Kontrola zámků', 'Revize bezpečnostních zámků', '', 1, '2026-01-13 23:11:31', NULL),
(107, 0, NULL, 2, 91, NULL, 'Test bezpečnosti', 'Komplexní bezpečnostní test', '', 4, '2026-01-13 23:11:31', NULL),
(108, 0, NULL, 3, 91, NULL, 'Finální kontrola', 'Závěrečná kontrola před předáním', 'done', 5, '2026-01-13 23:11:31', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `minutes_spent` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `company_id`, `parent_id`, `task_id`, `user_id`, `minutes_spent`, `note`, `created_by_user_id`, `created_at`) VALUES
(1, 0, NULL, 1, 2, 45, 'Základní kontrola bez závad', 1, '2026-01-10 08:15:00'),
(2, 0, NULL, 1, 6, 30, 'Doplnění měření', 1, '2026-01-10 09:30:00'),
(3, 0, NULL, 2, 6, 90, 'Výměna těsnění', 1, '2026-01-10 10:45:00'),
(4, 0, NULL, 3, 4, 60, 'Proměření okruhů', 4, '2026-01-11 13:20:00'),
(5, 0, NULL, 4, 4, 40, 'Montáž nového jističe', 4, '2026-01-11 14:45:00'),
(6, 0, NULL, 6, 2, 120, 'Demontáž a montáž ložisek', 4, '2026-01-11 15:30:00'),
(7, 0, NULL, 1, 3, 60, 'Kontrola těsnosti přírub', 1, '2026-01-10 11:00:00'),
(8, 0, NULL, 2, 1, 45, 'Dotažení spojů', 1, '2026-01-10 13:00:00'),
(9, 0, NULL, 3, 5, 30, 'Záznam měření', 4, '2026-01-11 14:00:00'),
(10, 0, NULL, 4, 6, 20, 'Označení v dokumentaci', 4, '2026-01-11 15:00:00'),
(11, 0, NULL, 5, 4, 180, 'Kompletní odstavení zařízení', 1, '2026-01-12 08:00:00'),
(12, 0, NULL, 5, 7, 60, 'Příprava nástrojů', 1, '2026-01-12 09:30:00'),
(13, 0, NULL, 6, 9, 90, 'Vyčištění prostoru', 4, '2026-01-12 10:45:00'),
(14, 0, NULL, 7, 2, 120, 'Odstranění usazenin z potrubí', 2, '2026-01-12 13:00:00'),
(15, 0, NULL, 7, 8, 60, 'Čištění filtrů', 2, '2026-01-12 14:30:00'),
(16, 0, NULL, 8, 5, 90, 'Diagnostika elektrického systému', 5, '2026-01-12 15:00:00'),
(17, 0, NULL, 9, 5, 60, 'Zpracování technické zprávy', 5, '2026-01-12 16:00:00'),
(18, 0, NULL, 10, 1, 45, 'Kontrola vizuálního stavu', 1, '2026-01-13 08:00:00'),
(19, 0, NULL, 11, 1, 90, 'Montáž nových těsnění', 1, '2026-01-13 09:00:00'),
(20, 0, NULL, 12, 4, 75, 'Kontrola izolace kabeláže', 4, '2026-01-13 10:30:00'),
(21, 0, NULL, 12, 11, 45, 'Test připojení', 4, '2026-01-13 11:45:00'),
(22, 0, NULL, 13, 4, 60, 'Proměření odporů', 4, '2026-01-13 13:00:00'),
(23, 0, NULL, 14, 4, 40, 'Výměna jističe 16A', 4, '2026-01-13 13:45:00'),
(24, 0, NULL, 15, 5, 90, 'Instalace modulů', 5, '2026-01-13 14:30:00'),
(25, 0, NULL, 16, 1, 120, 'Bezpečnostní odstavení', 1, '2026-01-13 15:30:00'),
(26, 0, NULL, 16, 7, 60, 'Výstražné označení', 1, '2026-01-13 16:30:00'),
(27, 0, NULL, 17, 4, 180, 'Demontáž starých ložisek', 4, '2026-01-14 08:00:00'),
(28, 0, NULL, 17, 13, 120, 'Montáž nových ložisek', 4, '2026-01-14 11:00:00'),
(29, 0, NULL, 18, 2, 90, 'Kalibrace tlakových senzorů', 2, '2026-01-14 13:00:00'),
(30, 0, NULL, 19, 2, 120, 'Chemické čištění', 2, '2026-01-14 14:30:00'),
(31, 0, NULL, 20, 1, 60, 'Kontrola filtračních vložek', 1, '2026-01-14 16:00:00'),
(32, 0, NULL, 21, 5, 75, 'Analýza provozních dat', 5, '2026-01-14 17:00:00'),
(33, 0, NULL, 22, 5, 45, 'Příprava návrhu opravy', 5, '2026-01-14 17:45:00'),
(34, 0, NULL, 23, 4, 150, 'Komplexní kontrola stroje', 4, '2026-01-15 08:00:00'),
(35, 0, NULL, 24, 2, 60, 'Výměna motorového oleje', 2, '2026-01-15 10:30:00'),
(36, 0, NULL, 25, 1, 45, 'Test bezpečnostních pojistek', 1, '2026-01-15 11:15:00'),
(37, 0, NULL, 26, 1, 120, 'Oprava hydraulického válce', 1, '2026-01-15 12:00:00'),
(38, 0, NULL, 27, 4, 90, 'Hledání zkratu v rozvaděči', 4, '2026-01-15 14:00:00'),
(39, 0, NULL, 28, 5, 60, 'Seřízení převodovky', 5, '2026-01-15 15:30:00'),
(40, 0, NULL, 29, 2, 180, 'Čištění výměníku kyselinou', 2, '2026-01-16 08:00:00'),
(41, 0, NULL, 30, 1, 90, 'Tlaková zkouška 10 bar', 1, '2026-01-16 11:00:00'),
(42, 0, NULL, 31, 4, 240, 'Demontáž starého motoru', 4, '2026-01-16 13:00:00'),
(43, 0, NULL, 31, 10, 120, 'Montáž nového motoru', 4, '2026-01-16 17:00:00'),
(44, 0, NULL, 32, 5, 90, 'Instalace chladicího systému', 5, '2026-01-17 08:30:00'),
(45, 0, NULL, 33, 2, 45, 'Kalibrace digitální váhy', 2, '2026-01-17 10:00:00'),
(46, 0, NULL, 34, 1, 60, 'Kontrola pásu dopravníku', 1, '2026-01-17 10:45:00'),
(47, 0, NULL, 35, 4, 120, 'Výměna PLC jednotky', 4, '2026-01-17 12:00:00'),
(48, 0, NULL, 36, 5, 30, 'Aktualizace firmwaru', 5, '2026-01-17 14:00:00'),
(49, 0, NULL, 37, 2, 60, 'Výměna vzduchových filtrů', 2, '2026-01-17 14:30:00'),
(50, 0, NULL, 38, 1, 90, 'Tlaková zkouška potrubí', 1, '2026-01-17 15:30:00'),
(51, 0, NULL, 39, 4, 75, 'Diagnostika měniče', 4, '2026-01-18 08:00:00'),
(52, 0, NULL, 39, 11, 45, 'Výměna IGBT modulů', 4, '2026-01-18 09:15:00'),
(53, 0, NULL, 40, 5, 60, 'Seřízení polohy pohonu', 5, '2026-01-18 10:00:00'),
(54, 0, NULL, 41, 2, 120, 'Vyčištění chladicího okruhu', 2, '2026-01-18 11:00:00'),
(55, 0, NULL, 42, 1, 90, 'Test výkonu čerpadla', 1, '2026-01-18 13:00:00'),
(56, 0, NULL, 43, 4, 60, 'Montáž teplotních senzorů', 4, '2026-01-18 14:30:00'),
(57, 0, NULL, 44, 5, 45, 'Kalibrace měřících přístrojů', 5, '2026-01-18 15:30:00'),
(58, 0, NULL, 45, 2, 180, 'Výměna všech těsnění', 2, '2026-01-19 08:00:00'),
(59, 0, NULL, 46, 1, 60, 'Zkouška provozního tlaku', 1, '2026-01-19 11:00:00'),
(60, 0, NULL, 47, 4, 120, 'Oprava zkratu v rozvaděči', 4, '2026-01-19 12:00:00'),
(61, 0, NULL, 48, 5, 90, 'Montáž bezpečnostních krytů', 5, '2026-01-19 14:00:00'),
(62, 0, NULL, 49, 2, 150, 'Čištění zásobních nádrží', 2, '2026-01-19 15:30:00'),
(63, 0, NULL, 50, 1, 45, 'Kontrola regulačních ventilů', 1, '2026-01-20 08:00:00'),
(64, 0, NULL, 51, 4, 180, 'Výměna staré kabeláže', 4, '2026-01-20 08:45:00'),
(65, 0, NULL, 52, 5, 120, 'Montáž kabelových žlabů', 5, '2026-01-20 11:45:00'),
(66, 0, NULL, 53, 2, 60, 'Kalibrace teplotních čidel', 2, '2026-01-20 13:45:00'),
(67, 0, NULL, 54, 1, 90, 'Kontrola topných spirál', 1, '2026-01-20 14:45:00'),
(68, 0, NULL, 55, 4, 120, 'Výměna dotykového panelu', 4, '2026-01-21 08:00:00'),
(69, 0, NULL, 56, 5, 75, 'Aktualizace HMI softwaru', 5, '2026-01-21 10:00:00'),
(70, 0, NULL, 57, 2, 150, 'Čištění výfukového potrubí', 2, '2026-01-21 11:15:00'),
(71, 0, NULL, 58, 1, 60, 'Kontrola rotoru turbíny', 1, '2026-01-21 13:45:00'),
(72, 0, NULL, 59, 4, 90, 'Instalace nových svítidel', 4, '2026-01-22 08:00:00'),
(73, 0, NULL, 60, 5, 60, 'Montáž nouzového osvětlení', 5, '2026-01-22 09:30:00'),
(74, 0, NULL, 61, 2, 120, 'Výměna mazacího oleje', 2, '2026-01-22 10:30:00'),
(75, 0, NULL, 62, 1, 45, 'Kontrola mazacího okruhu', 1, '2026-01-22 12:30:00'),
(76, 0, NULL, 63, 4, 90, 'Výměna ethernetového switche', 4, '2026-01-22 13:15:00'),
(77, 0, NULL, 64, 5, 60, 'Konfigurace IP adres', 5, '2026-01-22 14:45:00'),
(78, 0, NULL, 65, 2, 180, 'Čištění odpadního systému', 2, '2026-01-23 08:00:00'),
(79, 0, NULL, 66, 1, 60, 'Kontrola trysek', 1, '2026-01-23 11:00:00'),
(80, 0, NULL, 67, 4, 45, 'Výměna pojistek 10A', 4, '2026-01-23 12:00:00'),
(81, 0, NULL, 68, 5, 90, 'Montáž přepěťové ochrany', 5, '2026-01-23 12:45:00'),
(82, 0, NULL, 69, 2, 75, 'Kalibrace vlhkostního čidla', 2, '2026-01-23 14:15:00'),
(83, 0, NULL, 70, 1, 90, 'Kontrola kompresoru sušičky', 1, '2026-01-24 08:00:00'),
(84, 0, NULL, 71, 4, 120, 'Výměna napájecího zdroje', 4, '2026-01-24 09:30:00'),
(85, 0, NULL, 72, 5, 180, 'Instalace UPS systému', 5, '2026-01-24 11:30:00'),
(86, 0, NULL, 73, 2, 60, 'Výměna vysokotlakých hadic', 2, '2026-01-25 08:00:00'),
(87, 0, NULL, 74, 1, 45, 'Kontrola šroubových spojů', 1, '2026-01-25 09:00:00'),
(88, 0, NULL, 75, 4, 75, 'Výměna snímače teploty', 4, '2026-01-25 09:45:00'),
(89, 0, NULL, 76, 5, 90, 'Kalibrace polohových senzorů', 5, '2026-01-25 11:00:00'),
(90, 0, NULL, 77, 2, 150, 'Čištění kompresorových částí', 2, '2026-01-25 12:30:00'),
(91, 0, NULL, 78, 1, 60, 'Kontrola tlakové nádoby', 1, '2026-01-26 08:00:00'),
(92, 0, NULL, 79, 4, 90, 'Instalace nového ventilátoru', 4, '2026-01-26 09:00:00'),
(93, 0, NULL, 80, 5, 45, 'Montáž regulačních žaluzií', 5, '2026-01-26 10:30:00'),
(94, 0, NULL, 81, 2, 60, 'Výměna řemenového převodu', 2, '2026-01-26 11:15:00'),
(95, 0, NULL, 82, 1, 45, 'Kontrola napínacích mechanismů', 1, '2026-01-26 12:15:00'),
(96, 0, NULL, 83, 4, 120, 'Výměna frekvenčního měniče', 4, '2026-01-27 08:00:00'),
(97, 0, NULL, 84, 5, 90, 'Programování řídicího algoritmu', 5, '2026-01-27 10:00:00'),
(98, 0, NULL, 85, 2, 120, 'Čištění cyklónového odlučovače', 2, '2026-01-27 11:30:00'),
(99, 0, NULL, 86, 1, 60, 'Kontrola filtrace vzduchu', 1, '2026-01-27 13:30:00'),
(100, 0, NULL, 87, 4, 75, 'Výměna termostatu', 4, '2026-01-28 08:00:00'),
(101, 0, NULL, 88, 5, 45, 'Kalibrace teplotních rozsahů', 5, '2026-01-28 09:15:00'),
(102, 0, NULL, 89, 2, 180, 'Výměna pneumatických válců', 2, '2026-01-28 10:00:00'),
(103, 0, NULL, 90, 1, 60, 'Kontrola pneumatického rozvodu', 1, '2026-01-28 13:00:00'),
(104, 0, NULL, 91, 4, 120, 'Výměna rotačního enkodéru', 4, '2026-01-28 14:00:00'),
(105, 0, NULL, 92, 5, 90, 'Nastavení referenční pozice', 5, '2026-01-29 08:00:00'),
(106, 0, NULL, 93, 2, 150, 'Čištění chladiče kondenzátoru', 2, '2026-01-29 09:30:00'),
(107, 0, NULL, 94, 1, 45, 'Kontrola hladiny chladiva', 1, '2026-01-29 12:00:00'),
(108, 0, NULL, 95, 4, 60, 'Výměna tlačítek ovládacího panelu', 4, '2026-01-29 12:45:00'),
(109, 0, NULL, 96, 5, 120, 'Montáž bezpečnostních krytů', 5, '2026-01-29 13:45:00'),
(110, 0, NULL, 97, 2, 180, 'Výměna ložisek hřídele', 2, '2026-01-30 08:00:00'),
(111, 0, NULL, 98, 1, 90, 'Kontrola rotačních hřídelí', 1, '2026-01-30 11:00:00'),
(112, 0, NULL, 99, 4, 120, 'Servis záložního napájení', 4, '2026-01-30 12:30:00'),
(113, 0, NULL, 100, 5, 60, 'Test nouzového stopu', 5, '2026-01-30 14:30:00'),
(114, 0, NULL, 101, 2, 150, 'Čištění výparníkové jednotky', 2, '2026-01-31 08:00:00'),
(115, 0, NULL, 102, 1, 90, 'Kontrola chladicího kompresoru', 1, '2026-01-31 10:30:00'),
(116, 0, NULL, 103, 4, 120, 'Výměna kontaktů relé', 4, '2026-01-31 12:00:00'),
(117, 0, NULL, 104, 5, 45, 'Seřízení limitních spínačů', 5, '2026-01-31 14:00:00'),
(118, 0, NULL, 105, 2, 90, 'Výměna těsnicích profilů', 2, '2026-01-31 14:45:00'),
(119, 0, NULL, 106, 1, 60, 'Kontrola bezpečnostních zámků', 1, '2026-01-31 16:00:00'),
(120, 0, NULL, 107, 4, 120, 'Test bezpečnostních funkcí', 4, '2026-02-01 08:00:00'),
(121, 0, NULL, 108, 5, 90, 'Závěrečná kontrola kvality', 5, '2026-02-01 10:00:00'),
(122, 0, NULL, 1, 7, 30, 'Dokončení dokumentace', 1, '2026-01-10 10:00:00'),
(123, 0, NULL, 3, 10, 45, 'Označení měřených bodů', 4, '2026-01-11 14:00:00'),
(124, 0, NULL, 5, 8, 60, 'Příprava pracovních pomůcek', 1, '2026-01-12 07:30:00'),
(125, 0, NULL, 12, 12, 30, 'Test izolace', 4, '2026-01-13 12:15:00'),
(126, 0, NULL, 23, 9, 60, 'Čištění stroje', 4, '2026-01-15 10:30:00'),
(127, 0, NULL, 31, 12, 90, 'Zapojení motoru', 4, '2026-01-16 15:00:00'),
(128, 0, NULL, 45, 15, 60, 'Tlaková zkouška po výměně', 2, '2026-01-19 12:00:00'),
(129, 0, NULL, 71, 10, 45, 'Test UPS zátěže', 4, '2026-01-24 13:30:00'),
(130, 0, NULL, 85, 14, 60, 'Kontrola funkce odlučovače', 2, '2026-01-27 13:30:00'),
(131, 0, NULL, 99, 11, 45, 'Zátěžový test záložního zdroje', 4, '2026-01-30 14:00:00');

-- --------------------------------------------------------

--
-- Struktura tabulky `work_orders`
--

CREATE TABLE `work_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `external_number` varchar(100) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `source` enum('email','phone','personal','system') NOT NULL,
  `requested_by` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `priority` enum('low','normal','high','emergency') NOT NULL DEFAULT 'normal',
  `estimated_hours` decimal(8,2) DEFAULT NULL,
  `status` enum('new','in_progress','done','exported','cancelled') NOT NULL DEFAULT 'new',
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `work_orders`
--

INSERT INTO `work_orders` (`id`, `company_id`, `parent_id`, `external_number`, `title`, `description`, `source`, `requested_by`, `contact`, `priority`, `estimated_hours`, `status`, `created_by_user_id`, `created_at`, `closed_at`) VALUES
(1, 0, NULL, 'něco', 'někdde', 'njěkdo', 'phone', 'chtěl', NULL, 'high', NULL, 'new', 1, '2026-01-13 12:05:41', NULL),
(2, 0, NULL, 'WO-2026-001', 'Netěsnící ventil', 'Ventil prosakuje olej', 'phone', 'Výroba', NULL, 'high', NULL, 'new', 2, '2026-01-13 22:13:00', NULL),
(3, 0, NULL, 'WO-2026-002', 'Porucha čerpadla', 'Nejde spustit čerpadlo', 'email', 'Vodárna', 'kokodák', 'normal', NULL, 'in_progress', 4, '2026-01-12 22:13:00', NULL),
(4, 0, NULL, 'WO-2026-003', 'Výměna filtru', 'Preventivní údržba', 'email', 'Plán', 'někdo', 'normal', NULL, 'done', 5, '2026-01-10 22:13:00', '2026-01-11 22:13:00'),
(5, 0, NULL, 'WO-2026-004', 'Vadné čidlo', 'Nesmyslné hodnoty', 'personal', 'SCADA', NULL, 'normal', NULL, 'new', 3, '2026-01-13 22:13:00', NULL),
(6, 0, NULL, 'WO-2026-005', 'Prasklá hadice', 'Únik kapaliny', 'phone', 'Výroba', NULL, 'high', NULL, 'cancelled', 2, '2026-01-08 22:13:00', NULL),
(52, 0, NULL, 'Wc-2026-001', 'Servis kotle', 'Nestartuje hořák', 'email', 'Novák', 'žádanka', 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(53, 0, NULL, 'Wc-2026-002', 'Revize výtahu', 'Pravidelná roční revize', 'email', 'SVJ Dlouhá', NULL, 'normal', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(54, 0, NULL, 'Wc-2026-003', 'Oprava zásuvky', 'Jiskření při zapnutí', 'email', 'Dvořák', '', 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(55, 0, NULL, 'Wc-2026-004', 'Kontrola klimatizace', 'Slabý výkon chlazení', 'email', 'ACME s.r.o.', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(56, 0, NULL, 'Wc-2026-005', 'Servis brány', 'Brána se nezavírá', 'phone', 'Horák', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(57, 0, NULL, 'Wc-2026-006', 'Výměna jističe', 'Vypadává hlavní jistič', 'personal', 'Král', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(58, 0, NULL, 'Wc-2026-007', 'Oprava osvětlení', 'Nefunkční světla v hale', 'email', 'LogiTrans', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(59, 0, NULL, 'Wc-2026-008', 'Revize elektro', 'Periodická revize objektu', 'email', 'Město', NULL, 'low', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(60, 0, NULL, 'Wc-2026-009', 'Servis UPS', 'Výpadky napájení', 'phone', 'IT odd.', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(61, 0, NULL, 'Wc-2026-010', 'Oprava zvonku', 'Zvonění nefunguje', 'personal', 'Beneš', NULL, 'low', NULL, '', 1, '2026-01-13 22:41:48', '2026-01-13 22:41:48'),
(62, 0, NULL, 'WO-2026-011', 'Montáž zásuvek', 'Nové kanceláře', 'email', 'OfficePro', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(63, 0, NULL, 'WO-2026-012', 'Servis čerpadla', 'Hlučný chod', 'phone', 'Zeman', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(64, 0, NULL, 'WO-2026-013', 'Oprava topení', 'Netopí radiátor', 'personal', 'Malá', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(65, 0, NULL, 'WO-2026-014', 'Revize hromosvodu', 'Povinná revize', 'email', 'Škola', NULL, 'low', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(66, 0, NULL, 'WO-2026-015', 'Servis kompresoru', 'Pokles tlaku', 'phone', 'KovoTech', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(67, 0, NULL, 'WO-2026-016', 'Výměna svítidel', 'LED upgrade', 'email', 'Retail Park', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(68, 0, NULL, 'WO-2026-017', 'Oprava rozvaděče', 'Přehřívání', 'phone', 'Energo', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(69, 0, NULL, 'WO-2026-018', 'Kontrola jističů', 'Preventivní kontrola', 'personal', 'Kučera', NULL, 'low', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(70, 0, NULL, 'WO-2026-019', 'Servis digestoře', 'Slabý tah', 'email', 'Restaurace U Mostu', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(71, 0, NULL, 'WO-2026-020', 'Oprava zásuvky', 'Uvolněný kontakt', 'phone', 'Urban', NULL, 'normal', NULL, '', 1, '2026-01-13 22:41:48', '2026-01-13 22:41:48'),
(72, 0, NULL, 'WO-2026-021', 'Revize kotelny', 'Roční revize', 'email', 'BD Javor', NULL, 'low', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(73, 0, NULL, 'WO-2026-022', 'Servis výtahu', 'Zasekávání dveří', 'phone', 'SVJ Lipová', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(74, 0, NULL, 'WO-2026-023', 'Oprava ventilátoru', 'Vibrace', 'personal', 'Pavelka', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(75, 0, NULL, 'WO-2026-024', 'Montáž rozvodů', 'Nová dílna', 'email', 'TechBuild', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(76, 0, NULL, 'WO-2026-025', 'Servis serverovny', 'Přehřívání racku', 'email', 'DataCorp', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(77, 0, NULL, 'WO-2026-026', 'Oprava světel', 'Blikání', 'personal', 'Sedláček', NULL, 'low', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(78, 0, NULL, 'WO-2026-027', 'Kontrola uzemnění', 'Bezpečnostní kontrola', 'email', 'Výrobní závod', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(79, 0, NULL, 'WO-2026-028', 'Servis čidla', 'Chybné hodnoty', 'phone', 'SmartHome', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(80, 0, NULL, 'WO-2026-029', 'Výměna pojistek', 'Opakované prasknutí', 'personal', 'Vlček', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(81, 0, NULL, 'WO-2026-030', 'Oprava rozvodnice', 'Poškozený kryt', 'email', 'Stavmont', NULL, 'normal', NULL, '', 1, '2026-01-13 22:41:48', '2026-01-13 22:41:48'),
(82, 0, NULL, 'WO-2026-031', 'Revize elektroinstalace', 'Kolaudace', 'email', 'Developer', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(83, 0, NULL, 'WO-2026-032', 'Servis čerpadla', 'Nízký výkon', 'phone', 'Farmář', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(84, 0, NULL, 'WO-2026-033', 'Oprava spínače', 'Zasekávání', 'personal', 'Hruška', NULL, 'low', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(85, 0, NULL, 'WO-2026-034', 'Kontrola světel', 'Noční provoz', 'email', 'Warehouse', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(86, 0, NULL, 'WO-2026-035', 'Servis rozvodů', 'Preventivní servis', 'email', 'Průmyslovka', NULL, 'low', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(87, 0, NULL, 'WO-2026-036', 'Oprava motoru', 'Přehřívání', 'phone', 'AutoServis', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(88, 0, NULL, 'WO-2026-037', 'Montáž senzorů', 'Nový systém', 'email', 'SmartFactory', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(89, 0, NULL, 'WO-2026-038', 'Revize UPS', 'Zátěžový test', 'email', 'IT firma', NULL, 'normal', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(90, 0, NULL, 'WO-2026-039', 'Servis osvětlení', 'Porucha okruhu', 'phone', 'Obchodní centrum', NULL, 'high', NULL, 'in_progress', 1, '2026-01-13 22:41:48', NULL),
(91, 0, NULL, 'WO-2026-040', 'Oprava kabeláže', 'Poškozený kabel', 'personal', 'Krejčí', NULL, 'high', NULL, 'new', 1, '2026-01-13 22:41:48', NULL),
(92, 0, NULL, NULL, 'oprava kotelny Jeremenko', 'hlodavci naělali paseku v kabeláži, pohryzané dráty, možná vadná čidla nebo i řídící jenotka', 'email', '', 'hhhhhhhh', 'normal', NULL, 'new', 1, '2026-01-23 22:52:15', NULL),
(95, 0, NULL, NULL, 'Zakázky > Nová', 'b', 'email', '', '', 'normal', NULL, 'new', 1, '2026-01-23 22:58:04', NULL),
(96, 0, NULL, NULL, 'job', '', 'email', 'job', NULL, 'normal', NULL, 'new', 1, '2026-01-23 23:08:32', NULL),
(97, 0, NULL, NULL, 'job', '', 'email', 'job', '', 'normal', NULL, 'new', 1, '2026-01-23 23:21:11', NULL),
(101, 0, NULL, 'po00744', 'bugreport servisního zápisníku :D', '', 'email', '', '', 'normal', NULL, 'new', 1, '2026-01-24 01:14:06', NULL),
(102, 0, NULL, NULL, 'rozbité přidávaní zakázek', 'prostě to nejde uložit', 'email', '', '', 'low', NULL, 'new', 1, '2026-01-28 20:55:44', NULL),
(103, 0, NULL, 'Nová', 'NováNová', 'NováNováNováNováNováNováNováNováNováNová', 'phone', '', '', 'high', NULL, 'new', 1, '2026-01-30 23:48:16', NULL),
(104, 0, NULL, 'Ddrt1234567890', 'Oprava mlýnice strusky', 'Nefunguje mlýnice strusky, napájení, ok dále mrtve', 'email', 'Vanek', '', 'emergency', NULL, 'new', 5, '2026-02-02 20:03:05', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `recurring_tasks`
--
ALTER TABLE `recurring_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recurring_tasks_parent` (`parent_id`),
  ADD KEY `idx_recurring_tasks_company` (`company_id`);

--
-- Indexy pro tabulku `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_team_status` (`team_id`,`status`),
  ADD KEY `idx_tasks_recurring` (`recurring_task_id`),
  ADD KEY `idx_tasks_parent` (`parent_id`),
  ADD KEY `idx_tasks_company` (`company_id`);

--
-- Indexy pro tabulku `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_assignments_task` (`task_id`),
  ADD KEY `idx_task_assignments_parent` (`parent_id`),
  ADD KEY `idx_task_assignments_company` (`company_id`);

--
-- Indexy pro tabulku `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_company_external_number` (`company_id`,`external_number`),
  ADD KEY `idx_work_orders_status` (`status`),
  ADD KEY `idx_work_orders_company` (`company_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `recurring_tasks`
--
ALTER TABLE `recurring_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT pro tabulku `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT pro tabulku `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;
COMMIT;
