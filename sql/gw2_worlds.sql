CREATE TABLE IF NOT EXISTS `gw2_worlds` (
  `world_id` SMALLINT(4) unsigned NOT NULL,
  `lang` enum('us','eu','de','fr','es') COLLATE utf8mb4_bin NOT NULL,
  `name_de` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_en` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_es` tinytext COLLATE utf8mb4_bin NOT NULL,
  `name_fr` tinytext COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`world_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;


INSERT INTO `gw2_worlds` (`world_id`, `lang`, `name_de`, `name_en`, `name_es`, `name_fr`) VALUES
(0, 'eu', '-', '-', '-', '-'),
(1001, 'us', 'Ambossfels', 'Anvil Rock', 'Roca del Yunque', 'Rocher de l''enclume'),
(1002, 'us', 'Borlis-Pass', 'Borlis Pass', 'Paso de Borlis', 'Passage de Borlis'),
(1003, 'us', 'Jakbiegung', 'Yak''s Bend', 'Declive del Yak', 'Courbe du Yak'),
(1004, 'us', 'Denravis Erdwerk', 'Henge of Denravi', 'Círculo de Denravi', 'Cromlech de Denravi'),
(1005, 'us', 'Maguuma', 'Maguuma', 'Maguuma', 'Maguuma'),
(1006, 'us', 'Hochofen der Betrübnis', 'Sorrow''s Furnace', 'Fragua del Pesar', 'Fournaise des lamentations'),
(1007, 'us', 'Tor des Irrsinns', 'Gate of Madness', 'Puerta de la Locura', 'Porte de la folie'),
(1008, 'us', 'Jade-Steinbruch', 'Jade Quarry', 'Cantera de Jade', 'Carrière de jade'),
(1009, 'us', 'Fort Espenwald', 'Fort Aspenwood', 'Fuerte Aspenwood', 'Fort Trembleforêt'),
(1010, 'us', 'Ehmry-Bucht', 'Ehmry Bay', 'Bahía de Ehmry', 'Baie d''Ehmry'),
(1011, 'us', 'Sturmklippen-Insel', 'Stormbluff Isle', 'Isla Cimatormenta', 'Ile de la Falaise tumultueuse'),
(1012, 'us', 'Finsterfreistatt', 'Darkhaven', 'Refugio Oscuro', 'Refuge noir'),
(1013, 'us', 'Heilige Halle von Rall', 'Sanctum of Rall', 'Sagrario de Rall', 'Sanctuaire de Rall'),
(1014, 'us', 'Kristallwüste', 'Crystal Desert', 'Desierto de Cristal', 'Désert de cristal'),
(1015, 'us', 'Janthir-Insel', 'Isle of Janthir', 'Isla de Janthir', 'Ile de Janthir'),
(1016, 'us', 'Meer des Leids', 'Sea of Sorrows', 'El Mar de los Pesares', 'Mer des lamentations'),
(1017, 'us', 'Befleckte Küste', 'Tarnished Coast', 'Costa de Bronce', 'Côte ternie'),
(1018, 'us', 'Nördliche Zittergipfel', 'Northern Shiverpeaks', 'Picosescalofriantes del Norte', 'Cimefroides nordiques'),
(1019, 'us', 'Schwarztor', 'Blackgate', 'Puertanegra', 'Portenoire'),
(1020, 'us', 'Fergusons Kreuzung', 'Ferguson''s Crossing', 'Encrucijada de Ferguson', 'Croisée de Ferguson'),
(1021, 'us', 'Drachenbrand', 'Dragonbrand', 'Marca del Dragón', 'Stigmate du dragon'),
(1022, 'us', 'Kaineng', 'Kaineng', 'Kaineng', 'Kaineng'),
(1023, 'us', 'Devonas Rast', 'Devona''s Rest', 'Descanso de Devona', 'Repos de Devona'),
(1024, 'us', 'Eredon-Terrasse', 'Eredon Terrace', 'Terraza de Eredon', 'Plateau d''Eredon'),
(2001, 'eu', 'Klagenriss', 'Fissure of Woe', 'Fisura de la Aflicción', 'Fissure du malheur'),
(2002, 'eu', 'Ödnis', 'Desolation', 'Desolación', 'Désolation'),
(2003, 'eu', 'Gandara', 'Gandara', 'Gandara', 'Gandara'),
(2004, 'eu', 'Schwarzflut', 'Blacktide', 'Marea Negra', 'Noirflot'),
(2005, 'eu', 'Feuerring', 'Ring of Fire', 'Anillo de Fuego', 'Cercle de feu'),
(2006, 'eu', 'Unterwelt', 'Underworld', 'Inframundo', 'Outre-monde'),
(2007, 'eu', 'Ferne Zittergipfel', 'Far Shiverpeaks', 'Lejanas Picosescalofriantes', 'Lointaines Cimefroides'),
(2008, 'eu', 'Weißflankgrat', 'Whiteside Ridge', 'Cadena Laderablanca', 'Crête de Verseblanc'),
(2009, 'eu', 'Ruinen von Surmia', 'Ruins of Surmia', 'Ruinas de Surmia', 'Ruines de Surmia'),
(2010, 'eu', 'Seemannsrast', 'Seafarer''s Rest', 'Refugio del Viajante', 'Repos du Marin'),
(2011, 'eu', 'Vabbi', 'Vabbi', 'Vabbi', 'Vabbi'),
(2012, 'eu', 'Piken-Platz', 'Piken Square', 'Plaza de Piken', 'Place Piken'),
(2013, 'eu', 'Lichtung der Morgenröte', 'Aurora Glade', 'Claro de la Aurora', 'Clairière de l''aurore'),
(2014, 'eu', 'Gunnars Feste', 'Gunnar''s Hold', 'Fuerte de Gunnar', 'Campement de Gunnar'),
(2101, 'fr', 'Jademeer [FR]', 'Jade Sea [FR]', 'Mar de Jade [FR]', 'Mer de Jade [FR]'),
(2102, 'fr', 'Fort Ranik [FR]', 'Fort Ranik [FR]', 'Fuerte Ranik [FR]', 'Fort Ranik [FR]'),
(2103, 'fr', 'Augurenstein [FR]', 'Augury Rock [FR]', 'Roca del Augurio [FR]', 'Roche de l''Augure [FR]'),
(2104, 'fr', 'Vizunah-Platz [FR]', 'Vizunah Square [FR]', 'Plaza de Vizunah [FR]', 'Place de Vizunah [FR]'),
(2105, 'fr', 'Laubenstein [FR]', 'Arborstone [FR]', 'Piedra Arbórea [FR]', 'Pierre Arborea [FR]'),
(2201, 'de', 'Kodasch [DE]', 'Kodash [DE]', 'Kodash [DE]', 'Kodash [DE]'),
(2202, 'de', 'Flussufer [DE]', 'Riverside [DE]', 'Ribera [DE]', 'Provinces fluviales [DE]'),
(2203, 'de', 'Elonafels [DE]', 'Elona Reach [DE]', 'Cañón de Elona [DE]', 'Bief d''Elona [DE]'),
(2204, 'de', 'Abaddons Mund [DE]', 'Abaddon''s Mouth [DE]', 'Boca de Abaddon [DE]', 'Bouche d''Abaddon [DE]'),
(2205, 'de', 'Drakkar-See [DE]', 'Drakkar Lake [DE]', 'Lago Drakkar [DE]', 'Lac Drakkar [DE]'),
(2206, 'de', 'Millersund [DE]', 'Miller''s Sound [DE]', 'Estrecho de Miller [DE]', 'Détroit de Miller [DE]'),
(2207, 'de', 'Dzagonur [DE]', 'Dzagonur [DE]', 'Dzagonur [DE]', 'Dzagonur [DE]'),
(2301, 'es', 'Baruch-Bucht [SP]', 'Baruch Bay [SP]', 'Bahía de Baruch [ES]', 'Baie de Baruch [SP]');
