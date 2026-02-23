-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost
-- Vytvořeno: Pon 23. úno 2026, 22:18
-- Verze serveru: 10.4.28-MariaDB
-- Verze PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Databáze: `work`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `billing_exports`
--

CREATE TABLE `billing_exports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `exported_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `exported_at` datetime NOT NULL DEFAULT current_timestamp(),
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `done_at` datetime DEFAULT NULL,
  `billing_export_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `tasks`
--

INSERT INTO `tasks` (`id`, `company_id`, `parent_id`, `team_id`, `work_order_id`, `recurring_task_id`, `title`, `description`, `status`, `created_by_user_id`, `created_at`, `done_at`, `billing_export_id`) VALUES
(1, 1, NULL, 1, 1, NULL, 'Kontrola zapojení', 'Fyzická kontrola rozvaděče', 'done', 1, '2026-01-29 12:43:44', '2026-01-31 12:43:44', NULL),
(2, 1, NULL, 1, 1, NULL, 'Vyhotovení protokolu', 'Sepsání revizní zprávy', 'done', 1, '2026-01-30 12:43:44', '2026-02-03 12:43:44', NULL),
(3, 1, NULL, 2, 2, NULL, 'Diagnostika čidla', 'Proměření signálu', 'open', 2, '2026-02-13 12:43:44', NULL, NULL),
(4, 1, NULL, 2, 2, NULL, 'Objednání náhradního dílu', 'Čidlo PT100', 'open', 2, '2026-02-14 12:43:44', NULL, NULL),
(5, 1, NULL, 1, 5, NULL, 'Výjezd na místo', 'Okamžitý zásah', 'open', 2, '2026-02-17 12:43:44', NULL, NULL),
(6, 2, NULL, 3, 3, NULL, 'Montážní práce', 'Instalace čerpadla', 'done', 3, '2026-01-19 12:44:04', '2026-01-23 12:44:04', NULL),
(7, 2, NULL, 3, 4, NULL, 'Tlaková zkouška', 'Ověření těsnosti', 'done', 3, '2026-01-20 12:44:04', '2026-01-24 12:44:04', NULL),
(8, 2, NULL, 4, 5, NULL, 'Diagnostika PLC', 'Kontrola logů', 'open', 4, '2026-02-15 12:44:04', NULL, NULL),
(823, 2, NULL, 3, 113, NULL, 'ladit', 'prostě vyladit do dokonalosti', 'open', 3, '2026-02-18 19:16:25', NULL, NULL),
(824, 2, NULL, 4, 112, NULL, 'hhhhhhhhhhhhhhhhhhhhhh', '', 'open', 3, '2026-02-18 19:18:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `work_order_id` bigint(20) UNSIGNED NOT NULL,
  `kilometers` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `minutes_spent` int(10) UNSIGNED NOT NULL,
  `note` text DEFAULT NULL,
  `created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `company_id`, `parent_id`, `task_id`, `work_order_id`, `kilometers`, `user_id`, `minutes_spent`, `note`, `created_by_user_id`, `created_at`) VALUES
(1, 1, NULL, 1, 1, 12, 2, 180, 'Revize provedena', 1, '2026-01-31 12:45:58'),
(2, 1, NULL, 3, 2, 5, 2, 90, 'Probíhá měření', 2, '2026-02-14 12:45:58'),
(3, 2, NULL, 6, 4, 20, 4, 240, 'Montáž dokončena', 3, '2026-01-23 12:45:58'),
(145, 2, NULL, 823, 113, 29, 3, 960, 'Glauberova sůl (dekahydrát síranu sodného,\r\n) je bílá krystalická látka s výraznými projímavými účinky, historicky využívaná k detoxikaci a očistě střev. Působí jako silné osmotické laxativum, které rychle vyprazdňuje trávicí trakt, ale při dlouhodobém užívání hrozí ztráta minerálů. Využívá se také v průmyslu (papírenství, prací prášky) a jako potravinářská přídatná látka E514', 3, '2026-02-20 11:02:25'),
(146, 2, NULL, 823, 113, 10, 3, 120, 'Glauberova sůl čistá\r\nZVC Dr. Hoffmann\r\nhttps://www.drhoffmann.cz › glauberova-sul-cista-id634\r\nGlauberova sůl je Síran sodný dekahydrát a používá se v lidovém lékařství k detoxikaci organizmu a jako mírné osmotické projímadlo.\r\n240,00 Kč · Skladem\r\nSíran sodný – Wikipedie\r\nWikipedia\r\nhttps://cs.wikipedia.org › wiki › Síran_sodný\r\nDekahydrát je známý od 17. století jako Glauberova sůl nebo mirabilit (historicky sal mirabilis). ... Glauberova sůl (dekahydrát) Sal mirabilis (dekahydrát).', 3, '2026-02-20 11:30:32'),
(147, 2, NULL, 823, 113, 67, 3, 1140, 'Ladíme jak ďas ', 3, '2026-02-20 14:06:27');

-- --------------------------------------------------------

--
-- Struktura tabulky `task_assignment_participants`
--

