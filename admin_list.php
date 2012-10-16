<?php
session_start();

include "connect.php";
include "db.php";

$db = new db();

$admin_list = $db->retrieve( "form_template", "select * from form_template" );

include "header.php";

?>
<center>
<table>
<?

foreach ( $admin_list as $row ) {
	echo "<tr>";
	echo "<td><a href='admin.php?form_id=" . $row['form_id'] . "'>" . $row['form_title'] . "</a>";
}

?>

</table>
</center>

<? include 'footer.php'; ?>