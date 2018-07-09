<?php
/**
 * Class ItemSearch
 *
 *
 *
 * @filesource   ItemSearch.php
 * @created      04.10.2017
 * @package      chillerlan\GW2DB
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB;

use chillerlan\Database\{Drivers\DriverInterface, Result};
use chillerlan\GW2DB\Helpers as func;
use chillerlan\GW2DB\Helpers\Chatlinks\Chatlink;
use chillerlan\GW2DB\Updaters\UpdaterInterface;
use chillerlan\Traits\ContainerInterface;

/**
 * AJAX methods for the item search
 */
class ItemSearch{

	/**
	 * @var \chillerlan\Database\Database
	 */
	protected $db;

	/**
	 * @var \chillerlan\GW2DB\Helpers\Chatlinks\Chatlink
	 */
	protected $chatlink;

	/**
	 * @var \chillerlan\GW2DB\GW2DBOptions
	 */
	protected $options;

	/**
	 * ItemSearch constructor.
	 *
	 * @param \chillerlan\Traits\ContainerInterface        $options
	 * @param \chillerlan\Database\Drivers\DriverInterface $db
	 *
	 * @throws \chillerlan\Database\Drivers\DriverException
	 */
	public function __construct(ContainerInterface $options, DriverInterface $db){
		$this->options = $options;
		$this->db      = $db;

		$this->db->connect();

		$this->chatlink = new Chatlink;
	}

	/**
	 * @return array
	 */
	public function loadForm():array {
		$response = [];

		// list types and subtypes
		$combinations = $this->db->select
			->cols(['type', 'subtype'])
			->from([$this->options->tableItems])
			->groupBy(['type', 'subtype'])
			->orderBy(['type', 'subtype'])
			->cached()
			->query();

		foreach($combinations as $sub){
			$response['subtypes'][$sub['type']][] = $sub['subtype'];
		}

		$response['types'] = array_keys($response['subtypes']);

		// list attribute combinations
		$combinations = $this->db->select
			->from(['gw2_attribute_combinations'])
			->cached()
			->query()
			->__toArray();

		$response['combinations'] = array_map(function($c){

			$combination = [
				'id'         => $c['id'],
				'attributes' => [$c['attribute1']],
			];

			if($c['id'] === 52){
				$combination['attributes'] = ['Celestial'];

				return $combination;
			}

			if(!empty($c['attribute2'])){
				$combination['attributes'][] = $c['attribute2'];

				if(!empty($c['attribute3'])){
					$combination['attributes'][] = $c['attribute3'];

					if(!empty($c['attribute4'])){
						$combination['attributes'][] = $c['attribute4'];
					}
				}
			}

			return $combination;

		}, $combinations);

		return $response;
	}

