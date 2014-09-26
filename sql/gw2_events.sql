CREATE TABLE IF NOT EXISTS `gw2_events` (
  `event_id` varchar(40) COLLATE utf8mb4_bin NOT NULL,
  `map_id` SMALLINT(4) unsigned NOT NULL DEFAULT '0',
  `level` SMALLINT(4) unsigned NOT NULL DEFAULT '0',
  `flags` tinytext COLLATE utf8mb4_bin NOT NULL,
  `location` text COLLATE utf8mb4_bin NOT NULL,
  `name_de` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_en` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_es` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_fr` tinytext COLLATE utf8mb4_bin NOT NULL,
  `wikipage_de` int(10) unsigned NOT NULL DEFAULT '0',
  `wikipage_en` int(10) unsigned NOT NULL DEFAULT '0',
  `wikipage_es` int(10) unsigned NOT NULL DEFAULT '0',
  `wikipage_fr` int(10) unsigned NOT NULL DEFAULT '0',
  `wiki_checked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
