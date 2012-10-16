<?php
session_start();

// get package and asset id
// update asset_assignment table
// set date assigned in asset table
// show start.php in user list only mode
// jump to recently assigned

$user_id = $_SESSION['user_id'];
$package_id = $_POST['package_id'];
$asset_id = $_POST['asset_id'];

include 'connect.php';

$sql = "insert into asset_assignment ( package_id, asset_id, user_id ) values ( $package_id, $asset_id, '$user_id' )";
echo $sql;
$result = mysql_query( $sql ) or die( mysql_error() );

$now = date( 'Y-m-d H:i:s');
$sql = "update asset set date_assigned = '" . $now . "' where package_id = $package_id and asset_id = $asset_id";
echo $sql;
$result = mysql_query( $sql ) or die( mysql_error() );

$_SESSION["mode"] = "user";
$form_id = "id" . $package_id;
include 'start.php';

?>