	/**
	 * @param string $json
	 *
	 * @return array
	 */
	public function search(string $json):array {
		$response = [];

		// decode the json
		if(!$data = json_decode($json, true)){
			$response['error'] = 'JOSN error';

			return $response;
		}

		$form = $data['form'];

		// whitelist language
		$lang = in_array($form['lang'], UpdaterInterface::API_LANGUAGES, true) ? $form['lang'] : 'en' ;

		// determine the current page number
		$page = $data['p'] ?? 1;

		// search text
		$str = utf8_encode(base64_decode($data['str']));

		// determine the correct language column / else set a default

		// ORDER BY clause
		$orderby = [
			'id'     => 'id',
			'name'   => 'name_'.$lang,
			'level'  => 'level',
			'rarity' => 'rarity',
			'weight' => 'weight',
			'attr'   => 'attr_name',
		][$form['orderby']] ?? (func\check_int($str) || preg_match("/\d+-\d+/", $str) ? 'id' : 'name_'.$lang);

		$q = $this->db->select
			->cols(['name' => 'name_'.$lang, 'id', 'level', 'rarity'])//, 'data' => 'data_'.$lang
			->from([$this->options->tableItems])
			->orderBy([$orderby => isset($form['orderdir']) && $form['orderdir'] === 'desc' ? 'DESC' : 'ASC']);

		// determine search mode: id, id-range, string (is_int() doesn't work here!)
		if(func\check_int($str)){
			$q->where('id', '%'.intval($str).'%', 'LIKE');
		}
		else if(preg_match("/\d+-\d+/", $str)
			&& is_array($range = explode('-', $str))
			&& count($range) === 2
			&& func\check_int(trim($range[0]))
			&& func\check_int(trim($range[1]))
		){
			$q
				->where('id', min(intval(trim($range[0])), intval(trim($range[1]))), '>=')
				->where('id', max(intval(trim($range[0])), intval(trim($range[1]))), '<=');
		}
		else{
			$q->where(['name_'.$lang, 'lower'], '%'.mb_strtolower($str).'%', 'LIKE');
		}

		if($form['type'] ?? false){
			$q->where('type', $form['type']);
		}

		if($form['subtype'] ?? false){
			$q->where('subtype', $form['subtype']);
		}

		if($form['attributes'] ?? false){
			$q->where('attr_combination', $form['attributes']);
		}

		if($form['weight'] ?? false){
			$q->where('weight', $form['weight']);
		}

		if($form['rarity'] ?? false){
			$q->where('rarity', $form['rarity']);
		}

		if($form['min-level'] ?? false){

			$level = isset($form['max-level']) && (int)$form['max-level'] < (int)$form['min-level']
				? (int)$form['max-level']
				: (int)$form['min-level'];

			$q->where('level', $level, '>=');
		}

		if($form['max-level'] ?? false){

			$level = isset($form['min-level']) && (int)$form['min-level'] > (int)$form['max-level']
				? (int)$form['min-level']
				: (int)$form['max-level'];

			$q->where('level', $level, '<=');
		}

		if($form['gametype'] ?? false){
			$q->where('pvp', (int)$form['gametype'] === 'pvp');
		}

		// items per page limit
		$limit = isset($form['limit']) && !empty($form['limit'])
			? max(1, min(250, intval($form['limit'])))
			: 50;

		// create the pagination
		$pagination = func\pagination($q->count(), $page, $limit);

		$result = $q
			->limit($limit)
			->offset($pagination['pages'][$page] ?? 0)
			->cached()
			->query();

		// process the result
		if($result instanceof Result && $result->length > 0){
			$response['data'] = $result->__toArray();
			$response['pagination'] = $pagination['html'];
		}
		else{
			$response['error'] = 'no results';
			$response['pagination'] = '';
		}

		return $response;
	}

