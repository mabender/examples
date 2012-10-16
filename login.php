<?php
session_start();

$user_id = $_POST['user_id'];
$password = $_POST['password'];

include "connect.php";

$sql = "select * from users where user_id = '" . $user_id . "' and password = md5('" . $password . "')";
$query = mysql_query( $sql ) or die( mysql_error());
if ($query) {
	$row = mysql_fetch_array( $query, MYSQL_ASSOC ) or die ( mysql_error() );
	echo "ok";
	if ($row) {
		$_SESSION['user_id'] = $row['user_id'];
		// grab first and last name
		$_SESSION['mode'] = "list"; // grab from login option
		$_SESSION['privilege_id'] = $row['privilege_id'];
		$_SESSION['access_id'] = $row['access_id'];
		
		include "db.php";
		$db = new db();
		$_SESSION['privileges'] = $db->retrieve( "privileges", "select * from privileges where privilege_id = " . $_SESSION['privilege_id'] );
		$_SESSION['access'] = $db->retrieve( "access", "select * from access where access_id = " . $_SESSION['access_id'] );
		$location = "new.php"; // based on login option
		// login
	} else {
		$location = "index.html";
		// relog
	}
	header( "location: " . $location );
} else {
	echo "trouble";
}

?>
