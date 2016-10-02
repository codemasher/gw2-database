<?php
/**
 * Class Emblem
 *
 * @filesource   Emblem.php
 * @created      10.04.2016
 * @package      GW2Treasures\GW2Tools\Guildemblems
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Guildemblems;

/**
 * @link https://github.com/arenanet/api-cdi/pull/132
 * @link https://github.com/arenanet/api-cdi/issues/65
 * @link https://forum-en.guildwars2.com/forum/community/api/API-Suggestion-Guilds/page/2#post2155863
 */
class Emblem{

	/**
	 * Based on the script by Moturdrn.2837
	 * https://gist.github.com/moturdrn/9d03a0cd4967828ac6cc
	 *
	 * @param resource $image GD
	 * @param bool     $horizontal
	 * @param bool     $vertical
	 *
	 * @return resource $dest GD
	 */
	protected function flip($image, bool $horizontal = true, bool $vertical = false){
		$w = imagesx($image);
		$h = imagesy($image);
		$dest = imagecreatetruecolor($w, $h);

		imagesavealpha($dest, true);
		imagefill($dest, 0, 0, imagecolorallocatealpha($dest, 0, 0, 0, 127));

		if($vertical){
			for($i = 0; $i < $h; $i++){
				imagecopy($dest, $image, 0, ($h - $i - 1), 0, $i, $w, 1);
			}
		}

		if($horizontal){
			for($i = 0; $i < $w; $i++){
				imagecopy($dest, $image, ($w - $i - 1), 0, $i, 0, 1, $h);
			}
		}

		return $dest;
	}

	/**
	 * Image applyHue calculation
	 *
	 * @author Moturdrn.2837
	 * @link   https://gist.github.com/moturdrn/9d03a0cd4967828ac6cc
	 *
	 * @param resource $image image GD
	 * @param array    $hslbc the [material] array from the color API response
	 *
	 * @return void
	 */
	protected function applyHue($image, array $hslbc){
		$colorMatrix = Math::getColorMatrix($hslbc);

		$width  = imagesx($image);
		$heigth = imagesy($image);

		for($x = 0; $x < $width; $x++){
			for($y = 0; $y < $heigth; $y++){
				$ic = imagecolorsforindex($image, imagecolorat($image, $x, $y));
				if($ic['alpha'] < 127){
					$rgb = Math::applyColorTransform($colorMatrix, [$ic['red'], $ic['green'], $ic['blue']]);
					imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $rgb[0], $rgb[1], $rgb[2], $ic['alpha']));
				}
			}
		}

	}

}
