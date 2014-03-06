<?php
/**
 * variables.inc.php
 * created: 18.09.13
 *
 * This file defines some basic language arrays, some translations still needed.
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

// attribute combination map
$attribute_map = array(
	'single' => array(
		'winter_1' =>     'BoonDuration',
		'festering' =>    'ConditionDamage',
		'givers_1' =>     'ConditionDuration',
		'compassion' =>   'Healing',
		'might' =>        'Power',
		'precision' =>    'Precision',
		'resilience' =>   'Toughness',
		'vitality' =>     'Vitality',
	),
	'double' => array(
		'ravaging' =>     array('ConditionDamage', 'Precision'),
		'lingering' =>    array('ConditionDamage', 'Vitality'),
		'givers_2w' =>    array('ConditionDuration', 'Vitality'), //weapon
		'rejuvenation' => array('Healing', 'Power'),
		'mending' =>      array('Healing', 'Vitality'),
		'potency' =>      array('Power', 'ConditionDamage'),
		'honing' =>       array('Power', 'CritDamage'),
		'strength' =>     array('Power', 'Precision'),
		'vigor' =>        array('Power', 'Vitality'),
		'penetration' =>  array('Precision', 'CritDamage'),
		'hunter' =>       array('Precision', 'Power'),
		'enduring' =>     array('Toughness', 'ConditionDamage'),
		'givers_2a ' =>   array('Toughness', 'Healing'), //armor
		'hearty' =>       array('Vitality', 'Toughness'),
		'stout' =>        array('Toughness', 'Precision'),
		'winter_2' =>     array('Toughness', 'Healing'),
	),
	'triple' => array(
		'carrion' =>      array('ConditionDamage', 'Power', 'Vitality'),
		'rabid' =>        array('ConditionDamage', 'Precision', 'Toughness'),
		'dire' =>         array('ConditionDamage', 'Toughness', 'Vitality'),
		'givers_3w' =>    array('ConditionDuration', 'Precision', 'Vitality'), //weapon
		'apothecary' =>   array('Healing', 'ConditionDamage', 'Toughness'),
		'cleric' =>       array('Healing', 'Power', 'Toughness'),
		'magi' =>         array('Healing', 'Precision', 'Vitality'),
		'zealot' =>       array('Power', 'Healing', 'Precision'), //same stats as Keeper's
		'berserker' =>    array('Power', 'Precision', 'CritDamage'),
		'soldier' =>      array('Power', 'Toughness', 'Vitality'),
		'valkyrie' =>     array('Power', 'Vitality', 'CritDamage'),
		'rampager' =>     array('Precision', 'ConditionDamage', 'Power'),
		'assassin' =>     array('Precision', 'Power', 'CritDamage'),
		'knight_3s' =>    array('Precision', 'Power', 'Toughness'), //suffix
		'settler' =>      array('Toughness', 'ConditionDamage', 'Healing'),
		'givers_3a' =>    array('Toughness', 'Healing', 'BoonDuration'), //armor
		'cavalier' =>     array('Toughness', 'Power', 'CritDamage'),
		'knight_3p' =>    array('Toughness', 'Power', 'Precision'), //prefix
		'shaman_3p' =>    array('Vitality', 'ConditionDamage', 'Healing'), //prefix
		'sentinel' =>     array('Vitality', 'Power', 'Toughness'),
		'shaman_3s' =>    array('Vitality', 'Healing', 'Power'), //suffix
	),
);

// pre/suffix strings (used to strip from the names to create redirect links if needed etc.) - experimental
$fixes = array(
	'de' => array('Grausame ', 'Grausamer ', 'Grausames ', 'Himmlische ','Himmlischer ','Himmlisches ', ' der Fäulnis', ' der Walküre', ' des Arzneikundlers', ' des Assassinen', ' des Berserkers', ' des Explorators',
					' des Klerikers', ' des Ritters', ' des Wüters', ' des Schildwächters', ' des Spenders', ' der Magi', ' des Kavaliers', ' des Schamanen', ' des Siedlers', ' des Soldaten', 'Tollwütige ', 'Tollwütiger ', 'Tollwütiges ',
					' des Jägers', 'Energische ', 'Energischer ', 'Energisches ', 'Plündernde ', 'Plündernder ', 'Plünderndes ', 'Starke ', 'Starker ', 'Starkes ', 'Veredelte ', 'Veredelter ', 'Veredeltes ',
					'Verjüngende ', 'Verjüngender ', 'Verjüngendes ', 'Verwüstende ', 'Verwüstender ', 'Verwüstendes ', 'Wackere ', 'Wackerer ', 'Wackeres ', ' der Intelligenz', ' der Präzision', ' des Blutes', ' der Rage',
					' des Reisenden', 'Faulverstärkte ', 'Faulverstärkter ', 'Faulverstärktes ', ' des Wanderers', ' der Nacht', ' des Wassers', ' des Kampfes', ' der Verdorbenheit', ' der Energie', ' der Luft',
					' des Geomanten', ' der Erde', ' der Qual', ' der Blutgier', ' der Ogervernichtung', 'Durchdringende ', 'Durchdringender ', 'Durchdringendes ', 'Genesende ', 'Genesender ', 'Genesendes ',
					' der Heftigkeit', ' der Träume', 'Unheilvolle ', 'Unheilvoller ', 'Unheilvolles ', ' der Grawlvernichtung', ' der Schlangenvernichtung', ' der Glut', ' der Schwäche', ' des Hydromanten',
					'Heilende ', 'Heilender ', 'Heilendes ', ' der Genesung', ' des Humpelns', ' der Auslöschung', ' der Ausdauer', ' des Lebensfressers', ' der Wahrnehmung', ' des Feuers', ' der Dämonenbeschwörung', ' der Gefahr',
					' der Reinheit', ' des Eises', ' der Kühle', ' des Lebens'),//, ''

	'en' => array('Apothecary\'s ', 'Assassin\'s ', 'Berserker\'s ', 'Carrion ', 'Celestial ', 'Cleric\'s ', 'Giver\'s ', 'Knight\'s ', 'Rampager\'s ', 'Sentinel\'s ', 'Valkyrie ',
				  'Cavalier\'s ', 'Dire ', 'Magi\'s ', 'Rabid ', 'Settler\'s ', 'Shaman\'s ', 'Soldier\'s ', ' of Rage', ' of Intelligence', ' of Accuracy ', ' of Blood', ' of Energy', ' of Air', ' of Corruption', '',
				  ' of Water', ' of Dreams', 'Explorer\'s ', ' of Force', 'Hunter\'s ', 'Rejuvenating ', 'Vigorous ', 'Hearty ', 'Honed '),
	'es' => array(' celestial'),
	'fr' => array(' céleste', ' sanguinaire', ' de sang', ' de rage', ' nécrophage', ' de corruption', ' d\'eau', 'd\'exactitude', ' d\'intelligence', ' d\'air', ' d\'énergie', ' de soldat', ' enragé', ' de chamane',
					' de rêves', ' de mage', ' de cavalier', ' d\'assassin', ' de berserker', ' de chevalier', ' d\'explorateur', ' de bienfaiteur', ' de valkyrie', ' d\'apothicaire', ' de maraudeur', ' de fermeté',
					' de chasseur', ' de jouvence', ' vigoureux', ' vigoureuse', ' de vigueur', ' robuste', ' aiguisé')
);

$weapon_types = array(
	'api' =>  array('Axe', 'Dagger', 'Focus', 'Greatsword', 'Hammer', 'Harpoon', 'LargeBundle', 'LongBow', 'Mace', 'Pistol', 'Rifle', 'Scepter', 'Shield', 'ShortBow', 'Speargun', 'Staff', 'Sword', 'Torch', 'Toy', 'Trident', 'TwoHandedToy', 'Warhorn'),
	'de' => array('Axt', 'Dolch', 'Fokus', 'Großschwert', 'Hammer', 'Speer', 'Bündel', 'Langbogen', 'Streitkolben', 'Pistole', 'Gewehr', 'Zepter', 'Schild', 'Kurzbogen', 'Harpunenschleuder', 'Stab', 'Schwert', 'Fackel', 'Spielzeug', 'Dreizack', 'Zweihändiges Spielzeug', 'Kriegshorn'),
	'en' => array('Axe', 'Dagger', 'Focus', 'Greatsword', 'Hammer', 'Harpoon', 'Large bundle', 'Longbow', 'Mace', 'Pistol', 'Rifle', 'Scepter', 'Shield', 'Shortbow', 'Speargun', 'Staff', 'Sword', 'Torch', 'Toy', 'Trident', 'Two handed toy', 'Warhorn'),
	'es' => array('---'),
	'fr' => array('Hache','Dague','Focus','Espadon','Marteau','Lance','-','Arc long','Masse','Pistolet','Fusil','Sceptre','Bouclier','Arc court','Fusil-harpon','Bâton','Épée','Torche','-','Trident','-','Cor de guerre')
);

$infusions = array(
	'api' => array('Defense','Offense','Utility'),
	'de' => array('defensiv','offensiv','hilfe'),
	'en' => array('Defense','Offense','Utility'),
	'es' => array('Defense','Offense','Utility'),
	'fr' => array('Defense','Offense','Utility')
);

$armor_weight = array(
	'api' => array('Clothing','Light','Medium','Heavy'),
	'de' => array('Stadtkleidung','leicht','mittel','schwer'),
	'en' => array('clothing','light','medium','heavy'),
	'es' => array('-','-','-','-'),
	'fr' => array('-','-','-','-')
);

$armor_pos = array(
	'api' => array('Boots','Coat','Gloves','Helm','HelmAquatic','Leggings','Shoulders'),
	'de' => array('fuß','brust','hand','kopf','atemgerät','bein','schulter'),
	'en' => array('Boots','Coat','Gloves','Helm','HelmAquatic','Leggings','Shoulders'),
	'es' => array('-','-','-','-','-','-','-'),
	'fr' => array('-','-','-','-','-','-','-')
);

?>