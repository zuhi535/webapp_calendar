-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Sze 06. 20:58
-- Kiszolgáló verziója: 10.4.28-MariaDB
-- PHP verzió: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `webapp_calendar`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `action_logs`
--

CREATE TABLE `action_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('create','update','delete') NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `is_undoable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `is_image` tinyint(1) DEFAULT 0,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `is_all_day` tinyint(1) DEFAULT 0,
  `color` varchar(7) DEFAULT '#3B82F6',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `user_id`, `title`, `description`, `start_datetime`, `end_datetime`, `is_all_day`, `color`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Csapat megbeszélés', 'Heti rendszeres csapat egyeztetés a projektekről', '2025-08-26 10:00:00', '2025-08-26 11:00:00', 0, '#EF4444', '2025-08-28 16:44:48', '2025-08-26 20:20:15', '2025-08-28 16:44:48'),
(2, 1, 'Orvosi vizsgálat', 'Éves kontroll vizsgálat', '2025-08-27 14:30:00', '2025-08-27 15:30:00', 0, '#10B981', '2025-08-28 16:45:03', '2025-08-26 20:20:15', '2025-08-28 16:45:03'),
(3, 1, 'Projekt deadline', 'Webalkalmazás leadási határidő', '2025-08-28 23:59:00', '2025-08-28 23:59:00', 0, '#F59E0B', '2025-08-28 16:45:05', '2025-08-26 20:20:15', '2025-08-28 16:45:05'),
(4, 1, 'Családi vacsora', '', '2025-08-29 18:00:00', '2025-08-29 20:00:00', 0, '#8B5CF6', '2025-08-28 16:45:10', '2025-08-26 20:20:15', '2025-08-28 16:45:10'),
(5, 1, 'Szabadság kezdete', 'Kéthetes nyaralás', '2025-08-30 00:00:00', '2025-08-30 23:59:59', 0, '#06B6D4', '2025-08-28 16:45:07', '2025-08-26 20:20:15', '2025-08-28 16:45:07'),
(6, 1, 'evfordulo', '', '2025-12-17 00:00:00', '2025-12-17 23:59:59', 1, '#3498db', '2025-08-26 22:06:46', '2025-08-26 22:06:30', '2025-08-26 22:06:46'),
(7, 1, 'jon', '', '2025-08-05 09:00:00', '2025-08-05 10:00:00', 0, '#3498db', '2025-08-26 22:11:21', '2025-08-26 22:08:18', '2025-08-26 22:11:21'),
(8, 1, 'Vezetés rutinpálya (fizetés)', '', '2025-08-29 12:00:00', '2025-08-29 13:40:00', 0, '#3498db', NULL, '2025-08-28 16:45:34', '2025-08-28 16:45:34'),
(9, 1, 'Szorgalmi időszak kezdete', '', '2025-09-08 00:00:00', '2025-09-08 23:59:59', 1, '#3498db', '2025-08-28 17:19:40', '2025-08-28 16:45:52', '2025-08-28 17:19:40'),
(10, 1, 'Regisztrálciós / tárgyfelvétel', '', '2025-09-05 00:00:00', '2025-09-05 12:00:00', 0, '#3498db', '2025-08-28 17:19:20', '2025-08-28 16:46:23', '2025-08-28 17:19:20'),
(11, 1, 'Regisztrálciós / tárgyfelvétel', '', '2025-09-05 00:00:00', '2025-09-05 12:00:00', 0, '#EF4444', NULL, '2025-08-28 17:19:36', '2025-08-28 17:19:36'),
(12, 1, 'Szorgalmi időszak kezdete', '', '2025-09-08 00:00:00', '2025-09-08 23:59:59', 1, '#10B981', NULL, '2025-08-28 17:19:50', '2025-08-28 17:19:50');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('project','note') NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `sort_order` int(11) DEFAULT 0,
  `is_pinned` tinyint(1) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `collections`
--

