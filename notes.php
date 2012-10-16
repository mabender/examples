<?php
session_start();

print_r( $_POST );
include "connect.php";
include "db.php";

$db = new db();
$package_id = $_POST['package_id'];
$asset_id = $_POST['asset_id'];

$note = "";
if ( isset( $_POST['@note'] ) ) {
	$sql = "select notes from asset where package_id = " . $package_id . " and asset_id = " . $asset_id;
	$note = $db->check_value( "notes", $sql );
}

if ( isset( $_POST['@location'] ) ) {
 	if ( !isset ( $_POST['@done'] ) ) {
		if ( isset( $_POST['@submit'] ) ) {
			$sql = "update asset set notes = '" . $_POST['note'] . "', notes_set = 'Y' where package_id = " . $package_id . " and asset_id = " . $asset_id;
		}
		if ( isset( $_POST['@delete'] ) ) {
			$sql = "update asset set notes = null, notes_set = 'N' where package_id = " . $package_id . " and asset_id = " . $asset_id;
		}
		$db->send( $sql );
		$db->log_mod();
	}
	$_SESSION['mode'] = "list";
	$_SESSION['package_id'] = $package_id;	
	header( "location: " . $_POST['@location'] );
	return;
}


$_SESSION['javascript'] = <<<EOD
EOD;

$_SESSION['mode'] = "notes";
include "header.php";

?>

<center>
<form name="save_note" action="<? $_SERVER['PHP_SELF']; ?>" method="post">
<textarea name='note' cols='60' rows='12'><? echo $note; ?></textarea><br><br>
<input type='hidden' name='package_id' value='<? echo $package_id; ?>'>
<input type='hidden' name='asset_id' value='<? echo $asset_id; ?>'>
<input type='hidden' name='@location' value='list.php'>
<input type="submit" name="@done" value="Done">
<input type="submit" name="@submit" value="Save Note">
<input type="submit" name="@delete" value="Delete">
</form>

</form>
</center>
</body>
</html>