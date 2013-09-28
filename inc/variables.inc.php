<?php
/**
 * variables.inc.php
 * created: 18.09.13
 */

/*
 * TODO
 */

define('Armorsmith', 0x1);
define('Artificer', 0x2);
define('Chef', 0x4);
define('Huntsman', 0x8);
define('Jeweler', 0x10);
define('Leatherworker', 0x20);
define('Tailor', 0x40);
define('Weaponsmith', 0x80);

// rarity strings
$rarity = array(
	'de' => array('Schrott','Einfach','Edel','Meisterwerk','Selten','Exotisch','Aufgestiegen','Legendär'),
	'en' => array('Junk','Basic','Fine','Masterwork','Rare','Exotic','Ascended','Legendary'),
	'es' => array('Basura','Genérico','Bueno','Obra Maestra','Excepcional','Exótico','Ascendido','Legendario'),
	'fr' => array('Inutile','Simple','Raffiné','Chef-d\'œuvre','Rare','Exotique','Élevé','Légendaire')
);

// discipline strings
$disciplines = array(
	'de' => array('Rüstungsschmied','Konstrukteur','Küchenmeister','Waidmann','Juwelier','Lederer','Schneider','Waffenschmied'),
	'en' => array('Armorsmith','Artificer','Chef','Huntsman','Jeweler','Leatherworker','Tailor','Weaponsmith'),
	'es' => array('Forjador de armaduras','Artificiero','Cocinero','Cazador','Joyero','Peletero','Sastre','Armero'),
	'fr' => array('Forgeron d\'armures','Artificier','Maître-queux','Chasseur','Bijoutier','Travailleur du cuir','Tailleur','Forgeron d\'armes')
);

// attribute strings
$attributes = array(
	'api' => array('AgonyResistance','BoonDuration','ConditionDamage','ConditionDuration','CritDamage','Healing','MagicFind','Power','Precision','Toughness','Vitality'),
	'de' => array('Qual-Widerstand','Segensdauer','Zustandsschaden','Zustandsdauer','Kritischer Schaden','Heilkraft','Magisches Gespür','Kraft','Präzision','Zähigkeit','Vitalität'),
	'en' => array('Agony Resistance','Boon Duration','Condition Damage','Condition Duration','Critical Damage','Healing Power','Magic Find','Power','Precision','Toughness','Vitality'),
	'es' => array('---'),
	'fr' => array('---')
);

// pre/suffix strings (used to strip from the names to create redirect links if needed etc.) - experimental
$fixes = array(
	'de' => array('Grausame ', 'Grausamer ', 'Grausames ', 'Himmlische ','Himmlischer ','Himmlisches ', ' der Fäulnis', ' der Walküre', ' des Arzneikundlers', ' des Assassinen', ' des Berserkers', ' des Explorators',
				  ' des Klerikers', ' des Ritters', ' des Wüters', ' des Schildwächters', ' des Spenders', ' der Magi', ' des Kavaliers', ' des Schamanen', ' des Siedlers', ' des Soldaten', 'Tollwütige ', 'Tollwütiger ', 'Tollwütiges ',
				  ' des Jägers', 'Energische ', 'Energischer ', 'Energisches ', 'Plündernde ', 'Plündernder ', 'Plünderndes ', 'Starke ', 'Starker ', 'Starkes ', 'Veredelte ', 'Veredelter ', 'Veredeltes ',
				  'Verjüngende ', 'Verjüngender ', 'Verjüngendes ', 'Verwüstende ', 'Verwüstender ', 'Verwüstendes ', 'Wackere ', 'Wackerer ', 'Wackeres ', ' der Intelligenz', ' der Präzision', ' des Blutes', ' der Rage' ),
	'en' => array('Apothecary\'s ', 'Assassin\'s ', 'Berserker\'s ', 'Carrion ', 'Celestial ', 'Cleric\'s ', 'Giver\'s ', 'Knight\'s ', 'Rampager\'s ', 'Sentinel\'s ', 'Valkyrie ',
				  'Cavalier\'s ', 'Dire ', 'Magi\'s ', 'Rabid ', 'Settler\'s ', 'Shaman\'s ', 'Soldier\'s '),
	'es' => array(' celestial'),
	'fr' => array(' céleste')
);

$weapon_types = array(
	'api' =>  array('Axe', 'Dagger', 'Focus', 'Greatsword', 'Hammer', 'Harpoon', 'LargeBundle', 'LongBow', 'Mace', 'Pistol', 'Rifle', 'Scepter', 'Shield', 'ShortBow', 'Speargun', 'Staff', 'Sword', 'Torch', 'Toy', 'Trident', 'TwoHandedToy', 'Warhorn'),
	'de' => array('Axt', 'Dolch', 'Fokus', 'Großschwert', 'Hammer', 'Speer', 'Bündel', 'Langbogen', 'Streitkolben', 'Pistole', 'Gewehr', 'Zepter', 'Schild', 'Kurzbogen', 'Harpunenschleuder', 'Stab', 'Schwert', 'Fackel', 'Spielzeug', 'Dreizack', 'Zweihändiges Spielzeug', 'Kriegshorn'),
	'en' => array('Axe', 'Dagger', 'Focus', 'Greatsword', 'Hammer', 'Harpoon', 'Large bundle', 'Longbow', 'Mace', 'Pistol', 'Rifle', 'Scepter', 'Shield', 'Shortbow', 'Speargun', 'Staff', 'Sword', 'Torch', 'Toy', 'Trident', 'Two handed toy', 'Warhorn'),
	'es' => array('---'),
	'fr' => array('---')
);

$infusions = array(
	'api' => array('Defense','Offense','Utility'),
	'de' => array('defensiv','offensiv','hilfe'),
	'en' => array('Defense','Offense','Utility'),
	'es' => array('Defense','Offense','Utility'),
	'fr' => array('Defense','Offense','Utility')
);



?>