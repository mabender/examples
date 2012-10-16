<?php

class db {
	var $table;
	
	function retrieve( $name, $sql ) {
		$query = mysql_query( $sql ) or die (mysql_error());
		$n = 0;
		while ( $row = mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$n++;
			$this->table[$name][$n] = $row;
		}
		return $this->table[$name];
	}
	
	function get( $name, $row, $column ) {
		return $this->table[$name][$row][$column];
	}
	
	function set( $name, $row, $column, $value ) {
		// sets the value in the array
	}
	
	function grab( $name ) {
		return $this->table[$name];
	}
	
	function table_exists( $name ) {
		return array_key_exists( $name, $this->table );
	}
	
	function copy() {
		return $this->table;
	}
	
	function db() {
		$this->table = array();
	}
}

?>