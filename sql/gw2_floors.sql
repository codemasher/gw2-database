CREATE TABLE IF NOT EXISTS `gw2_map_floors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `continent_id` tinyint(2) unsigned NOT NULL,
  `floor_id` smallint(3) NOT NULL,
  `regions` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