	/**
	 * @param string $json
	 *
	 * @return array
	 */
	public function showDetails(string $json){
		$response = [];

		if(!$data = json_decode($json,true)){
			$response['error'] = 'JOSN error';

			return $response;
		}

		$lang = in_array($data['lang'], UpdaterInterface::API_LANGUAGES, true) ? $data['lang'] : 'en';

		$result = $this->db->select
			->from([$this->options->tableItems])
			->where('id', $data['id'])
			->limit(1)
			->cached()
			->query();

		$item = $result[0];

		// wiki prefixes
		$wikis = [
			'de' => 'wiki-de',
			'en' => 'wiki',
			'es' => 'wiki-es',
			'fr' => 'wiki-fr',
		];

		$redirect = [
			'de' => 'WEITERLEITUNG',
			'en' => 'REDIRECT',
			'es' => 'REDIRECT',
			'fr' => 'REDIRECTION',
		];

		$n = "\n";

		// interwiki links
		$interwiki =[
			'de' => $n.'[[en:'.$item['name_en'].']]'.$n.'[[es:'.$item['name_es'].']]'.$n.'[[fr:'.$item['name_fr'].']]',
			'en' => $n.'[[de:'.$item['name_de'].']]'.$n.'[[es:'.$item['name_es'].']]'.$n.'[[fr:'.$item['name_fr'].']]',
			'es' => $n.'[[de:'.$item['name_de'].']]'.$n.'[[en:'.$item['name_en'].']]'.$n.'[[fr:'.$item['name_fr'].']]',
			'fr' => $n.'[[de:'.$item['name_de'].']]'.$n.'[[en:'.$item['name_en'].']]'.$n.'[[es:'.$item['name_es'].']]'
		];

		// pre/suffix strings (used to strip from the names to create redirect links if needed etc.) - experimental
		$fixes = [
			'de' => [
				'Grausame ', 'Grausamer ', 'Grausames ', 'Himmlische ', 'Himmlischer ', 'Himmlisches ', ' der Fäulnis', ' der Walküre', ' des Arzneikundlers', ' des Assassinen', ' des Berserkers', ' des Explorators',
				' des Klerikers', ' des Ritters', ' des Wüters', ' des Schildwächters', ' des Spenders', ' der Magi', ' des Kavaliers', ' des Schamanen', ' des Siedlers', ' des Soldaten', 'Tollwütige ', 'Tollwütiger ', 'Tollwütiges ',
				' des Jägers', 'Energische ', 'Energischer ', 'Energisches ', 'Plündernde ', 'Plündernder ', 'Plünderndes ', 'Starke ', 'Starker ', 'Starkes ', 'Veredelte ', 'Veredelter ', 'Veredeltes ',
				'Verjüngende ', 'Verjüngender ', 'Verjüngendes ', 'Verwüstende ', 'Verwüstender ', 'Verwüstendes ', 'Wackere ', 'Wackerer ', 'Wackeres ', ' der Intelligenz', ' der Präzision', ' des Blutes', ' der Rage',
				' des Reisenden', 'Faulverstärkte ', 'Faulverstärkter ', 'Faulverstärktes ', ' des Wanderers', ' der Nacht', ' des Wassers', ' des Kampfes', ' der Verdorbenheit', ' der Energie', ' der Luft',
				' des Geomanten', ' der Erde', ' der Qual', ' der Blutgier', ' der Ogervernichtung', 'Durchdringende ', 'Durchdringender ', 'Durchdringendes ', 'Genesende ', 'Genesender ', 'Genesendes ',
				' der Heftigkeit', ' der Träume', 'Unheilvolle ', 'Unheilvoller ', 'Unheilvolles ', ' der Grawlvernichtung', ' der Schlangenvernichtung', ' der Glut', ' der Schwäche', ' des Hydromanten',
				'Heilende ', 'Heilender ', 'Heilendes ', ' der Genesung', ' des Humpelns', ' der Auslöschung', ' der Ausdauer', ' des Lebensfressers', ' der Wahrnehmung', ' des Feuers', ' der Dämonenbeschwörung', ' der Gefahr',
				' der Reinheit', ' des Eises', ' der Kühle', ' des Lebens',
			],//, ''

			'en' => [
				'Apothecary\'s ', 'Assassin\'s ', 'Berserker\'s ', 'Carrion ', 'Celestial ', 'Cleric\'s ', 'Giver\'s ', 'Knight\'s ', 'Rampager\'s ', 'Sentinel\'s ', 'Valkyrie ',
				'Cavalier\'s ', 'Dire ', 'Magi\'s ', 'Rabid ', 'Settler\'s ', 'Shaman\'s ', 'Soldier\'s ', ' of Rage', ' of Intelligence', ' of Accuracy ', ' of Blood', ' of Energy', ' of Air', ' of Corruption', '',
				' of Water', ' of Dreams', 'Explorer\'s ', ' of Force', 'Hunter\'s ', 'Rejuvenating ', 'Vigorous ', 'Hearty ', 'Honed ',
			],
			'es' => [' celestial'],
			'fr' => [
				' céleste', ' sanguinaire', ' de sang', ' de rage', ' nécrophage', ' de corruption', ' d\'eau', 'd\'exactitude', ' d\'intelligence', ' d\'air', ' d\'énergie', ' de soldat', ' enragé', ' de chamane',
				' de rêves', ' de mage', ' de cavalier', ' d\'assassin', ' de berserker', ' de chevalier', ' d\'explorateur', ' de bienfaiteur', ' de valkyrie', ' d\'apothicaire', ' de maraudeur', ' de fermeté',
				' de chasseur', ' de jouvence', ' vigoureux', ' vigoureuse', ' de vigueur', ' robuste', ' aiguisé',
			],
		];

		// API response JSON
		foreach(UpdaterInterface::API_LANGUAGES as $lng){
			$item['data_'.$lng] = json_decode($item['data_'.$lng]);
		}

		$icon_url = 'http://darthmaim-cdn.de/gw2treasures/icons/'.$item->signature.'/'.$item->file_id.'.png';
		$icon_api = 'https://render.guildwars2.com/file/'.$item->signature.'/'.$item->file_id.'.png';

		$chatlink = new \stdClass;
		$chatlink->id = $item->id;
		$chatlink->type = Chatlink::ITEM;
		$chatlink = $this->chatlink->encode($chatlink);

		// ...do stuff. @todo

		$response['html'] = '
		<h3>'.$item->{'name_'.$lang}.'</h3>
		<span class="description">'.nl2br($item->{'data_'.$lang}->description ?? '').'</span>
		<h4>item id/chat code</h4>
		<input type="text" readonly="readonly" value="'.$item->id.'" class="selectable" style="width:10em;" />
		<input type="text" readonly="readonly" value="'.$chatlink.'" class="selectable" style="width:10em;" />';

		$response['html'] .= '
		<h4>icon</h4>
		<img src="'.$icon_url.'"> <img src="'.$icon_api.'"><br />
		<span style="font-size: 70%;">(gw2treasures icons have metadata stripped)</span><br />
		<input type="text" readonly="readonly" value="'.$icon_url.'" class="selectable" /><br />
		<input type="text" readonly="readonly" value="'.$icon_api.'" class="selectable" />';

		$response['html'] .= '
		<h4>interwikis</h4>';

		foreach($wikis as $l => $wiki){
			$response['html'] .= '
		<!--<img src="icons/'.$l.'.png">--> ['.$l.'] 
		<a class="wikilink" href="https://'.$wiki.'.guildwars2.com/wiki/'.str_replace(' ', '_', $item['name_'.$l]).'" target="wiki-'.$l.'">'.$item['name_'.$l].'</a> -
		<a href="https://'.$wiki.'.guildwars2.com/index.php?title='.str_replace(' ', '_', $item['name_'.$l]).'&amp;action=edit" target="wiki-'.$l.'">edit wiki</a>
		(<a target="apidata" href="https://api.guildwars2.com/v2/items/'.$item['id'].'?lang='.$l.'">API</a> - 
		<a target="gw2treasures" href="https://'.$l.'.gw2treasures.com/item/'.$item['id'].'">gw2treasures</a>)<br />
		<input type="text" readonly="readonly" value="'.$item['name_'.$l].'" class="selectable" /><br />
		<textarea cols="20" readonly="readonly" class="selectable" rows="3">'.$interwiki[$l].'</textarea><br />
		<input type="text" readonly="readonly" value="#'.$redirect[$l].' [['.str_replace($fixes[$l], '', $item['name_'.$l]).']]" class="selectable" /><br />
		<br />';
		}

			$response['html'] .= '
		<h4>'.file_get_contents('http://www.sloganizer.net/en/outbound.php?slogan='.$item->name_en).'</h4>		
		<a target="gw2spidy" href="https://www.gw2spidy.com/item/'.$item['id'].'">gw2spidy</a>

		';

		return $response;
	}