CREATE TABLE `task_assignment_participants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `minutes_spent` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `task_assignment_participants`
--

INSERT INTO `task_assignment_participants` (`id`, `company_id`, `assignment_id`, `user_id`, `minutes_spent`, `created_at`) VALUES
(11, 2, 145, 4, 480, '2026-02-20 11:02:25'),
(12, 2, 145, 3, 480, '2026-02-20 11:02:25'),
(13, 2, 146, 4, 60, '2026-02-20 11:30:32'),
(14, 2, 146, 3, 60, '2026-02-20 11:30:32'),
(15, 2, 147, 3, 1140, '2026-02-20 14:06:27');

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
(1, 1, NULL, 'AC-2026-002', 'Vadné čidlo teploty', 'Nesmyslné hodnoty na SCADA', 'phone', 'Výroba', 'vyroba@firma.cz', 'high', NULL, 'in_progress', 1, '2026-02-13 12:42:48', NULL),
(2, 1, NULL, 'AC-2026-003', 'Nouzový výjezd', 'Zkrat na lince', 'phone', 'Dispečink', 'dsp@firma.cz', 'emergency', NULL, 'in_progress', 2, '2026-02-17 12:42:48', NULL),
(3, 2, NULL, 'BE-2026-001', 'Montáž čerpadla', 'Instalace nové jednotky', 'personal', 'Technolog', 'tech@beta.cz', 'normal', NULL, 'done', 3, '2026-01-19 12:43:15', '2026-01-24 12:43:15'),
(4, 2, NULL, 'BE-2026-002', 'Porucha PLC', 'Restart nepomohl', 'system', 'Monitoring', 'monitor@beta.cz', 'high', NULL, 'in_progress', 3, '2026-02-15 12:43:15', NULL),
(5, 1, NULL, 'AC-2026-001', 'Revize rozvaděče', 'Pravidelná roční revize', 'email', 'Správa budovy', 'sprava@firma.cz', 'normal', NULL, 'done', 1, '2026-01-29 12:42:48', '2026-02-03 12:42:48'),
(112, 2, NULL, NULL, 'ladění kódu někde jinde', 'Dokonale odladit kód přidávání zakázek, přidávání tasků k nim a jednoznačné změny stavů zakázek', 'email', 'vaněk', '', 'normal', NULL, 'in_progress', 3, '2026-02-18 17:10:51', NULL),
(113, 2, NULL, NULL, 'ladění kódu', 'Dokonale odladit kód přidávání zakázek, přidávání tasků k nim a jednoznačné změny stavů zakázek', 'phone', 'vaněk', 'kdokoli', 'normal', NULL, 'in_progress', 3, '2026-02-18 17:15:34', NULL),
(114, 2, NULL, 'ENV-55', 'vykopání studny', 'Na pozemku 55 najít optimální místo a vykopat studnu', 'email', 'vaněk', 'Vaněk', 'normal', NULL, 'new', 3, '2026-02-21 04:56:58', NULL),
(115, 17, NULL, 'Nějaké číslo', 'Co je KDE?', 'Mezinárodní komunita vyvíjející nejlepší svobodný a otevřený software na světě.\r\nSoftware KDE pohání společnost NASA, CERN, elektrická vozidla Mercedes, Steam Deck, vašeho oblíbeného YouTubera stejně jako školy, vlády a úřady po celém světě.\r\n\r\nNáš software vdechuje nový život starým zařízením a ohromně nabíjí ta nová. Vítáme každého, kdo chce používat náš software zdarma nebo pomáhat při jeho vývoji!', 'email', 'Co je KDE?', 'Co je KDE?', 'high', NULL, 'new', 48, '2026-02-22 11:36:32', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `billing_exports`
--
ALTER TABLE `billing_exports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_billing_exports_company` (`company_id`),
  ADD KEY `idx_billing_exports_exported_at` (`exported_at`);

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
  ADD KEY `idx_tasks_company` (`company_id`),
  ADD KEY `idx_tasks_billing_export` (`billing_export_id`),
  ADD KEY `idx_tasks_company_status` (`company_id`,`status`);

--
-- Indexy pro tabulku `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_task_assignments_task` (`task_id`),
  ADD KEY `idx_task_assignments_parent` (`parent_id`),
  ADD KEY `idx_task_assignments_company` (`company_id`),
  ADD KEY `idx_task_assignments_work_order` (`work_order_id`);

--
-- Indexy pro tabulku `task_assignment_participants`
--
ALTER TABLE `task_assignment_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tap_assignment` (`assignment_id`),
  ADD KEY `idx_tap_user` (`user_id`),
  ADD KEY `idx_tap_company` (`company_id`);

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
-- AUTO_INCREMENT pro tabulku `billing_exports`
--
ALTER TABLE `billing_exports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `recurring_tasks`
--
ALTER TABLE `recurring_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=825;

--
-- AUTO_INCREMENT pro tabulku `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT pro tabulku `task_assignment_participants`
--
ALTER TABLE `task_assignment_participants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pro tabulku `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `task_assignment_participants`
--
ALTER TABLE `task_assignment_participants`
  ADD CONSTRAINT `fk_tap_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `task_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tap_user` FOREIGN KEY (`user_id`) REFERENCES `admin`.`users` (`id`);
COMMIT;
