-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 04:23 PM
-- Server version: 8.0.44-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webtech_2025A_yelsom_sanid`
--

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `name`, `logo`, `country`, `created_at`) VALUES
(1, 'Premier League', 'https://upload.wikimedia.org/wikipedia/en/thumb/f/f2/Premier_League_Logo.svg/1200px-Premier_League_Logo.svg.png', 'England', '2025-12-13 12:32:54'),
(2, 'La Liga', 'https://upload.wikimedia.org/wikipedia/commons/0/0f/LaLiga_logo_2023.svg', 'Spain', '2025-12-13 12:32:54'),
(4, 'Bundesliga', 'https://upload.wikimedia.org/wikipedia/en/thumb/d/df/Bundesliga_logo_%282017%29.svg/512px-Bundesliga_logo_%282017%29.svg.png', 'Germany', '2025-12-13 12:32:54'),
(5, 'Serie A', 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/Serie_A_logo_2022.svg/402px-Serie_A_logo_2022.svg.png', 'Italy', '2025-12-13 12:32:54'),
(6, 'Ligue 1', 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Ligue1_Uber_Eats_logo.png/600px-Ligue1_Uber_Eats_logo.png', 'France', '2025-12-13 12:32:54');

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int NOT NULL,
  `home_team` int NOT NULL,
  `away_team` int NOT NULL,
  `home_team_score` int DEFAULT NULL,
  `away_team_score` int DEFAULT NULL,
  `home_team_penalties` int DEFAULT NULL,
  `away_team_penalties` int DEFAULT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `competition_id` int DEFAULT NULL,
  `venue_id` int DEFAULT NULL,
  `round` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('scheduled','in_play','HT','FT','ET','PEN','postponed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'scheduled',
  `highlights_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_report` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `home_team`, `away_team`, `home_team_score`, `away_team_score`, `home_team_penalties`, `away_team_penalties`, `match_date`, `match_time`, `competition_id`, `venue_id`, `round`, `status`, `highlights_url`, `match_report`, `created_at`) VALUES
(1, 6, 7, 3, 1, NULL, NULL, '2025-12-14', '15:30:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(2, 4, 17, NULL, NULL, NULL, NULL, '2025-12-20', '19:45:00', 2, NULL, NULL, 'scheduled', NULL, NULL, '2025-12-13 12:38:43'),
(3, 5, 15, NULL, NULL, NULL, NULL, '2025-12-21', '20:40:00', 2, NULL, NULL, 'scheduled', NULL, NULL, '2025-12-13 12:38:43'),
(4, 9, 22, 3, 2, NULL, NULL, '2025-12-13', '12:00:00', 4, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(5, 21, 13, 1, 2, NULL, NULL, '2025-12-13', '14:00:00', 5, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(6, 11, 20, NULL, NULL, NULL, NULL, '2025-12-20', '23:30:00', 5, NULL, NULL, 'scheduled', NULL, NULL, '2025-12-13 12:38:43'),
(7, 14, 26, 4, 3, NULL, NULL, '2025-12-13', '18:30:00', 6, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(8, 6, 2, 2, 1, NULL, NULL, '2025-10-04', '16:30:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(9, 3, 8, 3, 0, NULL, NULL, '2025-10-20', '19:00:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(10, 1, 7, 0, 5, NULL, NULL, '2025-10-25', '13:30:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(11, 5, 17, 5, 1, NULL, NULL, '2025-11-01', '15:00:00', 2, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(12, 4, 15, 1, 2, NULL, NULL, '2025-11-09', '14:00:00', 2, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(13, 12, 13, 3, 3, NULL, NULL, '2025-11-22', '14:00:00', 5, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(14, 11, 21, 2, 2, NULL, NULL, '2025-11-24', '17:30:00', 5, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(15, 22, 23, 3, 2, NULL, NULL, '2025-10-31', '20:00:00', 4, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(16, 26, 25, 5, 3, NULL, NULL, '2025-12-13', '16:30:00', 6, NULL, NULL, 'FT', NULL, NULL, '2025-12-13 12:38:43'),
(18, 18, 16, NULL, NULL, NULL, NULL, '2025-12-19', '16:45:00', 2, NULL, NULL, 'postponed', NULL, NULL, '2025-12-14 03:06:14'),
(19, 2, 8, 5, 2, NULL, NULL, '2025-12-06', '15:30:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-14 03:08:25'),
(20, 1, 7, NULL, NULL, NULL, NULL, '2025-12-28', '16:30:00', 1, NULL, NULL, 'scheduled', NULL, NULL, '2025-12-18 21:09:22'),
(22, 3, 6, NULL, NULL, NULL, NULL, '2025-12-22', '15:30:00', 1, NULL, NULL, 'scheduled', NULL, NULL, '2025-12-19 10:23:12'),
(23, 3, 7, 2, 2, NULL, NULL, '2025-12-19', '18:30:00', 1, NULL, NULL, 'FT', NULL, NULL, '2025-12-19 10:25:04');

-- --------------------------------------------------------

--
-- Table structure for table `match_events`
--

CREATE TABLE `match_events` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team_id` int NOT NULL,
  `player_id` int DEFAULT NULL,
  `related_player_id` int DEFAULT NULL,
  `event_type` enum('goal','yellow_card','red_card','substitution','penalty','own_goal','var_decision') COLLATE utf8mb4_general_ci NOT NULL,
  `minute` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `match_events`
--

