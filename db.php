<?php

class db {
	var $table;
	var $mod;
	
	function get_current_datetime() {
		return date('Y-m-d H:i');	
	}
		
	function retrieve( $name, $sql ) {
		$query = mysql_query( $sql ) or die (mysql_error());
		if ( isset( $this->table[$name] ) ) {
			unset( $this->table[$name] );
		}
		$this->table[$name] = array();
		$n = 0;
		while ( $row = mysql_fetch_array( $query, MYSQL_ASSOC ) ) {
			$this->table[$name][$n] = $row;
			$n++;
		}
		return $this->table[$name];
	}
	
	function check_value( $value, $sql ) {
		$query = mysql_query( $sql ) or die(mysql_error());
		$row = mysql_fetch_array($query, MYSQL_ASSOC);
		return $row[$value];
	}

	function add_mod( $sql ) {
	 	$n = count( $this->mod );
	 	$this->mod[ $n ] = array();
	 	$this->mod[ $n ]["user_id"] = $_SESSION["user_id"];
	 	$this->mod[ $n ]["machine"] = $_SESSION["machine"];
	 	$this->mod[ $n ]["mod_datetime"] = $this->get_current_datetime();
	 	$this->mod[ $n ]["mod_query"] = $sql;
	}	
	
	function log_mod() {
	 	if ( count( $this->mod ) > 0 ) {
			foreach( $this->mod as $row ) {
			 	$sql = "insert into data_mod ( user_id, machine, mod_datetime, mod_query ) value ( '" . $row['user_id'] . "', '" . $row['machine'] . "', '" . $row['mod_datetime'] . "', \"" . $row['mod_query'] . "\" )";
				$query = mysql_query( $sql ) or die (mysql_error());			 	
			}			
		}
	}

	function send( $sql ) {
		$query = mysql_query( $sql ) or die (mysql_error());
		$this->add_mod( $sql );
		return $query;
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
		$this->mod = array();
	}
}

?>