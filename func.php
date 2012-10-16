<?php

function filter_rows($array, $column, $value) {
	$ret = array();
	foreach ( $array as $row ) {
		if ( $row[$column] == $value ) {
			$ret[] = $row;
		}
	}
	return $ret;
}

?>