INSERT INTO `collections` (`id`, `parent_id`, `user_id`, `name`, `type`, `description`, `color`, `tags`, `sort_order`, `is_pinned`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 'Munkahelyi projektek', 'project', 'Hivatalos munkahelyi feladatok és projektjeim', '#EF4444', NULL, 1, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(2, NULL, 1, 'Személyes projektek', 'project', 'Hobbi és személyes fejlesztési projektek', '#10B981', NULL, 2, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(3, NULL, 1, 'Napi jegyzetek', 'note', 'Gyors feljegyzések és gondolatok', '#8B5CF6', NULL, 1, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(4, NULL, 1, 'Ötletek', 'note', 'Kreatív ötletek és inspirációk', '#F59E0B', NULL, 2, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(5, 1, 1, 'Webes projektek', 'project', 'Frontend és backend fejlesztések', '#3B82F6', NULL, 1, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(6, 1, 1, 'Marketing kampányok', 'project', 'Reklám és promóciós anyagok', '#EC4899', NULL, 2, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(7, 2, 1, 'Programozás', 'project', 'Saját szoftverprojektek', '#06B6D4', NULL, 1, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(8, NULL, 1, 'teszt', 'project', 'asd', '#3498db', NULL, 0, 0, '2025-08-28 17:06:38', '2025-08-28 16:53:30', '2025-08-28 17:06:38'),
(9, NULL, 1, 'Szakdolgozat', 'project', 'Vegera József:\r\n IoT security: támadási felületek azonosítása és védelmi mechanizmusok fejlesztése.', '#8B5CF6', NULL, 0, 0, NULL, '2025-08-28 17:07:28', '2025-08-28 17:21:45');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `notes`
--

INSERT INTO `notes` (`id`, `collection_id`, `user_id`, `title`, `content`, `excerpt`, `is_pinned`, `sort_order`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 5, 1, 'React komponens tervezés', '<h2>Komponens architektúra</h2><p>A <strong>moduláris felépítés</strong> előnyei:</p><ul><li>Újrafelhasználhatóság</li><li>Könnyű karbantartás</li><li>Tiszta kód</li></ul>', 'React komponens architektúra és moduláris felépítés előnyei...', 0, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(2, 3, 1, 'Mai teendők', '<p>Ma elvégzendő feladatok:</p><ul><li>Naptár modul befejezése</li><li>CSS optimalizálás</li><li>Tesztelés mobilon</li></ul>', 'Mai elvégzendő feladatok listája...', 0, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(3, 4, 1, 'App ötlet: Étkezési napló', '<h2>Koncepció</h2><p>Egy <em>intelligens étkezési napló</em> amely:</p><ul><li>Automatikusan felismeri az ételeket</li><li>Kalóriát számol</li><li>Ajánlásokat ad</li></ul>', 'Intelligens étkezési napló alkalmazás ötlete...', 0, 0, NULL, '2025-08-26 20:20:15', '2025-08-26 20:20:15'),
(4, 9, 1, 'teszt', '**asd**\r\n\r\n_asd_\r\n\r\n__asdasd__', '**asd**\r\n\r\n_asd_\r\n\r\n__asdasd__', 0, 0, NULL, '2025-08-28 17:22:13', '2025-08-28 17:22:13');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-08-26 20:20:14', '2025-08-26 20:20:14');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `action_logs`
--
ALTER TABLE `action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`),
  ADD KEY `idx_undoable` (`is_undoable`);

--
-- A tábla indexei `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_note` (`note_id`),
  ADD KEY `idx_type` (`is_image`);

--
-- A tábla indexei `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`start_datetime`),
  ADD KEY `idx_date_range` (`start_datetime`,`end_datetime`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- A tábla indexei `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`type`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- A tábla indexei `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_collection` (`collection_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_deleted` (`deleted_at`);
ALTER TABLE `notes` ADD FULLTEXT KEY `ft_search` (`title`,`content`,`excerpt`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `action_logs`
--
ALTER TABLE `action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT a táblához `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT a táblához `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `action_logs`
--
ALTER TABLE `action_logs`
  ADD CONSTRAINT `action_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `collections`
--
ALTER TABLE `collections`
  ADD CONSTRAINT `collections_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collections_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
