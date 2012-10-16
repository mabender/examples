<?php
session_start();

$mode = $_POST['option'];
switch( $mode ) {
	case "list":
		$href = "list.php";
		break;
	case "search":
		$href = "search.php";
		break;
	case "new":
		$href = "new.php";
		break;
	case "logout":
		$href = "index.php";
		break;
	case "admin":
		$href = "admin.php";
		break;
}

$_SESSION['mode'] = $mode;
header( "location: " . $href );

?>