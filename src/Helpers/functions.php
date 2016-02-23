<?php
/**
 * @filesource   functions.php
 * @created      23.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */


/**
 * @param array $arr1
 * @param array $arr2
 * @param bool  $identical
 *
 * @return array
 * @link http://php.net/manual/function.array-diff-assoc.php#111675
 */
if(!function_exists('')){
	function array_diff_assoc_recursive(array $arr1, array $arr2, $identical = false){
		$diff = $identical ? array_diff_key($arr2, $arr1) : [];
		foreach($arr1 as $key => $value){
			if(is_array($value)){
				if(!isset($arr2[$key]) || !is_array($arr2[$key])){
					$diff[$key] = $value;
				}
				else{
					$new_diff = array_diff_assoc_recursive($value, $arr2[$key], $identical);
					if(!empty($new_diff)){
						$diff[$key] = $new_diff;
					}
				}
			}
			else if(!array_key_exists($key, $arr2) || $arr2[$key] !== $value){
				$diff[$key] = $value;
			}
		}

		return $diff;
	}
}

/**
 * @param array $array
 *
 * @return array
 */
if(!function_exists('array_sort_recursive')){
	function array_sort_recursive(array $array){
		array_multisort($array);
		ksort($array);
		foreach($array as $key => $value){
			if(is_array($value)){
				$array[$key] = array_sort_recursive($value);
			}
		}

		return $array;
	}
}



