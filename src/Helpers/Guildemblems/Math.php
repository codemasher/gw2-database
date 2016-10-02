<?php
/**
 * Class Math
 *
 * @filesource   Math.php
 * @created      10.04.2016
 * @package      GW2Treasures\GW2Tools\Guildemblems
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers\Guildemblems;

class Math{

	// 4x4 identity matrix
	const MATRIX = [
		[1, 0, 0, 0],
		[0, 1, 0, 0],
		[0, 0, 1, 0],
		[0, 0, 0, 1]
	];

	/**
	 * Matrix-multiply
	 *
	 * @link http://sickel.net/blogg/?p=907
	 *
	 * @param array $matrixA
	 * @param array $matrixB
	 *
	 * @return array
	 * @throws \chillerlan\GW2DB\Helpers\Guildemblems\GuildemblemException
	 */
	public static function matrixMultiply(array $matrixA, array $matrixB):array {
		$rowsB = count($matrixB);

		if(count($matrixA[0]) !== $rowsB){
			throw new GuildemblemException('incompatible matrix');
		}

		$rowsA = count($matrixA);
		$colsB = count($matrixB[0]);

		$result = [];

		for($i = 0; $i < $rowsA; $i++){
			for($j = 0; $j < $colsB; $j++){
				$result[$i][$j] = 0;

				for($k = 0; $k < $rowsB; $k++){
					$result[$i][$j] += $matrixA[$i][$k] * $matrixB[$k][$j];
				}
			}
		}

		return $result;
	}

	/**
	 * Color Matrix calculation
	 *
	 * An approach to get the correct RGB values from the GW2 color API as Cliff Spradlin described on the GW2 API forums.
	 * The described function was split up in 2 functions to improve performance within long run loops e.g. image processing
	 * as suggested by Dr Ishmael.
	 *
	 * @link https://forum-en.guildwars2.com/forum/community/api/How-To-Colors-API/2148826 Cliff's second description
	 * @link https://forum-en.guildwars2.com/forum/community/api/API-Suggestion-Guilds/2155578 Dr Ishmael's suggestion
	 *
	 * @param array $hslbc the content of the arrays [cloth,leather,metal] which are returned by the API
	 *
	 * @return array matrix for calculation in applyColorTransform()
	 *
	 * @throws \chillerlan\GW2DB\Helpers\Guildemblems\GuildemblemException
	 */
	public static function getColorMatrix(array $hslbc):array {

		if(!isset($hslbc['hue'], $hslbc['saturation'], $hslbc['lightness'], $hslbc['brightness'], $hslbc['contrast'])){
			throw new GuildemblemException('invalid HSLBC data');
		}

		$hue        = ($hslbc['hue'] * M_PI) / 180;
		$saturation =  $hslbc['saturation'];
		$lightness  =  $hslbc['lightness'];
		$brightness =  $hslbc['brightness']  / 128;
		$contrast   =  $hslbc['contrast'];

		$matrix = self::MATRIX;

		// process brightness and contrast
		if($brightness !== 0 || $contrast !== 1){
			$t = 128 * (2 * $brightness + 1 - $contrast);

			$matrix = self::matrixMultiply([
				[$contrast, 0        , 0        , $t],
				[0        , $contrast, 0        , $t],
				[0        , 0        , $contrast, $t],
				[0        , 0        , 0        ,  1]
			], $matrix);
		}

		if($hue !== 0 || $saturation !== 1 || $lightness !== 1){

			// transform to HSL
			$matrix = self::matrixMultiply([
				[ 0.707107, 0       , -0.707107, 0],
				[-0.408248, 0.816497, -0.408248, 0],
				[ 0.577350, 0.577350,  0.577350, 0],
				[ 0       , 0       ,  0       , 1]
			], $matrix);

			// process adjustments
			$cosHue = cos($hue);
			$sinHue = sin($hue);

			$matrix = self::matrixMultiply([
				[ $cosHue * $saturation, $sinHue * $saturation,          0, 0],
				[-$sinHue * $saturation, $cosHue * $saturation,          0, 0],
				[                     0,                     0, $lightness, 0],
				[                     0,                     0,          0, 1]
			], $matrix);

			// transform back to RGB
			$matrix = self::matrixMultiply([
				[ 0.707107, -0.408248, 0.577350, 0],
				[ 0       ,  0.816497, 0.577350, 0],
				[-0.707107, -0.408248, 0.577350, 0],
				[ 0       ,  0       , 0       , 1]
			], $matrix);
		}

		return $matrix;
	}

	/**
	 * Apply color transform
	 *
	 * @param array $matrix the matrix returned by getColorMatrix()
	 * @param array $base   the base color provided in the API response, or calculated for the emblem colors
	 *
	 * @return array calculated RGB values
	 */
	public static function applyColorTransform(array $matrix, array $base):array {

		// apply the color transformation
		$matrix = self::matrixMultiply($matrix, [
			[$base[2]],
			[$base[1]],
			[$base[0]],
			[1]
		]);

		// clamp the values
		$clamp = function($val){
			return floor(max(0, min(255, $val)));
		};

		return [
			$clamp($matrix[2][0]),
			$clamp($matrix[1][0]),
			$clamp($matrix[0][0])
		];
	}

}
