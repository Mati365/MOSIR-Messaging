-- phpMyAdmin SQL Dump
-- version 4.2.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas generowania: 12 Sty 2015, 12:54
-- Wersja serwera: 5.5.40-0ubuntu1
-- Wersja PHP: 5.5.12-2ubuntu4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `reports`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `locked_users`
--

CREATE TABLE IF NOT EXISTS `locked_users` (
`id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `locked_zones`
--

CREATE TABLE IF NOT EXISTS `locked_zones` (
`id` int(11) NOT NULL,
  `object_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=198 ;

--
-- Zrzut danych tabeli `locked_zones`
--

INSERT INTO `locked_zones` (`id`, `object_id`, `zone_id`) VALUES
(1, 1, 1),
(6, 2, 2),
(7, 2, 3),
(8, 2, 4),
(21, 4, 6),
(22, 4, 7),
(23, 4, 8),
(24, 4, 9),
(26, 5, 10),
(27, 5, 11),
(33, 6, 12),
(34, 6, 13),
(35, 6, 14),
(36, 6, 15),
(38, 7, 1),
(45, 8, 16),
(46, 8, 17),
(47, 8, 18),
(48, 8, 19),
(50, 9, 1),
(105, 10, 18),
(106, 10, 20),
(107, 10, 21),
(108, 10, 22),
(109, 10, 23),
(110, 10, 24),
(111, 10, 33),
(112, 10, 34),
(113, 10, 35),
(114, 10, 36),
(115, 10, 37),
(117, 11, 1),
(119, 12, 1),
(121, 13, 1),
(123, 14, 1),
(125, 15, 1),
(127, 16, 1),
(158, 17, 42),
(159, 17, 43),
(160, 17, 44),
(161, 17, 45),
(162, 17, 46),
(163, 17, 47),
(164, 17, 48),
(165, 17, 49),
(166, 17, 50),
(167, 17, 51),
(168, 17, 52),
(169, 17, 54),
(170, 17, 55),
(171, 17, 56),
(172, 17, 57),
(173, 17, 58),
(174, 17, 59),
(175, 17, 60),
(176, 17, 61),
(177, 17, 62),
(178, 17, 63),
(187, 18, 64),
(188, 18, 65),
(194, 3, 66),
(195, 3, 67),
(196, 3, 68),
(197, 3, 69);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
`id` int(11) NOT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `messages_queue`
--

CREATE TABLE IF NOT EXISTS `messages_queue` (
`id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  `report_info_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `objects`
--

CREATE TABLE IF NOT EXISTS `objects` (
`id` int(11) NOT NULL,
  `name` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=19 ;

--
-- Zrzut danych tabeli `objects`
--

INSERT INTO `objects` (`id`, `name`) VALUES
(1, '*'),
(2, 'Hala Milenium'),
(3, 'Łącznik'),
(4, 'Hotel'),
(5, 'Hotel-Apartament'),
(6, 'Hala Basenowa'),
(7, 'Restauracja'),
(8, 'Hala Łuczniczka'),
(9, 'Euroboisko'),
(10, 'Stadion'),
(11, 'Hala przy Wąskiej'),
(12, 'Orlik na Makuszyńskiego'),
(13, 'Wylotowa'),
(14, 'Skatepark na Ogrodach'),
(15, 'Molo'),
(16, 'Pas techniczny'),
(17, 'Plaża'),
(18, 'Otoczenie Milenium');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reports_info`
--

CREATE TABLE IF NOT EXISTS `reports_info` (
`id` int(11) NOT NULL,
  `object` int(11) NOT NULL,
  `zone` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  `realization_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
`id` int(11) NOT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `flag` int(11) NOT NULL DEFAULT '0',
  `def_value` varchar(22) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(22) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Zrzut danych tabeli `settings`
--

INSERT INTO `settings` (`id`, `name`, `description`, `type`, `flag`, `def_value`, `icon`) VALUES
(2, 'object_filter', 'Obiekt', 2, 1, '1', 'home'),
(3, 'zone_filter', 'Strefa', 2, 1, '1', 'shopping-cart'),
(4, 'hide_viewed', 'Ukryj przeczyt.', 0, 3, '2', 'eye-open'),
(5, 'hide_done', 'Ukryj zrealiz.', 0, 3, '32', 'ok'),
(6, 'hide_removed', 'Ukryj usunięte', 0, 3, '4', 'trash'),
(7, 'show_reply', 'Ukryj odp.', 0, 3, '16', 'share-alt'),
(8, 'first_login', 'Pierwsze zalogowanie', 0, 0, '0', ''),
(9, 'view_limit', 'Wyświetlaj', 1, 1, '100', 'list');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
  `login` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `name` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `surname` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `job` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `type`, `name`, `surname`, `job`) VALUES
(1, 'admin', 'a7410477c0e335ae40ba3c9c9e74e57f00dec711e07e0c2372c4178e48b501f0', 7, 'Mateusz', 'Bagiński', 'Administrator systemu');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users_settings`
--

CREATE TABLE IF NOT EXISTS `users_settings` (
`id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `setting_id` int(11) NOT NULL,
  `value` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Zrzut danych tabeli `users_settings`
--

INSERT INTO `users_settings` (`id`, `user_id`, `setting_id`, `value`) VALUES
(1, 1, 2, '1'),
(2, 1, 3, '1'),
(3, 1, 4, '0'),
(4, 1, 5, '0'),
(5, 1, 6, '0'),
(6, 1, 7, '0'),
(7, 1, 8, '1'),
(8, 1, 9, '100');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users_types`
--

CREATE TABLE IF NOT EXISTS `users_types` (
`id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Zrzut danych tabeli `users_types`
--

INSERT INTO `users_types` (`id`, `name`) VALUES
(1, 'Użytkownik'),
(3, 'Moderator'),
(7, 'Administrator');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zones`
--

CREATE TABLE IF NOT EXISTS `zones` (
`id` int(11) NOT NULL,
  `name` text COLLATE utf8_polish_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci AUTO_INCREMENT=70 ;

--
-- Zrzut danych tabeli `zones`
--

INSERT INTO `zones` (`id`, `name`) VALUES
(1, '*'),
(2, 'Biura'),
(3, 'Parkiet'),
(4, 'Trybuny'),
(6, 'Pokoje 01-05 Nowa część'),
(7, 'Pokoje 101-105 Nowa część'),
(8, 'Pokoje 201-208'),
(9, 'Pokoje 301-312'),
(10, 'Sala konferencyna duża'),
(11, 'Sala konferencyjna prasowa'),
(12, 'Szatnia damska'),
(13, 'Szatnia męska'),
(14, 'Niepełnosprawni damska'),
(15, 'Niepełnosprawni męska'),
(16, 'Lodowisko'),
(17, 'Tory Łucznicze'),
(18, 'Sala konferencyjna'),
(19, 'Budynek'),
(20, 'Trybuna A'),
(21, 'Trybuna B'),
(22, 'Klasa A1'),
(23, 'Klasa A2'),
(24, 'Klasa B1'),
(33, 'Klasa B2'),
(34, 'Szatnia gości'),
(35, 'Szatnia gospodarzy'),
(36, 'Kontrola antydopingowa'),
(37, 'Skatepark'),
(38, 'Dojścia'),
(42, 'Zejście 1'),
(43, 'Zejście 2'),
(44, 'Zejście 3'),
(45, 'Zejście 4'),
(46, 'Zejście 5'),
(47, 'Zejście 6'),
(48, 'Zejście 7'),
(49, 'Zejście 8'),
(50, 'Zejście 9'),
(51, 'Zejście 10'),
(52, 'Zejście 11'),
(54, 'Zejście 12'),
(55, 'Baza 1'),
(56, 'Baza 2'),
(57, 'Zjazd tech. 1'),
(58, 'Zjazd tech. 2'),
(59, 'Zjazd tech. 3'),
(60, 'Zjazd tech. 4'),
(61, 'Zjazd tech. 5'),
(62, 'Zjazd tech. 6'),
(63, 'Zjazd tech. 7'),
(64, 'Parking'),
(65, 'Tereny wokół hali, basenu i Łuczniczki'),
(66, 'Korytarz/klatka piętro 0'),
(67, 'Korytarz/klatka piętro 1'),
(68, 'Korytarz/klatka piętro 2'),
(69, 'Korytarz/klatka piętro 3');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indexes for table `locked_users`
--
ALTER TABLE `locked_users`
 ADD PRIMARY KEY (`id`), ADD KEY `object_id` (`object_id`), ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `locked_zones`
--
ALTER TABLE `locked_zones`
 ADD PRIMARY KEY (`id`), ADD KEY `object_id` (`object_id`), ADD KEY `zone_id` (`zone_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages_queue`
--
ALTER TABLE `messages_queue`
 ADD PRIMARY KEY (`id`), ADD KEY `content_id` (`content_id`), ADD KEY `report_info_id` (`report_info_id`), ADD KEY `from_id` (`from_id`,`to_id`), ADD KEY `reply_id` (`reply_id`);

--
-- Indexes for table `objects`
--
ALTER TABLE `objects`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports_info`
--
ALTER TABLE `reports_info`
 ADD PRIMARY KEY (`id`), ADD KEY `object` (`object`,`zone`), ADD KEY `zone` (`zone`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`);

--
-- Indexes for table `users_settings`
--
ALTER TABLE `users_settings`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`,`setting_id`), ADD KEY `users_settings_ibfk_2` (`setting_id`);

--
-- Indexes for table `users_types`
--
ALTER TABLE `users_types`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT dla tabeli `locked_users`
--
ALTER TABLE `locked_users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `locked_zones`
--
ALTER TABLE `locked_zones`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=198;
--
-- AUTO_INCREMENT dla tabeli `messages`
--
ALTER TABLE `messages`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `messages_queue`
--
ALTER TABLE `messages_queue`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `objects`
--
ALTER TABLE `objects`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT dla tabeli `reports_info`
--
ALTER TABLE `reports_info`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT dla tabeli `settings`
--
ALTER TABLE `settings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT dla tabeli `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT dla tabeli `users_settings`
--
ALTER TABLE `users_settings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT dla tabeli `users_types`
--
ALTER TABLE `users_types`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT dla tabeli `zones`
--
ALTER TABLE `zones`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=70;
--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `locked_users`
--
ALTER TABLE `locked_users`
ADD CONSTRAINT `locked_users_ibfk_1` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`),
ADD CONSTRAINT `locked_users_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ograniczenia dla tabeli `locked_zones`
--
ALTER TABLE `locked_zones`
ADD CONSTRAINT `locked_zones_ibfk_1` FOREIGN KEY (`object_id`) REFERENCES `objects` (`id`),
ADD CONSTRAINT `locked_zones_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`);

--
-- Ograniczenia dla tabeli `messages_queue`
--
ALTER TABLE `messages_queue`
ADD CONSTRAINT `messages_queue_ibfk_1` FOREIGN KEY (`report_info_id`) REFERENCES `reports_info` (`id`),
ADD CONSTRAINT `messages_queue_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `messages` (`id`),
ADD CONSTRAINT `messages_queue_ibfk_3` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`);

--
-- Ograniczenia dla tabeli `reports_info`
--
ALTER TABLE `reports_info`
ADD CONSTRAINT `reports_info_ibfk_1` FOREIGN KEY (`object`) REFERENCES `objects` (`id`),
ADD CONSTRAINT `reports_info_ibfk_2` FOREIGN KEY (`zone`) REFERENCES `zones` (`id`);

--
-- Ograniczenia dla tabeli `users`
--
ALTER TABLE `users`
ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`type`) REFERENCES `users_types` (`id`);

--
-- Ograniczenia dla tabeli `users_settings`
--
ALTER TABLE `users_settings`
ADD CONSTRAINT `users_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
ADD CONSTRAINT `users_settings_ibfk_2` FOREIGN KEY (`setting_id`) REFERENCES `settings` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
