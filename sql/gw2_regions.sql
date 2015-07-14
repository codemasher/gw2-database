CREATE TABLE IF NOT EXISTS `gw2_regions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `region_id` smallint(3) NOT NULL DEFAULT '0',
  `continent_id` tinyint(2) NOT NULL DEFAULT '0',
  `floor_id` smallint(3) NOT NULL DEFAULT '0',
  `label_coord` tinytext COLLATE utf8_bin NOT NULL,
  `maps` text COLLATE utf8_bin NOT NULL,
  `name_de` enum('Ascalon','Dampfsporngebirge','Zittergipfelgebirge','Maguuma-Einöde','Befleckte Küste','Fraktale der Nebel','Spieler gegen Spieler','Kryta','Welt gegen Welt','Reich des Verrückten Königs','Ruinen von Orr','Super Adventure Box') COLLATE utf8mb4_bin NOT NULL,
  `name_en` enum('Ascalon','Steamspur Mountains','Shiverpeak Mountains','Maguuma Wastes','Tarnished Coast','Fractals of the Mists','Player vs. Player','Kryta','World vs. World','Mad King''s Realm','Ruins of Orr','Super Adventure Box') COLLATE utf8mb4_bin NOT NULL,
  `name_es` enum('Ascalon','Montañas Brotavapor','Montañas Picosescalofriantes','Páramos de Maguuma','Costa de Bronce','Fractales de la Niebla','Jugador contra Jugador','Kryta','Mundo contra Mundo','Reino del Rey Loco','Ruinas de Orr','Super Adventure Box') COLLATE utf8mb4_bin NOT NULL,
  `name_fr` enum('Ascalon','Chaîne de Pointebrume','Chaîne des Cimefroides','Contrées sauvages de Maguuma','Côte ternie','Fractales des Brumes','Joueur contre Joueur','Kryte','Monde contre Monde','Royaume du Roi Dément','Ruines d''Orr','Super Adventure Box') COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;