<?php
/**
 *
 * @filesource   CreateDB.php
 * @created      25.02.2016
 * @package      chillerlan\GW2DB\Updaters\Items
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Updaters\Items;

use chillerlan\GW2DB\Updaters\UpdaterBase;
use chillerlan\GW2DB\Updaters\UpdaterInterface;

/**
 * Class CreateDB
 */
class CreateDB extends UpdaterBase implements UpdaterInterface{
	const ITEM_TEMP_TABLE = 'gw2_items_temp';
	const ITEM_TABLE      = 'items_gw2treasures';

	protected $temp_items = [];
	protected $old_items  = [];

	public function init(){

		$this->old_items = $this->GW2MySQLiDriver->raw('SELECT `id`, `data_de`, `data_en`, `data_es`, `data_fr`, `data_zh` FROM '.self::ITEM_TEMP_TABLE, 'id');

		var_dump($this->old_items);
	}

}
