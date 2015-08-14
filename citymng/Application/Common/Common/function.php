<?php
function md5encode($str) {
	return md5($str);
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