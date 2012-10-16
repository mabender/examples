<?php
	
	$user = "root";
	// $user = "mbender";
	$password = "asnasna";
	// $host = "virt-mysql.vpr.int";
	$host = "localhost";
	$database = "mediamanager";
	
	$link = mysql_connect( $host, $user, $password) or die( mysql_error() );
	mysql_select_db($database) or die ("no db");
?>