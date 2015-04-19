-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.6.24 - MySQL Community Server (GPL)
-- ОС Сервера:                   Win64
-- HeidiSQL Версия:              9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры базы данных mpk_test
CREATE DATABASE IF NOT EXISTS `mpk_test` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `mpk_test`;


-- Дамп структуры для таблица mpk_test.participants
CREATE TABLE IF NOT EXISTS `participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы mpk_test.participants: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `participants` ENABLE KEYS */;


-- Дамп структуры для таблица mpk_test.participants_teams
CREATE TABLE IF NOT EXISTS `participants_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participant_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `participant_id_team_id` (`participant_id`,`team_id`),
  KEY `pt2t` (`team_id`),
  CONSTRAINT `pt2p` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pt2t` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы mpk_test.participants_teams: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `participants_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `participants_teams` ENABLE KEYS */;


-- Дамп структуры для таблица mpk_test.sports_kinds
CREATE TABLE IF NOT EXISTS `sports_kinds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы mpk_test.sports_kinds: ~2 rows (приблизительно)
/*!40000 ALTER TABLE `sports_kinds` DISABLE KEYS */;
/*!40000 ALTER TABLE `sports_kinds` ENABLE KEYS */;


-- Дамп структуры для таблица mpk_test.teams
CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sports_kind_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `t2sk` (`sports_kind_id`),
  CONSTRAINT `t2sk` FOREIGN KEY (`sports_kind_id`) REFERENCES `sports_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы mpk_test.teams: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
