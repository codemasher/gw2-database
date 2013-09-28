CREATE TABLE IF NOT EXISTS `gw2_colors` (
  `id` SMALLINT(4) unsigned NOT NULL AUTO_INCREMENT,
  `color_id` SMALLINT(4) unsigned NOT NULL,
  `item_id` MEDIUMINT(6) unsigned NOT NULL,
  `tone` enum('red','orange','yellow','green','blue','purple','brown','gray','none') COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `set` enum('starter','common','uncommon','rare','special','none') COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `material` tinytext COLLATE utf8_bin NOT NULL, -- will be changed to enum once we get this via the API
  `icon` enum('fine-left','fine-right','masterwork-left','masterwork-right','rare-left','rare-right','special','none') COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `name_de` tinytext COLLATE utf8_bin NOT NULL,
  `name_en` tinytext COLLATE utf8_bin NOT NULL,
  `name_es` tinytext COLLATE utf8_bin NOT NULL,
  `name_fr` tinytext COLLATE utf8_bin NOT NULL,
  `cloth` tinytext COLLATE utf8_bin NOT NULL,
  `leather` tinytext COLLATE utf8_bin NOT NULL,
  `metal` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
