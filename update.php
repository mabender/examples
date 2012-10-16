<?php
session_start();
$user_id = $_SESSION['user_id'];
// echo "postdata:";
// print_r( $_POST );

include 'connect.php';

$update = array();
$insert = array();
$delete = array();

$package_id = $_POST['@package_id'];
$asset_id = $_POST['@asset_id'];

foreach ( $_POST as $key => $value) {
	if ( substr( $key, 0, 1 ) != "@" ) {
		$p = strpos( $key, ":" );
		$column = substr( $key, 0, $p );
		$len = strlen( $key );
		if ( substr( $key, $len - 1, 1 ) == "*" ) {
			$key = substr( $key, 0, $len - 1 );
			$new = true;
		} else {
			$new = false;
		}
		$p = strpos( $key, "," );
		$step = substr( $key, $p + 1, $len - $p - 1 );
		if ($new ) {
			$insert[$step][$column] = $value;
		} else {
			$update[$step][$column] = $value;
		}
	} else {
		if ( substr( $key,0,7) == "@delete") {
			if ( $value == "1" ) {
				$delete[] = $key;
			}
		}
	}
}

foreach ( $delete as $value ) {
	$p = strpos( $value, "," );
	$step = substr( $value, $p + 1, $len - $p - 1 );
	$sql = "delete from asset_log where package_id = '$package_id' and asset_id = '$asset_id' and step = $step";
	echo "<br>$sql<br>";
	$result = mysql_query( $sql ) or die( mysql_error() );
	unset( $update[$step] );
	unset( $insert[$step] ); 
}

$next = 0;
foreach ( $update as $key => $row ) {
	$next++;
	$sql = "update asset_log set ";
	foreach ( $row as $xkey => $value ) {
		$sql .= " $xkey = '$value',";
	}
	$sql .= " step = $next, user_id = '$user_id'";
	$sql .= " where package_id = '$package_id' and asset_id = '$asset_id' and step = $key";
	$result = mysql_query( $sql ) or die( mysql_error() );
	echo "<br> $sql <br>";
}


foreach ( $insert as $step => $row ) {
	$next++;
	$columns = "insert into asset_log ( package_id, asset_id, step";
	$values = "'$package_id', '$asset_id', $next";
	foreach ( $row as $key => $value ) {
		$columns .= ", $key";
		$values .= ", '$value'";
	}
	$sql = $columns . ", user_id ) values ( " . $values . ", '$user_id' )";
	$result = mysql_query( $sql ) or die( mysql_error() );
	echo "<br> $sql <br>";
}

$sql = "update asset set log_count = " . $next . " where package_id = '$package_id' and asset_id = '$asset_id'";
$result = mysql_query( $sql ) or die( mysql_error() );
echo "<br> $sql <br>";

$form_id = $_POST['@form_id'];
	
include 'start.php';

?>