
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `geo_postcode_areas` (
  `postcode_area` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `center` point DEFAULT NULL,
  `geo` polygon DEFAULT NULL,
  PRIMARY KEY (`postcode_area`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `geo_postcode_outwards` (
  `postcode_outward` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `center` point NOT NULL,
  SPATIAL KEY `geo` (`center`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcodes` (
  `postcode` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,5) unsigned DEFAULT NULL,
  `longitude` decimal(10,5) DEFAULT NULL,
  `postcode_area` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode_outward` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  UNIQUE KEY `postcode` (`postcode`),
  KEY `latitude_longitude` (`latitude`,`longitude`),
  KEY `postcode_area` (`postcode_area`),
  KEY `postcode_outward` (`postcode_outward`),
  CONSTRAINT `postcodes_ibfk_1` FOREIGN KEY (`postcode_area`) REFERENCES `postcode_areas` (`postcode_area`),
  CONSTRAINT `postcodes_ibfk_2` FOREIGN KEY (`postcode_outward`) REFERENCES `postcode_outwards` (`postcode_outward`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcode_areas` (
  `postcode_area` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `population` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`postcode_area`),
  KEY `region` (`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcode_county` (
  `county` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`county`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcode_county_town` (
  `county` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `town` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  UNIQUE KEY `county_town` (`county`,`town`),
  KEY `town` (`town`),
  CONSTRAINT `postcode_county_town_ibfk_1` FOREIGN KEY (`county`) REFERENCES `postcode_county` (`county`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcode_outwards` (
  `postcode_outward` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode_area` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `outward_part` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,5) DEFAULT NULL,
  `longitude` decimal(10,5) DEFAULT NULL,
  `town` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `townUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_string` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `geo` point DEFAULT NULL,
  PRIMARY KEY (`postcode_outward`),
  KEY `town` (`town`(191)),
  KEY `postcode_area` (`postcode_area`),
  CONSTRAINT `postcode_outwards_ibfk_2` FOREIGN KEY (`postcode_area`) REFERENCES `postcode_areas` (`postcode_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `postcode_towns` (
  `postcode_outward` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode_area` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `outward_part` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eastings` int(6) unsigned DEFAULT NULL,
  `northings` int(6) unsigned DEFAULT NULL,
  `latitude` decimal(10,5) DEFAULT NULL,
  `longitude` decimal(10,5) DEFAULT NULL,
  `town` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `townUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regionUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_string` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`postcode_outward`),
  KEY `town` (`town`(191)),
  KEY `postcode_area` (`postcode_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
