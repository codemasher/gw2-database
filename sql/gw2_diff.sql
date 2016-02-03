CREATE TABLE IF NOT EXISTS `gw2_diff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` int(10) unsigned NOT NULL,
  `type` tinytext COLLATE utf8mb4_bin NOT NULL,
  `lang` enum('de','en','es','fr','zh') COLLATE utf8mb4_bin DEFAULT NULL,
  `date` int(10) NOT NULL,
  `data` text COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