	/**
	 * @todo
	 *
	 * @param string $json
	 *
	 * @return array
	 * @throws \chillerlan\GW2DB\Helpers\Chatlinks\ChatlinkException
	 */
	public function chatlinkSearch(string $json):array{
		$response = [];

		// decode the json
		if(!$data = json_decode($json, true)){
			$response['error'] = 'JOSN error';

			return $response;
		}

		$form = $data['form'];

		// whitelist language
		$lang = in_array($form['lang'], UpdaterInterface::API_LANGUAGES, true) ? $form['lang'] : 'en' ;


		// http://wiki.guildwars2.com/index.php?title=Special:Search&search=[&AgG/twDgthIAAAZgAADnXwAA]&fulltext=1
		// [&CwoEAAA=][&C88UAAA=][&C+YUAAA=][&DAQAAAA=][&DAMAAAA=][&AgHJrwAA][&C7oDAAA=][&AgG/twDgthIAAAZgAADnXwAA][&AgFdKwBAwGAAAA==][&AgGHKwBAwGAAAA==][&AgHbKwBAwGAAAA==][&AgEFLABAwGAAAA==][&AgFSLABACmEAAA==][&AgENqwBAwGAAAA==][&AgHtmADAIAkAALNfAAA=][&AgF7mgAA][&AgF6mgAA][&AgG7NABAs18AAA==][&AgFnNABAs18AAA==][&AgGsmgAA][&AgGEeQBA6l8AAA==][&AgF0OADAkhQAACpgAAA=][&AgF4eQBAJ2AAAA==][&AgF4eQBA/F8AAA==][&AvpkXwAA][&BDgAAAA=][&BEgAAAA=][&BDkDAAA=][&B+cCAAA=][&B3MVAAA=][&B30VAAA=][&CPIDAAA=][&CgEAAAA=][&CgIAAAA=][&CgcAAAA=][&CwQAAAA=][&DAQAAAA=][&AxcnAAA=][&AdsnAAA=]
		$ids = [];
		foreach($data['matches'] as $str){
			$chatlink = $this->chatlink->decode($str);

			if($chatlink->type === Chatlink::ITEM){
				$ids[] = $chatlink->id;

				if(isset($chatlink->upgrades)){
					$ids = array_merge($ids, $chatlink->upgrades);
				}
			}
		}

		$result = $this->db->select
			->cols(['name' => 'name_'.$lang, 'id', 'level', 'rarity'])//, 'data' => 'data_'.$lang
			->from([$this->options->tableItems])
			->where('id', $ids, 'in')
			->limit(250)
			->cached()
			->query();


		$response['data'] = $data['matches'];

		// process the result
		if($result instanceof Result && $result->length > 0){
			$response['data'] = $result->__toArray();
		}
		else{
			$response['error'] = 'no results';
		}

		return $response;
	}

}