INSERT INTO `match_events` (`id`, `match_id`, `team_id`, `player_id`, `related_player_id`, `event_type`, `minute`, `created_at`) VALUES
(6, 8, 6, 18, NULL, 'goal', 24, '2025-12-13 14:10:57'),
(7, 8, 6, 17, NULL, 'goal', 56, '2025-12-13 14:10:57'),
(8, 8, 2, 4, NULL, 'goal', 80, '2025-12-13 14:10:57'),
(9, 8, 6, 15, NULL, 'yellow_card', 32, '2025-12-13 14:10:57'),
(10, 8, 2, 31, NULL, 'yellow_card', 45, '2025-12-13 14:10:57'),
(11, 8, 2, 30, NULL, 'yellow_card', 67, '2025-12-13 14:10:57'),
(12, 8, 6, 21, 18, 'substitution', 65, '2025-12-13 14:10:57'),
(13, 8, 6, 22, 16, 'substitution', 72, '2025-12-13 14:10:57'),
(14, 8, 6, 19, 17, 'substitution', 85, '2025-12-13 14:10:57'),
(15, 8, 2, 36, 37, 'substitution', 60, '2025-12-13 14:10:57'),
(16, 8, 2, 32, 33, 'substitution', 70, '2025-12-13 14:10:57'),
(17, 8, 2, 35, 34, 'substitution', 75, '2025-12-13 14:10:57'),
(18, 9, 3, 7, NULL, 'goal', 15, '2025-12-13 14:10:57'),
(19, 9, 3, 87, NULL, 'goal', 35, '2025-12-13 14:10:57'),
(20, 9, 3, 88, NULL, 'goal', 60, '2025-12-13 14:10:57'),
(21, 9, 3, 83, NULL, 'yellow_card', 28, '2025-12-13 14:10:57'),
(22, 9, 8, 99, NULL, 'yellow_card', 40, '2025-12-13 14:10:57'),
(23, 9, 8, 95, NULL, 'yellow_card', 55, '2025-12-13 14:10:57'),
(24, 9, 8, 102, NULL, 'yellow_card', 82, '2025-12-13 14:10:57'),
(25, 9, 8, 95, NULL, 'red_card', 72, '2025-12-13 14:10:57'),
(26, 9, 3, 90, 7, 'substitution', 65, '2025-12-13 14:10:57'),
(27, 9, 3, 86, 83, 'substitution', 75, '2025-12-13 14:10:57'),
(28, 9, 3, 91, 87, 'substitution', 80, '2025-12-13 14:10:57'),
(29, 9, 8, 107, 109, 'substitution', 55, '2025-12-13 14:10:57'),
(30, 9, 8, 108, 100, 'substitution', 68, '2025-12-13 14:10:57'),
(31, 9, 8, 110, 106, 'substitution', 73, '2025-12-13 14:10:57'),
(32, 10, 7, 70, NULL, 'goal', 10, '2025-12-13 14:10:57'),
(33, 10, 7, 70, NULL, 'goal', 45, '2025-12-13 14:10:57'),
(34, 10, 7, 70, NULL, 'goal', 60, '2025-12-13 14:10:57'),
(35, 10, 7, 69, NULL, 'goal', 75, '2025-12-13 14:10:57'),
(36, 10, 7, 64, NULL, 'goal', 85, '2025-12-13 14:10:57'),
(37, 10, 1, 53, NULL, 'yellow_card', 25, '2025-12-13 14:10:57'),
(38, 10, 1, 43, NULL, 'yellow_card', 50, '2025-12-13 14:10:57'),
(39, 10, 1, 44, NULL, 'yellow_card', 70, '2025-12-13 14:10:57'),
(40, 10, 7, 62, NULL, 'yellow_card', 40, '2025-12-13 14:10:57'),
(41, 10, 1, 50, NULL, 'substitution', 60, '2025-12-13 14:10:57'),
(42, 10, 1, 48, 49, 'substitution', 70, '2025-12-13 14:10:57'),
(43, 10, 1, 46, 47, 'substitution', 75, '2025-12-13 14:10:57'),
(44, 10, 7, 73, 69, 'substitution', 70, '2025-12-13 14:10:57'),
(45, 10, 7, 67, 64, 'substitution', 75, '2025-12-13 14:10:57'),
(46, 10, 7, 74, 63, 'substitution', 80, '2025-12-13 14:10:57'),
(47, 11, 5, 127, NULL, 'goal', 12, '2025-12-13 14:10:57'),
(48, 11, 5, 127, NULL, 'goal', 30, '2025-12-13 14:10:57'),
(49, 11, 5, 126, NULL, 'goal', 50, '2025-12-13 14:10:57'),
(50, 11, 5, 128, NULL, 'goal', 65, '2025-12-13 14:10:57'),
(51, 11, 5, 121, NULL, 'goal', 80, '2025-12-13 14:10:57'),
(52, 11, 17, 183, NULL, 'goal', 70, '2025-12-13 14:10:57'),
(53, 11, 5, 120, NULL, 'yellow_card', 55, '2025-12-13 14:10:57'),
(54, 11, 17, 176, NULL, 'yellow_card', 35, '2025-12-13 14:10:57'),
(55, 11, 17, 177, NULL, 'yellow_card', 68, '2025-12-13 14:10:57'),
(56, 11, 5, 129, NULL, 'substitution', 70, '2025-12-13 14:10:57'),
(57, 11, 5, 123, 121, 'substitution', 75, '2025-12-13 14:10:57'),
(58, 11, 5, 130, 128, 'substitution', 82, '2025-12-13 14:10:57'),
(59, 11, 17, 185, 184, 'substitution', 60, '2025-12-13 14:10:57'),
(60, 11, 17, 180, 178, 'substitution', 70, '2025-12-13 14:10:57'),
(61, 11, 17, 182, 179, 'substitution', 75, '2025-12-13 14:10:57'),
(62, 12, 15, 165, NULL, 'goal', 20, '2025-12-13 14:10:57'),
(63, 12, 4, 146, NULL, 'goal', 55, '2025-12-13 14:10:57'),
(64, 12, 15, 164, NULL, 'goal', 88, '2025-12-13 14:10:57'),
(65, 12, 4, 140, NULL, 'yellow_card', 30, '2025-12-13 14:10:57'),
(66, 12, 4, 134, NULL, 'yellow_card', 62, '2025-12-13 14:10:57'),
(67, 12, 15, 158, NULL, 'yellow_card', 45, '2025-12-13 14:10:57'),
(68, 12, 15, 159, NULL, 'yellow_card', 75, '2025-12-13 14:10:57'),
(69, 12, 4, 148, 145, 'substitution', 65, '2025-12-13 14:10:57'),
(70, 12, 4, 143, 144, 'substitution', 70, '2025-12-13 14:10:57'),
(71, 12, 4, 149, 146, 'substitution', 80, '2025-12-13 14:10:57'),
(72, 12, 15, 166, 165, 'substitution', 70, '2025-12-13 14:10:57'),
(73, 12, 15, 163, 161, 'substitution', 75, '2025-12-13 14:10:57'),
(74, 12, 15, 167, 164, 'substitution', 85, '2025-12-13 14:10:57'),
(75, 13, 12, 231, NULL, 'goal', 18, '2025-12-13 14:10:57'),
(76, 13, 12, 233, NULL, 'goal', 35, '2025-12-13 14:10:57'),
(77, 13, 12, 232, NULL, 'goal', 77, '2025-12-13 14:10:57'),
(78, 13, 13, 252, NULL, 'goal', 25, '2025-12-13 14:10:57'),
(79, 13, 13, 238, NULL, 'goal', 60, '2025-12-13 14:10:57'),
(80, 13, 13, 248, NULL, 'goal', 85, '2025-12-13 14:10:57'),
(81, 13, 12, 227, NULL, 'yellow_card', 40, '2025-12-13 14:10:57'),
(82, 13, 12, 222, NULL, 'yellow_card', 68, '2025-12-13 14:10:57'),
(83, 13, 13, 247, NULL, 'yellow_card', 50, '2025-12-13 14:10:57'),
(84, 13, 13, 241, NULL, 'yellow_card', 82, '2025-12-13 14:10:57'),
(85, 13, 12, 234, 232, 'substitution', 70, '2025-12-13 14:10:57'),
(86, 13, 12, 230, 228, 'substitution', 75, '2025-12-13 14:10:57'),
(87, 13, 12, 236, 231, 'substitution', 85, '2025-12-13 14:10:57'),
(88, 13, 13, 254, 238, 'substitution', 65, '2025-12-13 14:10:57'),
(89, 13, 13, 251, 247, 'substitution', 75, '2025-12-13 14:10:57'),
(90, 13, 13, 250, 248, 'substitution', 80, '2025-12-13 14:10:57'),
(91, 14, 11, 271, NULL, 'goal', 22, '2025-12-13 14:10:57'),
(92, 14, 11, 270, NULL, 'goal', 68, '2025-12-13 14:10:57'),
(93, 14, 21, 289, NULL, 'goal', 30, '2025-12-13 14:10:57'),
(94, 14, 21, 288, NULL, 'goal', 75, '2025-12-13 14:10:57'),
(95, 14, 11, 265, NULL, 'yellow_card', 45, '2025-12-13 14:10:57'),
(96, 14, 11, 259, NULL, 'yellow_card', 70, '2025-12-13 14:10:57'),
(97, 14, 21, 283, NULL, 'yellow_card', 55, '2025-12-13 14:10:57'),
(98, 14, 21, 277, NULL, 'yellow_card', 80, '2025-12-13 14:10:57'),
(99, 14, 11, 272, 270, 'substitution', 65, '2025-12-13 14:10:57'),
(100, 14, 11, 269, 267, 'substitution', 75, '2025-12-13 14:10:57'),
(101, 14, 11, 274, 271, 'substitution', 82, '2025-12-13 14:10:57'),
(102, 14, 21, 287, 289, 'substitution', 70, '2025-12-13 14:10:57'),
(103, 14, 21, 284, 283, 'substitution', 78, '2025-12-13 14:10:57'),
(104, 14, 21, 290, 288, 'substitution', 85, '2025-12-13 14:10:57'),
(105, 15, 22, 386, NULL, 'goal', 15, '2025-12-13 14:10:57'),
(106, 15, 22, 386, NULL, 'goal', 42, '2025-12-13 14:10:57'),
(107, 15, 22, 382, NULL, 'goal', 78, '2025-12-13 14:10:57'),
(108, 15, 23, 370, NULL, 'goal', 25, '2025-12-13 14:10:57'),
(109, 15, 23, 369, NULL, 'goal', 85, '2025-12-13 14:10:57'),
(110, 15, 22, 377, NULL, 'yellow_card', 35, '2025-12-13 14:10:57'),
(111, 15, 22, 381, NULL, 'yellow_card', 62, '2025-12-13 14:10:57'),
(112, 15, 23, 365, NULL, 'yellow_card', 50, '2025-12-13 14:10:57'),
(113, 15, 23, 361, NULL, 'yellow_card', 72, '2025-12-13 14:10:57'),
(114, 15, 22, 387, 382, 'substitution', 70, '2025-12-13 14:10:57'),
(115, 15, 22, 389, 386, 'substitution', 80, '2025-12-13 14:10:57'),
(116, 15, 22, 385, 383, 'substitution', 85, '2025-12-13 14:10:57'),
(117, 15, 23, 372, 370, 'substitution', 65, '2025-12-13 14:10:57'),
(118, 15, 23, 371, 366, 'substitution', 75, '2025-12-13 14:10:57'),
(119, 15, 23, 373, 369, 'substitution', 82, '2025-12-13 14:10:57'),
(120, 16, 26, 437, NULL, 'goal', 12, '2025-12-13 14:10:57'),
(121, 16, 26, 436, NULL, 'goal', 28, '2025-12-13 14:10:57'),
(122, 16, 26, 437, NULL, 'goal', 55, '2025-12-13 14:10:57'),
(123, 16, 26, 438, NULL, 'goal', 72, '2025-12-13 14:10:57'),
(124, 16, 26, 439, NULL, 'goal', 85, '2025-12-13 14:10:57'),
(125, 16, 25, 454, NULL, 'goal', 20, '2025-12-13 14:10:57'),
(126, 16, 25, 454, NULL, 'goal', 65, '2025-12-13 14:10:57'),
(127, 16, 25, 455, NULL, 'goal', 80, '2025-12-13 14:10:57'),
(128, 16, 26, 434, NULL, 'yellow_card', 40, '2025-12-13 14:10:57'),
(129, 16, 26, 428, NULL, 'yellow_card', 70, '2025-12-13 14:10:57'),
(130, 16, 25, 448, NULL, 'yellow_card', 35, '2025-12-13 14:10:57'),
(131, 16, 25, 444, NULL, 'yellow_card', 75, '2025-12-13 14:10:57'),
(132, 16, 26, 440, 436, 'substitution', 70, '2025-12-13 14:10:57'),
(133, 16, 26, 441, 438, 'substitution', 80, '2025-12-13 14:10:57'),
(134, 16, 26, 433, 437, 'substitution', 88, '2025-12-13 14:10:57'),
(135, 16, 25, 456, 454, 'substitution', 60, '2025-12-13 14:10:57'),
(136, 16, 25, 457, 453, 'substitution', 70, '2025-12-13 14:10:57'),
(137, 16, 25, 452, 448, 'substitution', 78, '2025-12-13 14:10:57'),
(138, 1, 6, 17, NULL, 'goal', 15, '2025-12-19 09:49:06'),
(139, 1, 6, 19, NULL, 'goal', 40, '2025-12-19 09:49:42'),
(140, 1, 7, 70, NULL, 'goal', 49, '2025-12-19 09:50:32'),
(141, 1, 6, 11, NULL, 'goal', 78, '2025-12-19 09:51:00'),
(142, 1, 6, 20, 19, 'substitution', 65, '2025-12-19 09:52:04'),
(143, 1, 7, 74, 63, 'substitution', 68, '2025-12-19 09:52:46'),
(144, 1, 6, 23, 11, 'substitution', 83, '2025-12-19 09:53:25'),
(145, 1, 6, 24, 18, 'substitution', 55, '2025-12-19 09:54:41'),
(146, 1, 7, 71, 72, 'substitution', 60, '2025-12-19 09:55:17'),
(147, 1, 6, 15, NULL, 'yellow_card', 30, '2025-12-19 09:55:39'),
(148, 1, 6, 17, NULL, 'yellow_card', 60, '2025-12-19 09:55:53'),
(149, 1, 7, 67, NULL, 'yellow_card', 40, '2025-12-19 09:56:17'),
(150, 19, 2, 35, NULL, 'goal', 2, '2025-12-19 09:57:32'),
(151, 19, 2, 32, NULL, 'goal', 13, '2025-12-19 09:57:45'),
(152, 19, 2, 35, NULL, 'goal', 25, '2025-12-19 09:58:04'),
(153, 19, 2, 4, NULL, 'goal', 48, '2025-12-19 09:58:21'),
(154, 19, 8, 107, NULL, 'goal', 52, '2025-12-19 09:58:38'),
(155, 19, 8, 96, NULL, 'goal', 54, '2025-12-19 09:58:52'),
(156, 19, 2, 34, NULL, 'goal', 82, '2025-12-19 09:59:13'),
(157, 19, 2, 34, 35, 'substitution', 76, '2025-12-19 09:59:40'),
(158, 19, 2, 26, 39, 'substitution', 62, '2025-12-19 10:00:11'),
(159, 19, 8, 106, 110, 'substitution', 65, '2025-12-19 10:00:59'),
(160, 19, 8, 104, NULL, 'red_card', 44, '2025-12-19 10:01:34'),
(161, 19, 2, 30, NULL, 'yellow_card', 60, '2025-12-19 10:01:56'),
(162, 19, 8, 92, NULL, 'yellow_card', 78, '2025-12-19 10:02:16'),
(163, 23, 3, 7, NULL, 'goal', 30, '2025-12-19 10:25:45'),
(164, 23, 7, 70, NULL, 'goal', 40, '2025-12-19 10:25:56'),
(165, 23, 7, 69, NULL, 'goal', 63, '2025-12-19 10:26:11'),
(166, 23, 3, 7, NULL, 'goal', 93, '2025-12-19 10:26:41'),
(167, 23, 7, 59, NULL, 'yellow_card', 80, '2025-12-19 10:27:01'),
(168, 23, 3, 79, NULL, 'yellow_card', 67, '2025-12-19 10:27:15'),
(169, 23, 3, 89, 88, 'substitution', 65, '2025-12-19 10:28:13'),
(170, 23, 7, 71, 63, 'substitution', 65, '2025-12-19 10:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `match_stats`
--

CREATE TABLE `match_stats` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `team_id` int NOT NULL,
  `possession` int DEFAULT '50',
  `shots` int DEFAULT '0',
  `shots_on_target` int DEFAULT '0',
  `corners` int DEFAULT '0',
  `fouls` int DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `yellow_cards` int DEFAULT '0',
  `red_cards` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `match_stats`
--

INSERT INTO `match_stats` (`id`, `match_id`, `team_id`, `possession`, `shots`, `shots_on_target`, `corners`, `fouls`, `created_at`, `yellow_cards`, `red_cards`) VALUES
(127, 8, 6, 43, 14, 6, 5, 13, '2025-12-13 14:10:57', 1, 0),
(128, 8, 2, 57, 21, 9, 9, 11, '2025-12-13 14:10:57', 2, 0),
(129, 9, 3, 52, 14, 7, 5, 9, '2025-12-13 14:10:57', 1, 0),
(130, 9, 8, 48, 8, 2, 3, 12, '2025-12-13 14:10:57', 3, 1),
(131, 10, 1, 30, 3, 1, 2, 11, '2025-12-13 14:10:57', 3, 0),
(132, 10, 7, 70, 22, 10, 9, 6, '2025-12-13 14:10:57', 1, 0),
(133, 11, 5, 65, 18, 9, 7, 8, '2025-12-13 14:10:57', 1, 0),
(134, 11, 17, 35, 7, 3, 4, 10, '2025-12-13 14:10:57', 2, 0),
(135, 12, 4, 58, 15, 5, 6, 10, '2025-12-13 14:10:57', 2, 0),
(136, 12, 15, 42, 9, 4, 3, 14, '2025-12-13 14:10:57', 2, 0),
(137, 13, 12, 48, 14, 7, 5, 12, '2025-12-13 14:10:57', 2, 0),
(138, 13, 13, 52, 16, 8, 6, 9, '2025-12-13 14:10:57', 2, 0),
(139, 14, 11, 50, 12, 5, 4, 11, '2025-12-13 14:10:57', 2, 0),
(140, 14, 21, 50, 13, 6, 5, 10, '2025-12-13 14:10:57', 2, 0),
(141, 15, 22, 55, 16, 8, 6, 9, '2025-12-13 14:10:57', 2, 0),
(142, 15, 23, 45, 11, 5, 4, 12, '2025-12-13 14:10:57', 2, 0),
(143, 16, 26, 52, 19, 10, 7, 10, '2025-12-13 14:10:57', 2, 0),
(144, 16, 25, 48, 14, 7, 5, 13, '2025-12-13 14:10:57', 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int NOT NULL,
  `team_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `number` int DEFAULT NULL,
  `position` enum('GK','DEF','MID','FWD') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `team_id`, `name`, `number`, `position`, `created_at`) VALUES
(1, 1, 'Bruno Fernandes', 8, 'MID', '2025-12-13 12:32:54'),
(2, 1, 'Marcus Rashford', 10, 'FWD', '2025-12-13 12:32:54'),
(3, 1, 'Luke Shaw', 23, 'DEF', '2025-12-13 12:32:54'),
(4, 2, 'Mohamed Salah', 11, 'FWD', '2025-12-13 12:32:54'),
(5, 2, 'Virgil van Dijk', 4, 'DEF', '2025-12-13 12:32:54'),
(6, 2, 'Alisson Becker', 1, 'GK', '2025-12-13 12:32:54'),
(7, 3, 'Bukayo Saka', 7, 'FWD', '2025-12-13 12:32:54'),
(8, 3, 'Martin Ødegaard', 8, 'MID', '2025-12-13 12:32:54'),
(9, 3, 'William Saliba', 2, 'DEF', '2025-12-13 12:32:54'),
(10, 6, 'Robert Sanchez', 1, 'GK', '2025-12-13 12:38:45'),
(11, 6, 'Reece James', 24, 'DEF', '2025-12-13 12:38:45'),
(12, 6, 'Levi Colwill', 6, 'DEF', '2025-12-13 12:38:45'),
(13, 6, 'Wesley Fofana', 29, 'DEF', '2025-12-13 12:38:45'),
(14, 6, 'Marc Cucurella', 3, 'DEF', '2025-12-13 12:38:45'),
(15, 6, 'Moises Caicedo', 25, 'MID', '2025-12-13 12:38:45'),
(16, 6, 'Enzo Fernandez', 8, 'MID', '2025-12-13 12:38:45'),
(17, 6, 'Cole Palmer', 10, 'MID', '2025-12-13 12:38:45'),
(18, 6, 'Jamie Gittens', 11, 'FWD', '2025-12-13 12:38:45'),
(19, 6, 'Joao Pedro', 20, 'FWD', '2025-12-13 12:38:45'),
(20, 6, 'Liam Delap', 9, 'FWD', '2025-12-13 12:38:45'),
(21, 6, 'Pedro Neto', 7, 'FWD', '2025-12-13 12:38:45'),
(22, 6, 'Romeo Lavia', 45, 'MID', '2025-12-13 12:38:45'),
(23, 6, 'Malo Gusto', 27, 'DEF', '2025-12-13 12:38:45'),
(24, 6, 'Alejandro Garnacho', 49, 'FWD', '2025-12-13 12:38:45'),
(25, 6, 'Jorrel Hato', 21, 'DEF', '2025-12-13 12:38:45'),
(26, 2, 'Jeremie Frimpong', 30, 'DEF', '2025-12-13 12:38:45'),
(27, 2, 'Ibrahima Konate', 5, 'DEF', '2025-12-13 12:38:45'),
(28, 2, 'Andrew Robertson', 26, 'DEF', '2025-12-13 12:38:45'),
(29, 2, 'Milos Kerkez', 6, 'DEF', '2025-12-13 12:38:45'),
(30, 2, 'Ryan Gravenberch', 38, 'MID', '2025-12-13 12:38:45'),
(31, 2, 'Alexis Mac Allister', 10, 'MID', '2025-12-13 12:38:45'),
(32, 2, 'Dominik Szoboszlai', 8, 'MID', '2025-12-13 12:38:45'),
(33, 2, 'Florian Wirtz', 7, 'FWD', '2025-12-13 12:38:45'),
(34, 2, 'Hugo Ekitke', 22, 'FWD', '2025-12-13 12:38:45'),
(35, 2, 'Alexander Isak', 9, 'FWD', '2025-12-13 12:38:45'),
(36, 2, 'Cody Gakpo', 18, 'FWD', '2025-12-13 12:38:45'),
(37, 2, 'Federico Chiesa', 14, 'FWD', '2025-12-13 12:38:45'),
(38, 2, 'Joe Gomez', 21, 'DEF', '2025-12-13 12:38:45'),
(39, 2, 'Conor Bradley', 12, 'DEF', '2025-12-13 12:38:45'),
(40, 1, 'Senne Lammens', 24, 'GK', '2025-12-13 12:38:45'),
(41, 1, 'Diogo Dalot', 20, 'DEF', '2025-12-13 12:38:45'),
(42, 1, 'Lisandro Martinez', 6, 'DEF', '2025-12-13 12:38:45'),
(43, 1, 'Matthijs de Ligt', 4, 'DEF', '2025-12-13 12:38:45'),
(44, 1, 'Kobbie Mainoo', 37, 'MID', '2025-12-13 12:38:45'),
(45, 1, 'Manuel Ugarte', 25, 'MID', '2025-12-13 12:38:45'),
(46, 1, 'Mattheus Cunha', 10, 'FWD', '2025-12-13 12:38:45'),
(47, 1, 'Bryan Mbeumo', 19, 'FWD', '2025-12-13 12:38:45'),
(48, 1, 'Benjamin Sesko', 30, 'FWD', '2025-12-13 12:38:45'),
(49, 1, 'Joshua Zirkzee', 11, 'FWD', '2025-12-13 12:38:45'),
(50, 1, 'Amad Diallo', 16, 'FWD', '2025-12-13 12:38:45'),
(51, 1, 'Noussair Mazraoui', 3, 'DEF', '2025-12-13 12:38:45'),
(52, 1, 'Leny Yoro', 15, 'DEF', '2025-12-13 12:38:45'),
(53, 1, 'Casemiro', 18, 'MID', '2025-12-13 12:38:45'),
(54, 21, 'Ederson', 13, 'MID', '2025-12-13 12:38:45'),
(55, 7, 'Stefan Ortega', 18, 'GK', '2025-12-13 12:38:45'),
(56, 7, 'Kyle Walker', 2, 'DEF', '2025-12-13 12:38:45'),
(57, 7, 'Ruben Dias', 3, 'DEF', '2025-12-13 12:38:45'),
(58, 7, 'Manuel Akanji', 25, 'DEF', '2025-12-13 12:38:45'),
(59, 7, 'Josko Gvardiol', 24, 'DEF', '2025-12-13 12:38:45'),
(60, 7, 'Nathan Ake', 6, 'DEF', '2025-12-13 12:38:45'),
(61, 7, 'John Stones', 5, 'DEF', '2025-12-13 12:38:45'),
(62, 7, 'Rodri', 16, 'MID', '2025-12-13 12:38:45'),
(63, 7, 'Bernardo Silva', 20, 'MID', '2025-12-13 12:38:45'),
(64, 7, 'Kevin De Bruyne', 17, 'MID', '2025-12-13 12:38:45'),
(65, 7, 'Ilkay Gundogan', 19, 'MID', '2025-12-13 12:38:45'),
(66, 7, 'Mateo Kovacic', 8, 'MID', '2025-12-13 12:38:45'),
(67, 7, 'Matheus Nunes', 27, 'MID', '2025-12-13 12:38:45'),
(68, 7, 'James McAtee', 87, 'MID', '2025-12-13 12:38:45'),
(69, 7, 'Phil Foden', 47, 'FWD', '2025-12-13 12:38:45'),
(70, 7, 'Erling Haaland', 9, 'FWD', '2025-12-13 12:38:45'),
(71, 7, 'Jeremy Doku', 11, 'FWD', '2025-12-13 12:38:45'),
(72, 7, 'Savinho', 26, 'FWD', '2025-12-13 12:38:45'),
(73, 7, 'Jack Grealish', 10, 'FWD', '2025-12-13 12:38:45'),
(74, 7, 'Oscar Bobb', 52, 'FWD', '2025-12-13 12:38:45'),
(75, 3, 'David Raya', 22, 'GK', '2025-12-13 12:38:45'),
(76, 3, 'Neto', 32, 'GK', '2025-12-13 12:38:45'),
(77, 3, 'Ben White', 4, 'DEF', '2025-12-13 12:38:45'),
(78, 3, 'Gabriel Magalhaes', 6, 'DEF', '2025-12-13 12:38:45'),
(79, 3, 'Jurrien Timber', 12, 'DEF', '2025-12-13 12:38:45'),
(80, 3, 'Riccardo Calafiori', 33, 'DEF', '2025-12-13 12:38:45'),
(81, 3, 'Oleksandr Zinchenko', 17, 'DEF', '2025-12-13 12:38:45'),
(82, 3, 'Declan Rice', 41, 'MID', '2025-12-13 12:38:45'),
(83, 3, 'Thomas Partey', 5, 'MID', '2025-12-13 12:38:45'),
(84, 3, 'Martin Odegaard', 8, 'MID', '2025-12-13 12:38:45'),
(85, 3, 'Mikel Merino', 23, 'MID', '2025-12-13 12:38:45'),
(86, 3, 'Jorginho', 20, 'MID', '2025-12-13 12:38:45'),
(87, 3, 'Kai Havertz', 29, 'FWD', '2025-12-13 12:38:45'),
(88, 3, 'Gabriel Martinelli', 11, 'FWD', '2025-12-13 12:38:45'),
(89, 3, 'Leandro Trossard', 19, 'FWD', '2025-12-13 12:38:45'),
(90, 3, 'Raheem Sterling', 30, 'FWD', '2025-12-13 12:38:45'),
(91, 3, 'Gabriel Jesus', 9, 'FWD', '2025-12-13 12:38:45'),
(92, 8, 'Guglielmo Vicario', 1, 'GK', '2025-12-13 12:38:45'),
(93, 8, 'Fraser Forster', 20, 'GK', '2025-12-13 12:38:45'),
(94, 8, 'Pedro Porro', 23, 'DEF', '2025-12-13 12:38:45'),
(95, 8, 'Cristian Romero', 17, 'DEF', '2025-12-13 12:38:45'),
(96, 8, 'Micky van de Ven', 37, 'DEF', '2025-12-13 12:38:45'),
(97, 8, 'Destiny Udogie', 13, 'DEF', '2025-12-13 12:38:45'),
(98, 8, 'Radu Dragusin', 6, 'DEF', '2025-12-13 12:38:45'),
(99, 8, 'Yves Bissouma', 8, 'MID', '2025-12-13 12:38:45'),
(100, 8, 'James Maddison', 10, 'MID', '2025-12-13 12:38:45'),
(101, 8, 'Pape Matar Sarr', 29, 'MID', '2025-12-13 12:38:45'),
(102, 8, 'Rodrigo Bentancur', 30, 'MID', '2025-12-13 12:38:45'),
(103, 8, 'Lucas Bergvall', 14, 'MID', '2025-12-13 12:38:45'),
(104, 8, 'Archie Gray', 14, 'MID', '2025-12-13 12:38:45'),
(105, 8, 'Son Heung-min', 7, 'FWD', '2025-12-13 12:38:45'),
(106, 8, 'Dominic Solanke', 19, 'FWD', '2025-12-13 12:38:45'),
(107, 8, 'Brennan Johnson', 22, 'FWD', '2025-12-13 12:38:45'),
(108, 8, 'Dejan Kulusevski', 21, 'FWD', '2025-12-13 12:38:45'),
(109, 8, 'Timo Werner', 16, 'FWD', '2025-12-13 12:38:45'),
(110, 8, 'Richarlison', 9, 'FWD', '2025-12-13 12:38:45'),
(111, 5, 'Marc-Andre ter Stegen', 1, 'GK', '2025-12-13 12:38:45'),
(112, 5, 'Inaki Pena', 13, 'GK', '2025-12-13 12:38:45'),
(113, 5, 'Wojciech Szczesny', 25, 'GK', '2025-12-13 12:38:45'),
(114, 5, 'Jules Kounde', 23, 'DEF', '2025-12-13 12:38:45'),
(115, 5, 'Pau Cubarsi', 2, 'DEF', '2025-12-13 12:38:45'),
(116, 5, 'Inigo Martinez', 5, 'DEF', '2025-12-13 12:38:45'),
(117, 5, 'Alejandro Balde', 3, 'DEF', '2025-12-13 12:38:45'),
(118, 5, 'Andreas Christensen', 15, 'DEF', '2025-12-13 12:38:45'),
(119, 5, 'Ronald Araujo', 4, 'DEF', '2025-12-13 12:38:45'),
(120, 5, 'Marc Casado', 17, 'MID', '2025-12-13 12:38:45'),
(121, 5, 'Pedri', 8, 'MID', '2025-12-13 12:38:45'),
(122, 5, 'Dani Olmo', 20, 'MID', '2025-12-13 12:38:45'),
(123, 5, 'Gavi', 6, 'MID', '2025-12-13 12:38:45'),
(124, 5, 'Frenkie de Jong', 21, 'MID', '2025-12-13 12:38:45'),
(125, 5, 'Fermin Lopez', 16, 'MID', '2025-12-13 12:38:45'),
(126, 5, 'Lamine Yamal', 19, 'FWD', '2025-12-13 12:38:45'),
(127, 5, 'Robert Lewandowski', 9, 'FWD', '2025-12-13 12:38:45'),
(128, 5, 'Raphinha', 11, 'FWD', '2025-12-13 12:38:45'),
(129, 5, 'Ferran Torres', 7, 'FWD', '2025-12-13 12:38:45'),
(130, 5, 'Ansu Fati', 10, 'FWD', '2025-12-13 12:38:45'),
(131, 4, 'Thibaut Courtois', 1, 'GK', '2025-12-13 12:38:45'),
(132, 4, 'Andriy Lunin', 13, 'GK', '2025-12-13 12:38:45'),
(133, 4, 'Dani Carvajal', 2, 'DEF', '2025-12-13 12:38:45'),
(134, 4, 'Eder Militao', 3, 'DEF', '2025-12-13 12:38:45'),
(135, 4, 'Antonio Rudiger', 22, 'DEF', '2025-12-13 12:38:45'),
(136, 4, 'Ferland Mendy', 23, 'DEF', '2025-12-13 12:38:45'),
(137, 4, 'David Alaba', 4, 'DEF', '2025-12-13 12:38:45'),
(138, 4, 'Lucas Vazquez', 17, 'DEF', '2025-12-13 12:38:45'),
(139, 4, 'Fran Garcia', 20, 'DEF', '2025-12-13 12:38:45'),
(140, 4, 'Aurelien Tchouameni', 14, 'MID', '2025-12-13 12:38:45'),
(141, 4, 'Federico Valverde', 8, 'MID', '2025-12-13 12:38:45'),
(142, 4, 'Jude Bellingham', 5, 'MID', '2025-12-13 12:38:45'),
(143, 4, 'Eduardo Camavinga', 6, 'MID', '2025-12-13 12:38:45'),
(144, 4, 'Luka Modric', 10, 'MID', '2025-12-13 12:38:45'),
(145, 4, 'Arda Guler', 15, 'MID', '2025-12-13 12:38:45'),
(146, 4, 'Kylian Mbappe', 9, 'FWD', '2025-12-13 12:38:45'),
(147, 4, 'Vinicius Junior', 7, 'FWD', '2025-12-13 12:38:45'),
(148, 4, 'Rodrygo', 11, 'FWD', '2025-12-13 12:38:45'),
(149, 4, 'Endrick', 16, 'FWD', '2025-12-13 12:38:45'),
(150, 4, 'Brahim Diaz', 21, 'FWD', '2025-12-13 12:38:45'),
(151, 15, 'Jan Oblak', 13, 'GK', '2025-12-13 12:38:45'),
(152, 15, 'Juan Musso', 1, 'GK', '2025-12-13 12:38:45'),
(153, 15, 'Nahuel Molina', 16, 'DEF', '2025-12-13 12:38:45'),
(154, 15, 'Robin Le Normand', 24, 'DEF', '2025-12-13 12:38:45'),
(155, 15, 'Jose Maria Gimenez', 2, 'DEF', '2025-12-13 12:38:45'),
(156, 15, 'Reinildo Mandava', 23, 'DEF', '2025-12-13 12:38:45'),
(157, 15, 'Cesar Azpilicueta', 3, 'DEF', '2025-12-13 12:38:45'),
(158, 15, 'Axel Witsel', 20, 'DEF', '2025-12-13 12:38:45'),
(159, 15, 'Koke', 6, 'MID', '2025-12-13 12:38:45'),
(160, 15, 'Conor Gallagher', 4, 'MID', '2025-12-13 12:38:45'),
(161, 15, 'Rodrigo De Paul', 5, 'MID', '2025-12-13 12:38:45'),
(162, 15, 'Pablo Barrios', 8, 'MID', '2025-12-13 12:38:45'),
(163, 15, 'Marcos Llorente', 14, 'MID', '2025-12-13 12:38:45'),
(164, 15, 'Antoine Griezmann', 7, 'FWD', '2025-12-13 12:38:45'),
(165, 15, 'Julian Alvarez', 19, 'FWD', '2025-12-13 12:38:45'),
(166, 15, 'Alexander Sorloth', 9, 'FWD', '2025-12-13 12:38:45'),
(167, 15, 'Angel Correa', 10, 'FWD', '2025-12-13 12:38:45'),
(168, 15, 'Samuel Lino', 12, 'MID', '2025-12-13 12:38:45'),
(169, 17, 'Rui Silva', 1, 'GK', '2025-12-13 12:38:45'),
(170, 17, 'Adrian', 13, 'GK', '2025-12-13 12:38:45'),
(171, 17, 'Hector Bellerin', 2, 'DEF', '2025-12-13 12:38:45'),
(172, 17, 'Marc Bartra', 5, 'DEF', '2025-12-13 12:38:45'),
(173, 17, 'Diego Llorente', 3, 'DEF', '2025-12-13 12:38:45'),
(174, 17, 'Romain Perraud', 15, 'DEF', '2025-12-13 12:38:45'),
(175, 17, 'Ricardo Rodriguez', 12, 'DEF', '2025-12-13 12:38:45'),
(176, 17, 'Marc Roca', 21, 'MID', '2025-12-13 12:38:45'),
(177, 17, 'Johnny Cardoso', 4, 'MID', '2025-12-13 12:38:45'),
(178, 17, 'Pablo Fornals', 18, 'MID', '2025-12-13 12:38:45'),
(179, 17, 'Giovani Lo Celso', 20, 'MID', '2025-12-13 12:38:45'),
(180, 17, 'Isco', 22, 'MID', '2025-12-13 12:38:45'),
(181, 17, 'William Carvalho', 14, 'MID', '2025-12-13 12:38:45'),
(182, 17, 'Abde Ezzalzouli', 10, 'FWD', '2025-12-13 12:38:45'),
(183, 17, 'Vitor Roque', 8, 'FWD', '2025-12-13 12:38:45'),
(184, 17, 'Cedric Bakambu', 11, 'FWD', '2025-12-13 12:38:45'),
(185, 17, 'Chimy Avila', 9, 'FWD', '2025-12-13 12:38:45'),
(186, 18, 'Diego Conde', 13, 'GK', '2025-12-13 12:38:45'),
(187, 18, 'Luiz Junior', 1, 'GK', '2025-12-13 12:38:45'),
(188, 18, 'Kiko Femenia', 17, 'DEF', '2025-12-13 12:38:45'),
(189, 18, 'Raul Albiol', 3, 'DEF', '2025-12-13 12:38:45'),
(190, 18, 'Eric Bailly', 4, 'DEF', '2025-12-13 12:38:45'),
(191, 18, 'Sergi Cardona', 12, 'DEF', '2025-12-13 12:38:45'),
(192, 18, 'Logan Costa', 2, 'DEF', '2025-12-13 12:38:45'),
(193, 18, 'Juan Foyth', 8, 'DEF', '2025-12-13 12:38:45'),
(194, 18, 'Dani Parejo', 10, 'MID', '2025-12-13 12:38:45'),
(195, 18, 'Santi Comesana', 19, 'MID', '2025-12-13 12:38:45'),
(196, 18, 'Alex Baena', 16, 'MID', '2025-12-13 12:38:45'),
(197, 18, 'Pape Gueye', 18, 'MID', '2025-12-13 12:38:45'),
(198, 18, 'Yeremy Pino', 21, 'FWD', '2025-12-13 12:38:45'),
(199, 18, 'Ayoze Perez', 22, 'FWD', '2025-12-13 12:38:45'),
(200, 18, 'Thierno Barry', 15, 'FWD', '2025-12-13 12:38:45'),
(201, 18, 'Gerard Moreno', 7, 'FWD', '2025-12-13 12:38:45'),
(202, 18, 'Nicolas Pepe', 19, 'FWD', '2025-12-13 12:38:45'),
(203, 16, 'Orjan Nyland', 13, 'GK', '2025-12-13 12:38:45'),
(204, 16, 'Jesus Navas', 16, 'DEF', '2025-12-13 12:38:45'),
(205, 16, 'Loic Bade', 22, 'DEF', '2025-12-13 12:38:45'),
(206, 16, 'Nemanja Gudelj', 6, 'DEF', '2025-12-13 12:38:45'),
(207, 16, 'Adria Pedrosa', 3, 'DEF', '2025-12-13 12:38:45'),
(208, 16, 'Gonzalo Montiel', 4, 'DEF', '2025-12-13 12:38:45'),
(209, 16, 'Valentin Barco', 19, 'DEF', '2025-12-13 12:38:45'),
(210, 16, 'Saul Niguez', 17, 'MID', '2025-12-13 12:38:45'),
(211, 16, 'Lucien Agoume', 42, 'MID', '2025-12-13 12:38:45'),
(212, 16, 'Djibril Sow', 20, 'MID', '2025-12-13 12:38:45'),
(213, 16, 'Albert Sambi Lokonga', 12, 'MID', '2025-12-13 12:38:45'),
(214, 16, 'Dodi Lukebakio', 11, 'FWD', '2025-12-13 12:38:45'),
(215, 16, 'Isaac Romero', 7, 'FWD', '2025-12-13 12:38:45'),
(216, 16, 'Chidera Ejuke', 21, 'FWD', '2025-12-13 12:38:45'),
(217, 16, 'Kelechi Iheanacho', 9, 'FWD', '2025-12-13 12:38:45'),
(218, 16, 'Suso', 10, 'MID', '2025-12-13 12:38:45'),
(219, 12, 'Mike Maignan', 16, 'GK', '2025-12-13 12:38:45'),
(220, 12, 'Marco Sportiello', 57, 'GK', '2025-12-13 12:38:45'),
(221, 12, 'Emerson Royal', 22, 'DEF', '2025-12-13 12:38:45'),
(222, 12, 'Fikayo Tomori', 23, 'DEF', '2025-12-13 12:38:45'),
(223, 12, 'Strahinja Pavlovic', 31, 'DEF', '2025-12-13 12:38:45'),
(224, 12, 'Theo Hernandez', 19, 'DEF', '2025-12-13 12:38:45'),
(225, 12, 'Matteo Gabbia', 46, 'DEF', '2025-12-13 12:38:45'),
(226, 12, 'Davide Calabria', 2, 'DEF', '2025-12-13 12:38:45'),
(227, 12, 'Youssouf Fofana', 29, 'MID', '2025-12-13 12:38:45'),
(228, 12, 'Tijani Reijnders', 14, 'MID', '2025-12-13 12:38:45'),
(229, 12, 'Ruben Loftus-Cheek', 8, 'MID', '2025-12-13 12:38:45'),
(230, 12, 'Yunus Musah', 80, 'MID', '2025-12-13 12:38:45'),
(231, 12, 'Christian Pulisic', 11, 'FWD', '2025-12-13 12:38:45'),
(232, 12, 'Alvaro Morata', 7, 'FWD', '2025-12-13 12:38:45'),
(233, 12, 'Rafael Leao', 10, 'FWD', '2025-12-13 12:38:45'),
(234, 12, 'Tammy Abraham', 90, 'FWD', '2025-12-13 12:38:45'),
(235, 12, 'Samuel Chukwueze', 21, 'FWD', '2025-12-13 12:38:45'),
(236, 12, 'Noah Okafor', 17, 'FWD', '2025-12-13 12:38:45'),
(237, 13, 'Yann Sommer', 1, 'GK', '2025-12-13 12:38:45'),
(238, 13, 'Josep Martinez', 13, 'GK', '2025-12-13 12:38:45'),
(239, 13, 'Benjamin Pavard', 28, 'DEF', '2025-12-13 12:38:45'),
(240, 13, 'Francesco Acerbi', 15, 'DEF', '2025-12-13 12:38:45'),
(241, 13, 'Alessandro Bastoni', 95, 'DEF', '2025-12-13 12:38:45'),
(242, 13, 'Stefan de Vrij', 6, 'DEF', '2025-12-13 12:38:45'),
(243, 13, 'Yann Bisseck', 31, 'DEF', '2025-12-13 12:38:45'),
(244, 13, 'Denzel Dumfries', 2, 'DEF', '2025-12-13 12:38:45'),
(245, 13, 'Federico Dimarco', 32, 'DEF', '2025-12-13 12:38:45'),
(246, 13, 'Matteo Darmian', 36, 'DEF', '2025-12-13 12:38:45'),
(247, 13, 'Nicolo Barella', 23, 'MID', '2025-12-13 12:38:45'),
(248, 13, 'Hakan Calhanoglu', 20, 'MID', '2025-12-13 12:38:45'),
(249, 13, 'Henrikh Mkhitaryan', 22, 'MID', '2025-12-13 12:38:45'),
(250, 13, 'Piotr Zielinski', 7, 'MID', '2025-12-13 12:38:45'),
(251, 13, 'Davide Frattesi', 16, 'MID', '2025-12-13 12:38:45'),
(252, 13, 'Marcus Thuram', 9, 'FWD', '2025-12-13 12:38:45'),
(253, 13, 'Lautaro Martinez', 10, 'FWD', '2025-12-13 12:38:45'),
(254, 13, 'Mehdi Taremi', 99, 'FWD', '2025-12-13 12:38:45'),
(255, 13, 'Marko Arnautovic', 8, 'FWD', '2025-12-13 12:38:45'),
(256, 11, 'Michele Di Gregorio', 29, 'GK', '2025-12-13 12:38:45'),
(257, 11, 'Mattia Perin', 1, 'GK', '2025-12-13 12:38:45'),
(258, 11, 'Nicolo Savona', 37, 'DEF', '2025-12-13 12:38:45'),
(259, 11, 'Federico Gatti', 4, 'DEF', '2025-12-13 12:38:45'),
(260, 11, 'Bremer', 3, 'DEF', '2025-12-13 12:38:45'),
(261, 11, 'Andrea Cambiaso', 27, 'DEF', '2025-12-13 12:38:45'),
(262, 11, 'Pierre Kalulu', 15, 'DEF', '2025-12-13 12:38:45'),
(263, 11, 'Danilo', 6, 'DEF', '2025-12-13 12:38:45'),
(264, 11, 'Juan Cabal', 32, 'DEF', '2025-12-13 12:38:45'),
(265, 11, 'Manuel Locatelli', 5, 'MID', '2025-12-13 12:38:45'),
(266, 11, 'Khephren Thuram', 19, 'MID', '2025-12-13 12:38:45'),
(267, 11, 'Teun Koopmeiners', 8, 'MID', '2025-12-13 12:38:45'),
(268, 11, 'Douglas Luiz', 26, 'MID', '2025-12-13 12:38:45'),
(269, 11, 'Weston McKennie', 16, 'MID', '2025-12-13 12:38:45'),
(270, 11, 'Kenan Yildiz', 10, 'FWD', '2025-12-13 12:38:45'),
(271, 11, 'Dusan Vlahovic', 9, 'FWD', '2025-12-13 12:38:45'),
(272, 11, 'Francisco Conceicao', 7, 'FWD', '2025-12-13 12:38:45'),
(273, 11, 'Nico Gonzalez', 11, 'FWD', '2025-12-13 12:38:45'),
(274, 11, 'Timothy Weah', 22, 'FWD', '2025-12-13 12:38:45'),
(275, 21, 'Marco Carnesecchi', 29, 'GK', '2025-12-13 12:38:45'),
(276, 21, 'Rui Patricio', 28, 'GK', '2025-12-13 12:38:45'),
(277, 21, 'Berat Djimsiti', 19, 'DEF', '2025-12-13 12:38:45'),
(278, 21, 'Isak Hien', 4, 'DEF', '2025-12-13 12:38:45'),
(279, 21, 'Sead Kolasinac', 23, 'DEF', '2025-12-13 12:38:45'),
(280, 21, 'Raoul Bellanova', 16, 'DEF', '2025-12-13 12:38:45'),
(281, 21, 'Davide Zappacosta', 77, 'DEF', '2025-12-13 12:38:45'),
(282, 21, 'Ben Godfrey', 5, 'DEF', '2025-12-13 12:38:45'),
(283, 21, 'Marten de Roon', 15, 'MID', '2025-12-13 12:38:45'),
(284, 21, 'Mario Pasalic', 8, 'MID', '2025-12-13 12:38:45'),
(285, 21, 'Lazar Samardzic', 24, 'MID', '2025-12-13 12:38:45'),
(286, 21, 'Matteo Ruggeri', 22, 'MID', '2025-12-13 12:38:45'),
(287, 21, 'Charles De Ketelaere', 17, 'FWD', '2025-12-13 12:38:45'),
(288, 21, 'Mateo Retegui', 32, 'FWD', '2025-12-13 12:38:45'),
(289, 21, 'Ademola Lookman', 11, 'FWD', '2025-12-13 12:38:45'),
(290, 21, 'Nicolo Zaniolo', 10, 'FWD', '2025-12-13 12:38:45'),
(291, 20, 'Mile Svilar', 99, 'GK', '2025-12-13 12:38:45'),
(292, 20, 'Zeki Celik', 19, 'DEF', '2025-12-13 12:38:45'),
(293, 20, 'Gianluca Mancini', 23, 'DEF', '2025-12-13 12:38:45'),
(294, 20, 'Evan Ndicka', 5, 'DEF', '2025-12-13 12:38:45'),
(295, 20, 'Angelino', 3, 'DEF', '2025-12-13 12:38:45'),
(296, 20, 'Mats Hummels', 15, 'DEF', '2025-12-13 12:38:45'),
(297, 20, 'Mario Hermoso', 22, 'DEF', '2025-12-13 12:38:45'),
(298, 20, 'Bryan Cristante', 4, 'MID', '2025-12-13 12:38:45'),
(299, 20, 'Manu Kone', 17, 'MID', '2025-12-13 12:38:45'),
(300, 20, 'Lorenzo Pellegrini', 7, 'MID', '2025-12-13 12:38:45'),
(301, 20, 'Enzo Le Fee', 28, 'MID', '2025-12-13 12:38:45'),
(302, 20, 'Leandro Paredes', 16, 'MID', '2025-12-13 12:38:45'),
(303, 20, 'Matias Soule', 18, 'FWD', '2025-12-13 12:38:45'),
(304, 20, 'Artem Dovbyk', 11, 'FWD', '2025-12-13 12:38:45'),
(305, 20, 'Paulo Dybala', 21, 'FWD', '2025-12-13 12:38:45'),
(306, 20, 'Stephan El Shaarawy', 92, 'FWD', '2025-12-13 12:38:45'),
(307, 20, 'Alexis Saelemaekers', 56, 'FWD', '2025-12-13 12:38:45'),
(308, 19, 'Alex Meret', 1, 'GK', '2025-12-13 12:38:45'),
(309, 19, 'Giovani Di Lorenzo', 22, 'DEF', '2025-12-13 12:38:45'),
(310, 19, 'Amir Rrahmani', 13, 'DEF', '2025-12-13 12:38:45'),
(311, 19, 'Alessandro Buongiorno', 4, 'DEF', '2025-12-13 12:38:45'),
(312, 19, 'Mathias Olivera', 17, 'DEF', '2025-12-13 12:38:45'),
(313, 19, 'Leonardo Spinazzola', 37, 'DEF', '2025-12-13 12:38:45'),
(314, 19, 'Andre-Frank Zambo Anguissa', 99, 'MID', '2025-12-13 12:38:45'),
(315, 19, 'Stanislav Lobotka', 68, 'MID', '2025-12-13 12:38:45'),
(316, 19, 'Scott McTominay', 8, 'MID', '2025-12-13 12:38:45'),
(317, 19, 'Billy Gilmour', 6, 'MID', '2025-12-13 12:38:45'),
(318, 19, 'Matteo Politano', 21, 'FWD', '2025-12-13 12:38:45'),
(319, 19, 'Romelu Lukaku', 11, 'FWD', '2025-12-13 12:38:45'),
(320, 19, 'Khvicha Kvaratskhelia', 77, 'FWD', '2025-12-13 12:38:45'),
(321, 19, 'David Neres', 7, 'FWD', '2025-12-13 12:38:45'),
(322, 19, 'Giacomo Raspadori', 81, 'FWD', '2025-12-13 12:38:45'),
(323, 9, 'Manuel Neuer', 1, 'GK', '2025-12-13 12:38:45'),
(324, 9, 'Sven Ulreich', 26, 'GK', '2025-12-13 12:38:45'),
(325, 9, 'Raphael Guerreiro', 22, 'DEF', '2025-12-13 12:38:45'),
(326, 9, 'Dayot Upamecano', 2, 'DEF', '2025-12-13 12:38:45'),
(327, 9, 'Kim Min-jae', 3, 'DEF', '2025-12-13 12:38:45'),
(328, 9, 'Alphonso Davies', 19, 'DEF', '2025-12-13 12:38:45'),
(329, 9, 'Hiroki Ito', 21, 'DEF', '2025-12-13 12:38:45'),
(330, 9, 'Sacha Boey', 23, 'DEF', '2025-12-13 12:38:45'),
(331, 9, 'Joshua Kimmich', 6, 'MID', '2025-12-13 12:38:45'),
(332, 9, 'Aleksandar Pavlovic', 45, 'MID', '2025-12-13 12:38:45'),
(333, 9, 'Joao Palhinha', 16, 'MID', '2025-12-13 12:38:45'),
(334, 9, 'Konrad Laimer', 27, 'MID', '2025-12-13 12:38:45'),
(335, 9, 'Leon Goretzka', 8, 'MID', '2025-12-13 12:38:45'),
(336, 9, 'Michael Olise', 17, 'FWD', '2025-12-13 12:38:45'),
(337, 9, 'Jamal Musiala', 42, 'MID', '2025-12-13 12:38:45'),
(338, 9, 'Serge Gnabry', 7, 'FWD', '2025-12-13 12:38:45'),
(339, 9, 'Harry Kane', 9, 'FWD', '2025-12-13 12:38:45'),
(340, 9, 'Leroy Sane', 10, 'FWD', '2025-12-13 12:38:45'),
(341, 9, 'Thomas Muller', 25, 'FWD', '2025-12-13 12:38:45'),
(342, 9, 'Kingsley Coman', 11, 'FWD', '2025-12-13 12:38:45'),
(343, 10, 'Gregor Kobel', 1, 'GK', '2025-12-13 12:38:45'),
(344, 10, 'Yan Couto', 2, 'DEF', '2025-12-13 12:38:45'),
(345, 10, 'Waldemar Anton', 3, 'DEF', '2025-12-13 12:38:45'),
(346, 10, 'Nico Schlotterbeck', 4, 'DEF', '2025-12-13 12:38:45'),
(347, 10, 'Julian Ryerson', 26, 'DEF', '2025-12-13 12:38:45'),
(348, 10, 'Niklas Sule', 25, 'DEF', '2025-12-13 12:38:45'),
(349, 10, 'Emre Can', 23, 'MID', '2025-12-13 12:38:45'),
(350, 10, 'Pascal Gross', 13, 'MID', '2025-12-13 12:38:45'),
(351, 10, 'Marcel Sabitzer', 20, 'MID', '2025-12-13 12:38:45'),
(352, 10, 'Felix Nmecha', 8, 'MID', '2025-12-13 12:38:45'),
(353, 10, 'Julian Brandt', 10, 'MID', '2025-12-13 12:38:45'),
(354, 10, 'Serhou Guirassy', 9, 'FWD', '2025-12-13 12:38:45'),
(355, 10, 'Karim Adeyemi', 27, 'FWD', '2025-12-13 12:38:45'),
(356, 10, 'Donyell Malen', 21, 'FWD', '2025-12-13 12:38:45'),
(357, 10, 'Maximilian Beier', 14, 'FWD', '2025-12-13 12:38:45'),
(358, 23, 'Lukas Hradecky', 1, 'GK', '2025-12-13 12:38:45'),
(359, 23, 'Matej Kovar', 17, 'GK', '2025-12-13 12:38:45'),
(360, 23, 'Edmond Tapsoba', 12, 'DEF', '2025-12-13 12:38:45'),
(361, 23, 'Jonathan Tah', 4, 'DEF', '2025-12-13 12:38:45'),
(362, 23, 'Piero Hincapie', 3, 'DEF', '2025-12-13 12:38:45'),
(363, 23, 'Alejandro Grimaldo', 20, 'DEF', '2025-12-13 12:38:45'),
(364, 23, 'Nordi Mukiele', 23, 'DEF', '2025-12-13 12:38:45'),
(365, 23, 'Granit Xhaka', 34, 'MID', '2025-12-13 12:38:45'),
(366, 23, 'Aleix Garcia', 24, 'MID', '2025-12-13 12:38:45'),
(367, 23, 'Robert Andrich', 8, 'MID', '2025-12-13 12:38:45'),
(368, 23, 'Exequiel Palacios', 25, 'MID', '2025-12-13 12:38:45'),
(369, 23, 'Martin Terrier', 11, 'FWD', '2025-12-13 12:38:45'),
(370, 23, 'Victor Boniface', 22, 'FWD', '2025-12-13 12:38:45'),
(371, 23, 'Amine Adli', 21, 'FWD', '2025-12-13 12:38:45'),
(372, 23, 'Patrik Schick', 14, 'FWD', '2025-12-13 12:38:45'),
(373, 23, 'Jonas Hofmann', 7, 'MID', '2025-12-13 12:38:45'),
(374, 22, 'Peter Gulacsi', 1, 'GK', '2025-12-13 12:38:45'),
(375, 22, 'Lutsharel Geertruida', 3, 'DEF', '2025-12-13 12:38:45'),
(376, 22, 'Willi Orban', 4, 'DEF', '2025-12-13 12:38:45'),
(377, 22, 'Castello Lukeba', 23, 'DEF', '2025-12-13 12:38:45'),
(378, 22, 'David Raum', 22, 'DEF', '2025-12-13 12:38:45'),
(379, 22, 'Benjamin Henrichs', 39, 'DEF', '2025-12-13 12:38:45'),
(380, 22, 'Amadou Haidara', 8, 'MID', '2025-12-13 12:38:45'),
(381, 22, 'Nicolas Seiwald', 13, 'MID', '2025-12-13 12:38:45'),
(382, 22, 'Xavi Simons', 10, 'MID', '2025-12-13 12:38:45'),
(383, 22, 'Christoph Baumgartner', 14, 'MID', '2025-12-13 12:38:45'),
(384, 22, 'Arthur Vermeeren', 18, 'MID', '2025-12-13 12:38:45'),
(385, 22, 'Kevin Kampl', 44, 'MID', '2025-12-13 12:38:45'),
(386, 22, 'Lois Openda', 11, 'FWD', '2025-12-13 12:38:45'),
(387, 22, 'Antonio Nusa', 7, 'FWD', '2025-12-13 12:38:45'),
(388, 22, 'Yussuf Poulsen', 9, 'FWD', '2025-12-13 12:38:45'),
(389, 22, 'Andre Silva', 19, 'FWD', '2025-12-13 12:38:45'),
(390, 14, 'Gianluigi Donnarumma', 1, 'GK', '2025-12-13 12:38:45'),
(391, 14, 'Matvey Safonov', 39, 'GK', '2025-12-13 12:38:45'),
(392, 14, 'Achraf Hakimi', 2, 'DEF', '2025-12-13 12:38:45'),
(393, 14, 'Marquinhos', 5, 'DEF', '2025-12-13 12:38:45'),
(394, 14, 'Willian Pacho', 51, 'DEF', '2025-12-13 12:38:45'),
(395, 14, 'Nuno Mendes', 25, 'DEF', '2025-12-13 12:38:45'),
(396, 14, 'Lucas Beraldo', 35, 'DEF', '2025-12-13 12:38:45'),
(397, 14, 'Lucas Hernandez', 21, 'DEF', '2025-12-13 12:38:45'),
(398, 14, 'Vitinha', 17, 'MID', '2025-12-13 12:38:45'),
(399, 14, 'Joao Neves', 87, 'MID', '2025-12-13 12:38:45'),
(400, 14, 'Warren Zaire-Emery', 33, 'MID', '2025-12-13 12:38:45'),
(401, 14, 'Fabian Ruiz', 8, 'MID', '2025-12-13 12:38:45'),
(402, 14, 'Ousmane Dembele', 10, 'FWD', '2025-12-13 12:38:45'),
(403, 14, 'Marco Asensio', 11, 'FWD', '2025-12-13 12:38:45'),
(404, 14, 'Bradley Barcola', 29, 'FWD', '2025-12-13 12:38:45'),
(405, 14, 'Randal Kolo Muani', 23, 'FWD', '2025-12-13 12:38:45'),
(406, 14, 'Lee Kang-in', 19, 'MID', '2025-12-13 12:38:45'),
(407, 14, 'Desire Doue', 14, 'FWD', '2025-12-13 12:38:45'),
(408, 24, 'Geronimo Rulli', 1, 'GK', '2025-12-13 12:38:45'),
(409, 24, 'Jeffrey de Lange', 12, 'GK', '2025-12-13 12:38:45'),
(410, 24, 'Michael Murillo', 62, 'DEF', '2025-12-13 12:38:45'),
(411, 24, 'Leonardo Balerdi', 5, 'DEF', '2025-12-13 12:38:45'),
(412, 24, 'Derek Cornelius', 13, 'DEF', '2025-12-13 12:38:45'),
(413, 24, 'Quentin Merlin', 3, 'DEF', '2025-12-13 12:38:45'),
(414, 24, 'Lilian Brassier', 20, 'DEF', '2025-12-13 12:38:45'),
(415, 24, 'Pol Lirola', 29, 'DEF', '2025-12-13 12:38:45'),
(416, 24, 'Pierre-Emile Hojbjerg', 23, 'MID', '2025-12-13 12:38:45'),
(417, 24, 'Adrien Rabiot', 25, 'MID', '2025-12-13 12:38:45'),
(418, 24, 'Geoffrey Kondogbia', 19, 'MID', '2025-12-13 12:38:45'),
(419, 24, 'Ismael Kone', 51, 'MID', '2025-12-13 12:38:45'),
(420, 24, 'Mason Greenwood', 10, 'FWD', '2025-12-13 12:38:45'),
(421, 24, 'Elye Wahi', 9, 'FWD', '2025-12-13 12:38:45'),
(422, 24, 'Luis Henrique', 44, 'FWD', '2025-12-13 12:38:45'),
(423, 24, 'Neal Maupay', 32, 'FWD', '2025-12-13 12:38:45'),
(424, 24, 'Amine Harit', 11, 'MID', '2025-12-13 12:38:45'),
(425, 24, 'Valentin Carboni', 7, 'MID', '2025-12-13 12:38:45'),
(426, 26, 'Djordje Petrovic', 1, 'GK', '2025-12-13 12:38:45'),
(427, 26, 'Guela Doue', 2, 'DEF', '2025-12-13 12:38:45'),
(428, 26, 'Saidou Sow', 4, 'DEF', '2025-12-13 12:38:45'),
(429, 26, 'Abakar Sylla', 5, 'DEF', '2025-12-13 12:38:45'),
(430, 26, 'Caleb Wiley', 3, 'DEF', '2025-12-13 12:38:45'),
(431, 26, 'Marvin Senaya', 28, 'DEF', '2025-12-13 12:38:45'),
(432, 26, 'Andrey Santos', 8, 'MID', '2025-12-13 12:38:45'),
(433, 26, 'Ismael Doukoure', 29, 'MID', '2025-12-13 12:38:45'),
(434, 26, 'Habib Diarra', 19, 'MID', '2025-12-13 12:38:45'),
(435, 26, 'Felix Lemarechal', 6, 'MID', '2025-12-13 12:38:45'),
(436, 26, 'Dilane Bakwa', 11, 'FWD', '2025-12-13 12:38:45'),
(437, 26, 'Emanuel Emegha', 10, 'FWD', '2025-12-13 12:38:45'),
(438, 26, 'Sebastian Nanasi', 15, 'FWD', '2025-12-13 12:38:45'),
(439, 26, 'Sekou Mara', 14, 'FWD', '2025-12-13 12:38:45'),
(440, 26, 'Diego Moreira', 7, 'FWD', '2025-12-13 12:38:45'),
(441, 26, 'Oscar Perea', 20, 'FWD', '2025-12-13 12:38:45'),
(442, 25, 'Lucas Perri', 1, 'GK', '2025-12-13 12:38:45'),
(443, 25, 'Ainsley Maitland-Niles', 98, 'DEF', '2025-12-13 12:38:45'),
(444, 25, 'Duje Caleta-Car', 55, 'DEF', '2025-12-13 12:38:45'),
(445, 25, 'Moussa Niakhate', 19, 'DEF', '2025-12-13 12:38:45'),
(446, 25, 'Nicolas Tagliafico', 3, 'DEF', '2025-12-13 12:38:45'),
(447, 25, 'Abner', 16, 'DEF', '2025-12-13 12:38:45'),
(448, 25, 'Nemanja Matic', 31, 'MID', '2025-12-13 12:38:45'),
(449, 25, 'Corentin Tolisso', 8, 'MID', '2025-12-13 12:38:45'),
(450, 25, 'Jordan Veretout', 7, 'MID', '2025-12-13 12:38:45'),
(451, 25, 'Maxence Caqueret', 6, 'MID', '2025-12-13 12:38:45'),
(452, 25, 'Tanner Tessmann', 15, 'MID', '2025-12-13 12:38:45'),
(453, 25, 'Rayan Cherki', 18, 'MID', '2025-12-13 12:38:45'),
(454, 25, 'Alexandre Lacazette', 10, 'FWD', '2025-12-13 12:38:45'),
(455, 25, 'Malick Fofana', 11, 'FWD', '2025-12-13 12:38:45'),
(456, 25, 'Georges Mikautadze', 69, 'FWD', '2025-12-13 12:38:45'),
(457, 25, 'Said Benrahma', 17, 'FWD', '2025-12-13 12:38:45'),
(458, 25, 'Wilfried Zaha', 12, 'FWD', '2025-12-13 12:38:45');

-- --------------------------------------------------------

--
-- Table structure for table `reactions`
--

CREATE TABLE `reactions` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `user_id` int NOT NULL,
  `type` enum('like','dislike') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `match_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `match_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 3, 12, 4, 'This was an entertaining game!', '2025-12-13 15:17:35', '2025-12-13 15:17:35'),
(2, 3, 8, 5, 'Cole Palmer against the world! He\'s winning the Balon D\'or.', '2025-12-14 03:44:25', '2025-12-14 03:44:36'),
(3, 3, 11, 5, 'Lewandowski might not be finished. Lamine Yamal wow!! This guy is world class.', '2025-12-14 03:45:24', '2025-12-14 03:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `review_replies`
--

CREATE TABLE `review_replies` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `user_id`, `admin_id`, `message`, `is_admin`, `created_at`) VALUES
(1, 3, NULL, 'Hello', 0, '2025-12-14 03:41:03'),
(2, 3, 4, 'Hello', 1, '2025-12-14 03:41:14'),
(3, 3, 4, 'How may I help you', 1, '2025-12-14 03:41:19'),
(4, 3, NULL, 'Would be nice if we could rate teams alone you know. Rating Real Madrid 1/5 stars😂.', 0, '2025-12-14 03:42:40'),
(5, 3, 4, 'Alright. Don\'t worry. We\'ll be looking into that👌.', 1, '2025-12-14 03:43:06'),
(6, 3, NULL, 'Thanks.', 0, '2025-12-14 03:43:15');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `short_name` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `founded` int DEFAULT NULL,
  `stadium` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `venue_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `competition_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `short_name`, `logo`, `country`, `founded`, `stadium`, `venue_id`, `created_at`, `competition_id`) VALUES
(1, 'Manchester United', 'MUN', 'https://resources.premierleague.com/premierleague/badges/t1.svg', 'England', 1878, 'Old Trafford', 1, '2025-12-13 12:32:54', 1),
(2, 'Liverpool', 'LIV', 'https://resources.premierleague.com/premierleague/badges/t14.svg', 'England', 1892, 'Anfield', 2, '2025-12-13 12:32:54', 1),
(3, 'Arsenal', 'ARS', 'https://resources.premierleague.com/premierleague/badges/t3.svg', 'England', 1886, 'Emirates Stadium', 3, '2025-12-13 12:32:54', 1),
(4, 'Real Madrid', 'RMA', 'https://upload.wikimedia.org/wikipedia/en/5/56/Real_Madrid_CF.svg', 'Spain', 1902, 'Santiago Bernabéu', 4, '2025-12-13 12:32:54', 2),
(5, 'Barcelona', 'BAR', 'https://upload.wikimedia.org/wikipedia/en/4/47/FC_Barcelona_%28crest%29.svg', 'Spain', 1899, 'Camp Nou', 5, '2025-12-13 12:32:54', 2),
(6, 'Chelsea', 'CHE', 'https://resources.premierleague.com/premierleague/badges/t8.svg', 'England', 1905, 'Stamford Bridge', 6, '2025-12-13 12:32:54', 1),
(7, 'Manchester City', 'MCI', 'https://resources.premierleague.com/premierleague/badges/t43.svg', 'England', 1880, 'Etihad Stadium', 7, '2025-12-13 12:32:54', 1),
(8, 'Tottenham Hotspur', 'TOT', 'https://resources.premierleague.com/premierleague/badges/t6.svg', 'England', 1882, 'Tottenham Hotspur Stadium', 8, '2025-12-13 12:32:54', 1),
(9, 'Bayern Munich', 'BAY', 'https://upload.wikimedia.org/wikipedia/commons/1/1b/FC_Bayern_München_logo_%282017%29.svg', 'Germany', 1900, 'Allianz Arena', 9, '2025-12-13 12:32:54', 4),
(10, 'Borussia Dortmund', 'BVB', 'https://upload.wikimedia.org/wikipedia/commons/6/67/Borussia_Dortmund_logo.svg', 'Germany', 1909, 'Signal Iduna Park', 10, '2025-12-13 12:32:54', 4),
(11, 'Juventus', 'JUV', 'assets/images/teams/juventus.png', 'Italy', 1897, 'Allianz Stadium', 11, '2025-12-13 12:32:54', 5),
(12, 'AC Milan', 'ACM', 'https://upload.wikimedia.org/wikipedia/commons/d/d0/Logo_of_AC_Milan.svg', 'Italy', 1899, 'San Siro', 12, '2025-12-13 12:32:54', 5),
(13, 'Inter Milan', 'INT', 'https://upload.wikimedia.org/wikipedia/commons/0/05/FC_Internazionale_Milano_2021.svg', 'Italy', 1908, 'San Siro', 12, '2025-12-13 12:32:54', 5),
(14, 'Paris Saint-Germain', 'PSG', 'https://upload.wikimedia.org/wikipedia/en/a/a7/Paris_Saint-Germain_F.C..svg', 'France', 1970, 'Parc des Princes', 13, '2025-12-13 12:32:54', 6),
(15, 'Atletico Madrid', NULL, 'assets/images/teams/atletico_madrid.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 2),
(16, 'Sevilla', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/3/3b/Sevilla_FC_logo.svg/512px-Sevilla_FC_logo.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 2),
(17, 'Real Betis', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/1/13/Real_betis_logo.svg/512px-Real_betis_logo.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 2),
(18, 'Villarreal', NULL, 'assets/images/teams/villarreal.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 2),
(19, 'Napoli', NULL, 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/SSC_Neapel.svg/512px-SSC_Neapel.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 5),
(20, 'Roma', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/f/f7/AS_Roma_logo_%282017%29.svg/512px-AS_Roma_logo_%282017%29.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 5),
(21, 'Atalanta', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/6/66/AtalantaBC.svg/512px-AtalantaBC.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 5),
(22, 'RB Leipzig', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/0/04/RB_Leipzig_2014_logo.svg/512px-RB_Leipzig_2014_logo.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 4),
(23, 'Bayer Leverkusen', NULL, 'https://upload.wikimedia.org/wikipedia/en/thumb/5/59/Bayer_04_Leverkusen_logo.svg/512px-Bayer_04_Leverkusen_logo.svg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 4),
(24, 'Marseille', NULL, 'assets/images/teams/marseille.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 6),
(25, 'Lyon', NULL, 'assets/images/teams/lyon.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 6),
(26, 'Strasbourg', NULL, 'assets/images/teams/strasbourg.png', NULL, NULL, NULL, NULL, '2025-12-13 12:38:34', 6);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `display_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `role` enum('fan','admin','user') COLLATE utf8mb4_general_ci DEFAULT 'fan',
  `status` enum('active','inactive','banned') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `login_attempts` int DEFAULT '0',
  `last_failed_login` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `display_name`, `avatar`, `bio`, `role`, `status`, `login_attempts`, `last_failed_login`, `last_login`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', NULL, NULL, 'admin', 'active', 0, NULL, NULL, '2025-12-13 12:32:54', '2025-12-13 12:32:54'),
(2, NULL, 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', NULL, NULL, 'user', 'active', 0, NULL, NULL, '2025-12-13 12:32:54', '2025-12-13 12:32:54'),
(3, NULL, 'yelsomsanid@gmail.com', '$2y$10$yga.bjS46svjCmSDZqiBOOC3UVEnI5hS8qlQVoqtKyMMNmwuPhx5.', 'twopiecemadeit', NULL, NULL, 'fan', 'active', 0, NULL, '2025-12-19 15:58:15', '2025-12-13 14:59:30', '2025-12-19 15:58:15'),
(4, NULL, 'smadiba@gmail.com', '$2y$10$6z2IJrX6l5P9ONUCGZSV/.onmr/MYGnxrwNtooxsMUe9Pg/XPrr4a', 'themamba21', NULL, NULL, 'admin', 'active', 0, NULL, '2025-12-19 16:21:47', '2025-12-13 15:56:29', '2025-12-19 16:21:47'),
(5, NULL, 'lidwana@gmail.com', '$2y$10$7X0IRJ09lwC9PB986YbPWe2eNbnN0rhNvFDgUSxHWO7yDQ.roKbw.', 'Liddy', NULL, NULL, 'fan', 'active', 0, NULL, '2025-12-19 15:26:02', '2025-12-19 15:25:45', '2025-12-19 15:26:02'),
(6, NULL, 'atofynn31@gmail.com', '$2y$10$aT8vaWP29p2/Vt3iMuzXk.RUj7oBzhMiDKfLEXrq/sCZts.qwC0ne', 'Ato', NULL, NULL, 'fan', 'active', 0, NULL, '2025-12-19 16:12:54', '2025-12-19 16:12:31', '2025-12-19 16:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `full_name`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, NULL, '2025-12-13 14:59:30', '2025-12-13 14:59:30'),
(2, 4, NULL, NULL, NULL, '2025-12-13 15:56:29', '2025-12-13 15:56:29'),
(3, 5, NULL, NULL, NULL, '2025-12-19 15:25:45', '2025-12-19 15:25:45'),
(4, 6, NULL, NULL, NULL, '2025-12-19 16:12:31', '2025-12-19 16:12:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `selector` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `city`, `country`, `capacity`, `created_at`) VALUES
(1, 'Old Trafford', 'Manchester', 'England', 74310, '2025-12-13 12:32:54'),
(2, 'Anfield', 'Liverpool', 'England', 53394, '2025-12-13 12:32:54'),
(3, 'Emirates Stadium', 'London', 'England', 60704, '2025-12-13 12:32:54'),
(4, 'Santiago Bernabéu', 'Madrid', 'Spain', 81044, '2025-12-13 12:32:54'),
(5, 'Camp Nou', 'Barcelona', 'Spain', 99354, '2025-12-13 12:32:54'),
(6, 'Stamford Bridge', 'London', 'England', 40341, '2025-12-13 12:32:54'),
(7, 'Etihad Stadium', 'Manchester', 'England', 53400, '2025-12-13 12:32:54'),
(8, 'Tottenham Hotspur Stadium', 'London', 'England', 62850, '2025-12-13 12:32:54'),
(9, 'Allianz Arena', 'Munich', 'Germany', 75024, '2025-12-13 12:32:54'),
(10, 'Signal Iduna Park', 'Dortmund', 'Germany', 81365, '2025-12-13 12:32:54'),
(11, 'Allianz Stadium', 'Turin', 'Italy', 41507, '2025-12-13 12:32:54'),
(12, 'San Siro', 'Milan', 'Italy', 75817, '2025-12-13 12:32:54'),
(13, 'Parc des Princes', 'Paris', 'France', 47929, '2025-12-13 12:32:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `home_team` (`home_team`),
  ADD KEY `away_team` (`away_team`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `match_events`
--
ALTER TABLE `match_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `player_id` (`player_id`),
  ADD KEY `related_player_id` (`related_player_id`);

--
-- Indexes for table `match_stats`
--
ALTER TABLE `match_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`token`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_reaction` (`review_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`);

--
-- Indexes for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `fk_team_competition` (`competition_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `match_events`
--
ALTER TABLE `match_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `match_stats`
--
ALTER TABLE `match_stats`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=459;

--
-- AUTO_INCREMENT for table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_replies`
--
ALTER TABLE `review_replies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`home_team`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`away_team`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `match_events`
--
ALTER TABLE `match_events`
  ADD CONSTRAINT `match_events_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_events_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_events_ibfk_3` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_events_ibfk_4` FOREIGN KEY (`related_player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `match_stats`
--
ALTER TABLE `match_stats`
  ADD CONSTRAINT `match_stats_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_stats_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);

--
-- Constraints for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD CONSTRAINT `review_replies_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_messages_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `fk_team_competition` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
