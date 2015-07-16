CREATE TABLE IF NOT EXISTS `gw2_colors` (
  `color_id` smallint(5) unsigned NOT NULL,
  `name_de` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_en` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_es` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_fr` tinytext COLLATE utf8mb4_bin NOT NULL,
  `base_rgb` tinytext COLLATE utf8mb4_bin NOT NULL,
  `cloth` tinytext COLLATE utf8mb4_bin NOT NULL,
  `leather` tinytext COLLATE utf8mb4_bin NOT NULL,
  `metal` tinytext COLLATE utf8mb4_bin NOT NULL,
  `updated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `added` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`color_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
