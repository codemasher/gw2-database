<?php
/**
 * @filesource   functions.php
 * @created      23.02.2016
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */

namespace chillerlan\GW2DB\Helpers;


/**
 * @param array $arr1
 * @param array $arr2
 * @param bool  $identical
 *
 * @return array
 * @link http://php.net/manual/function.array-diff-assoc.php#111675
 */
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

/**
 * @param array $array
 *
 * @return array
 */
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


/**
 * @param array $flags
 * @param int   $val
 *
 * @return int
 */
function set_bitflag(array $flags, $val = 0){

	foreach($flags as $flag){
		$val = $val|$flag;
	}

	return $val;
}


/**
 * @param int $flag
 * @param int $val
 *
 * @return bool
 */
function get_bitflag($flag, $val){
	return ($val&$flag) === $flag;
}

/**
 * Check whether string is really int
 * @kink http://php.net/manual/function.is-int.php#87670
 *
 * @param $val
 *
 * @return bool
 */
function check_int(string $val){
	return (string)(int)$val === $val;
}


/**
 * Creates pagination links for an array
 * (c) Smiley
 *
 * @param int    $total          total items
 * @param int    $start          start page
 * @param int    $limit          items per page
 * @param string $request        [optional] the page request, pagenumber needs last param like http://domain.tld/index.php?blah=blub&page=
 * @param int    $firstpage      [optional] you may want to begin counting at zero or something else...
 * @param int    $adjacents      [optional]
 * @param int    $adjacents_mid  [optional]
 * @param int    $adjacents_jump [optional]
 *
 * @return array $pages (pages,total,prev,next,currentpage,links,pagination)
 */
function pagination($total, $start, $limit, $request = null, $firstpage = 1, $adjacents = 1, $adjacents_mid = 1, $adjacents_jump = 1){
	$ext = ''; // leave this empty if you don't use url-rewriting
	$limit = $limit < 1 ? 1 : $limit; //prevent division by zero
	$pages = array(
		'pages' => array(),
		'total' => ceil($total/$limit),
		'prev' => '',
		'next' => '',
		'currentpage' => '',
		'links' => '',
		'html' => ''
	);
	$lastpage = $pages['total']+($firstpage-1);
	//if first page or less
	if($start <= $firstpage){
		$currentpage = $firstpage;
		$next = $firstpage+1;
		$prev = $lastpage;
	}
	//if last page or higher
	else if($start >= $lastpage){
		$currentpage = $start;
		$next = $firstpage;
		$prev = $lastpage-1;
	}
	//if page in between
	else{
		$currentpage = $start;
		$next = $start+1;
		$prev = $start-1;
	}
	//wanna have links? no problem :D
	//no pages - no links
	if($pages['total'] < 1){
		$pages['prev'] = '';
		$pages['next'] = '';
	}
	//one single page
	else if($pages['total'] == 1){
		$pages['prev'] = '<span class="p-links p-inactive">&#171; prev</span>';
		$pages['next'] = '<span class="p-links p-inactive">next &#187;</span>';
	}
	//more than one page^^
	else{
		$pages['prev'] = '<a class="p-links p-prevnext" href="'.($request === null ? '' : $request.$prev.$ext).'" data-page="'.$prev.'">&#171; prev</a>';
		$pages['next'] = '<a class="p-links p-prevnext" href="'.($request === null ? '' : $request.$next.$ext).'" data-page="'.$next.'">next &#187;</a>';
	}
	//loop the startpoints out...
	//$i=sql startline, $j=pagenumber/arr_key
	for($i = 0, $j = $firstpage; $i < $total; $i += $limit, $j++){
		//...and build some links in between
		$pages['pages'][$j] = $i;
		$href = ($request === null ? '' : $request.$j.$ext);
		//current page
		if($j == $currentpage){
			$pages['links'] .= '<span class="p-links p-current">'.$j.'</span>';
			$pages['currentpage'] = $j;
		}
		//pages between start and current
		else if($j >= ($firstpage+$adjacents) && $j < ($currentpage-$adjacents_mid)){
			//jump-to links between start and current page
			if($j >= floor((($firstpage+$currentpage+$adjacents_mid)-$adjacents)/2)-$adjacents_jump && $j <= floor((($firstpage+$currentpage+$adjacents_mid)-$adjacents)/2)+$adjacents_jump){
				$pages['links'] .= '<a class="p-links p-middle" href="'.$href.'" data-page="'.$j.'">'.$j.'</a>';
			}
			//spaces in between - we can hide them by adding a nodisplay style ;)
#			else{
#				$pages['links'] .= '<a class="p-links p-middle hidden" href="'.$href.'" data-page="'.$j.'">'.$j.'</a>';
#			}
		}
		//pages between current and last
		else if($j > ($currentpage+$adjacents_mid) && $j <= ($lastpage-$adjacents)){
			//jump-to links between current and last page
			if($j >= ceil((($lastpage+$currentpage+$adjacents_mid)-$adjacents)/2)-$adjacents_jump && $j <= ceil((($lastpage+$currentpage+$adjacents_mid)-$adjacents)/2)+$adjacents_jump){
				$pages['links'] .= '<a class="p-links p-middle" href="'.$href.'" data-page="'.$j.'">'.$j.'</a>';
			}
			//spaces in between
#			else{
#				$pages['links'] .= '<a class="p-links p-middle hidden" href="'.$href.'" data-page="'.$j.'">'.$j.'</a>';
#			}
		}
		//first/last page & adjacents
		else{
			$pages['links'] .= '<a class="p-links" href="'.$href.'" data-page="'.$j.'">'.$j.'</a>';
		}
	}
	$pages['html'] = $pages['total'] > 1 ? $pages['prev'].$pages['links'].$pages['next'] : '';
	return $pages;
}
