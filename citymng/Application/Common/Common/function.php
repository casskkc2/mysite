<?php
function md5encode($str) {
	return md5($str);
}

function array_merge_sum($arr1, $arr2) {
	foreach($arr1 as $key=>$value) {
		if (isset($arr2[$key])) {
			$arr1[$key] += $arr2[$key];
		}
	}
	
	foreach($arr2 as $key=>$value) {
		if (!isset($arr1[$key])) {
			$arr1[$key] = $value;
		}
	}
	
	return $arr1;
}

function buildTree($data, $pid=',0,', $attrs=array(), $id="id", $text='name') {
	$tree = array();
	foreach($data as $key=>$row) {
		if (strcmp($row['path'], $pid) == 0) {
			$tmp = array(
				'id' => $row[$id],
				'text' => $row[$text]
			);
			if (!empty($attrs)) {
				foreach($attrs as $o) {
					if (isset($row[$o])) {
						$tmp['attributes'][$o] = $row[$o];
					}
				}
			}
			
			unset($data[$key]);
			
			$children_pid = $pid . $row['id'] . ',';
			$tmp['children'] = buildTree($data, $children_pid, $attrs, $id, $text);
			$tree[] = $tmp;
		}
	}
	
	return $tree;
}

function treePathToArray($path) {
	$tmp_arr = explode(',', $path);
	return array_filter($tmp_arr, function($v) {
		return !empty($v);
	});
}

function getSummaryColsFromTreeData(&$tree_data) {
	$n = 0;
	foreach($tree_data as $key=>$row) {
		if (empty($row['children'])) {
			$tree_data[$key]['descendant_num'] = 0;
			$n++;
		}else {
			$tree_data[$key]['descendant_num'] = getSummaryColsFromTreeData($tree_data[$key]['children']);
			$n += $tree_data[$key]['descendant_num'];
		}
	}
	
	return $n;
}

function getMaxDimensionOfTreeData($tree_data) {
	$i = 0;
	$do = false;
	$max = 0;
	foreach($tree_data as $key=>$row) {
		if (!$do) {
			$i++;
			$do = true;
		}
		if (!empty($row['children'])) {
			$j = getMaxDimensionOfTreeData($row['children']);
			if ($j > $max) $max = $j;
		}
	}
	$i += $max;
	return $i;
}

function getLeafNodesFromTreeData($tree_data) {
	$nodes = array();
	foreach($tree_data as $row) {
		if (empty($row['children'])) {
			$nodes[] = $row;
		}else {
			$tmp_nodes = getLeafNodesFromTreeData($row['children']);
			$nodes = array_merge($nodes, $tmp_nodes);
		}
	}
	return $nodes;
}