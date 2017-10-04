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

use chillerlan\Database\{Connection, Result};
use chillerlan\GW2DB\Helpers as func;
use chillerlan\GW2DB\Helpers\Chatlinks\Chatlink;
use chillerlan\GW2DB\Updaters\UpdaterInterface;

/**
 * AJAX methods for the item search
 */
class ItemSearch{

	/**
	 * @var \chillerlan\Database\Connection
	 */
	protected $db;

	/**
	 * @var \chillerlan\GW2DB\Helpers\Chatlinks\Chatlink
	 */
	protected $Chatlink;

	/**
	 * ItemSearch constructor.
	 *
	 * @param \chillerlan\Database\Connection $db
	 */
	public function __construct(Connection $db){
		$this->db = $db;
		$this->db->connect();

		$this->Chatlink = new Chatlink;
	}

	/**
	 * @return array
	 */
	public function loadForm():array {
		$response = [];

		// list types and subtypes
		$combinations = $this->db->select
			->cols(['type', 'subtype'])
			->from(['gw2_items'])
			->groupBy(['type', 'subtype'])
			->orderBy(['type', 'subtype'])
			->cached()
			->execute();

		foreach($combinations as $sub){
			$response['subtypes'][$sub['type']][] = $sub['subtype'];
		}

		$response['types'] = array_keys($response['subtypes']);

		// list attribute combinations
		$combinations = $this->db->select
			->from(['gw2_attribute_combinations'])
			->cached()
			->execute()
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
			->from(['gw2_items'])
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
			->execute();

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

		$lang = in_array($data['lang'], UpdaterInterface::API_LANGUAGES, true) ? $data['lang'] : 'de';

		$result = $this->db->select
			->from(['gw2_items'])
			->where('id', $data['id'])
			->limit(1)
			->cached()
			->execute();

		$item = $result[0];

		// API response JSON
		foreach(UpdaterInterface::API_LANGUAGES as $lng){
			$item['data_'.$lng] = json_decode($item['data_'.$lng]);
		}

		$icon_url = 'http://darthmaim-cdn.de/gw2treasures/icons/'.$item->signature.'/'.$item->file_id.'.png';
		$icon_api = 'https://render.guildwars2.com/file/'.$item->signature.'/'.$item->file_id.'.png';

		$chatlink = new \stdClass;
		$chatlink->id = $item->id;
		$chatlink->type = Chatlink::ITEM;
		$chatlink = $this->Chatlink->encode($chatlink);

		// ...do stuff. @todo

		$response['html'] = '
		<h3>'.$item->{'name_'.$lang}.'</h3>
		<span class="description">'.nl2br($item->{'data_'.$lang}->description ?? '').'</span>
		<h4>item id/chat code</h4>
		<input type="text" readonly="readonly" value="'.$item->id.'" class="selectable" style="width:10em;" />
		<input type="text" readonly="readonly" value="'.$chatlink.'" class="selectable" style="width:10em;" /><br />
		
		<h4>icon</h4>
		<img src="'.$icon_url.'"> <img src="'.$icon_api.'"><br />
		<span style="font-size: 70%;">(gw2treasures icons have metadata stripped)</span><br />
		<input type="text" readonly="readonly" value="'.$icon_url.'" class="selectable" /><br />
		<input type="text" readonly="readonly" value="'.$icon_api.'" class="selectable" /><br />
		
		<h4>'.file_get_contents('http://www.sloganizer.net/en/outbound.php?slogan='.$item->name_en).'</h4>
		';

		return $response;
	}

	/**
	 * @todo
	 * @param string $json
	 *
	 * @return array
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
			$chatlink = $this->Chatlink->decode($str);

			if($chatlink->type === Chatlink::ITEM){
				$ids[] = $chatlink->id;

				if(isset($chatlink->upgrades)){
					$ids = array_merge($ids, $chatlink->upgrades);
				}
			}
		}

		$result = $this->db->select
			->cols(['name' => 'name_'.$lang, 'id', 'level', 'rarity'])//, 'data' => 'data_'.$lang
			->from(['gw2_items'])
			->where('id', $ids, 'in')
			->limit(250)
			->cached()
			->execute();


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
