<?php
/**
 *
 * @filesource   Chatlink.php
 * @created      05.10.2016
 * @package      chillerlan\GW2DB\Helpers\Chatlinks
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Chatlinks;

use stdClass;

/**
 * Class Chatlink
 */
class Chatlink{

	const COIN   = 0x01;
	const ITEM   = 0x02;
	const TEXT   = 0x03;
	const MAP    = 0x04;
	const SKILL  = 0x07;
	const TRAIT  = 0x08;
	const RECIPE = 0x0A;
	const SKIN   = 0x0B;
	const OUTFIT = 0x0C;

	const UPGRADE_NONE   = 0x00;
	const UPGRADE_1      = 0x40;
	const UPGRADE_2      = 0x60;
	const UPGRADE_SKIN   = 0x80;
	const UPGRADE_SKIN_1 = 0xC0;
	const UPGRADE_SKIN_2 = 0xE0;

	/**
	 * @param array $flags
	 *
	 * @param int   $val
	 *
	 * @return int
	 */
	public function set_bitflag(array $flags, int $val = 0): int{

		foreach($flags as $flag){
			$val = $val|constant('self::'.$flag);
		}

		return $val;
	}

	/**
	 * @param int $flag
	 * @param int $val
	 *
	 * @return bool
	 */
	public function get_bitflag($flag, $val): bool{
		return $val&$flag === $flag;
	}

	/**
	 *
	 * @param \stdClass $data
	 *
	 * @return string
	 * @author {@link https://twitter.com/poke poke}
	 * @link   http://wiki.guildwars2.com/wiki/Widget:Game_link
	 */
	public function encode(stdClass $data): string{
		$out     = [$data->type];
		$ids     = [$data->id];
		$upgrade = self::UPGRADE_NONE;

		if($data->type === self::ITEM){
			$out[] = isset($data->count) && !empty($data->count) ? $data->count : 1;

			if(isset($data->skin) && !empty($data->skin)){
				$ids[] = $data->skin;
				$upgrade = $this->set_bitflag(['UPGRADE_SKIN'], $upgrade);
			}

			if(isset($data->upgrades) && is_array($data->upgrades) && !empty($data->upgrades)){
				$ids = array_merge($ids, $data->upgrades);
				$upgrade = $this->set_bitflag(['UPGRADE_'.count($data->upgrades)], $upgrade);
			}
		}

		foreach($ids as $k => $id){
			$octets = [];

			while($id > 0){
				$octets[] = $id&255;
				$id = $id >> 8;
			}

			while(count($octets) < 3){
				$octets[] = 0;
			}

			$octets[] = $k === 0 ? $upgrade : 0;
			$out = array_merge($out, $octets);
		}

		$out = array_map(function ($ascii){
			return chr($ascii);
		}, $out);

		return '[&'.base64_encode(implode($out)).']';
	}

	/**
	 * @param string $chatlink
	 *
	 * @return \stdClass
	 * @throws \chillerlan\GW2DB\Helpers\Chatlinks\ChatlinkException
	 *
	 * @author {@link https://twitter.com/poke poke}
	 * @link   http://ideone.com/0RSpAA
	 */
	public function decode(string $chatlink): stdClass{

		if(preg_match('/\[&(?P<base64>[a-z\d+\/]+=*)\]/i', $chatlink, $match) > 0){
			$chars = str_split(base64_decode($match['base64']));

			if(!is_array($chars)){
				throw new ChatlinkException('invalid chatlink');
			}

			$octets = [];

			foreach($chars as $char){
				$octets[] = ord($char);
			}

			$out = new stdClass;

			$out->in   = $chatlink;
			$out->type = array_shift($octets);
			$skinned = false;

			// special treatment for item codes
			if($out->type === self::ITEM){
				$out->count = array_shift($octets);
				$skinned = $this->get_bitflag(self::UPGRADE_SKIN, $octets[3]);
				var_dump([self::UPGRADE_SKIN, $octets[3], $skinned]);
			}

			// read the id chunks
			foreach(array_chunk($octets, 4) as $k => $chunk){

				if(count($chunk) === 4){
					$id = $chunk[2] << 16|$chunk[1] << 8|$chunk[0];

					if($k === 0){
						$out->id = $id;
					}

					if($out->type === self::ITEM){

						if($k === 1 && $skinned){
							$out->skin = $id;
						}
						else if(($k > 0 && $k < 3 && !$skinned) || ($k > 1 && $k < 4 && $skinned)){
							$out->upgrades[] = $id;
						}
						else{
							var_dump($id);
						}
					}
				}
			}
			return $out;
		}

		throw new ChatlinkException('invalid chatlink');
	}

}
