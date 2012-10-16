<?php
session_start();

include "connect.php";
include "db.php";

$db = new db();

function generate_input_search( $id, $table_name ) {

$title = ucfirst( $id );
$html = <<<EOH
<div class='searchcontent'>
{$title}<br>
<input id='@{$id}_select' name='@{$id}_select' type='text' size='10'>
<input name="add_{$id}" value="Add" type="button" onclick="add_value_from_input('{$id}')"><br>
<textarea readonly='readonly' name='@{$id}' id='@{$id}' cols='10' rows='3'></textarea>
<input type='hidden' id='*{$id}' name='*{$id}' value='{$table_name}'>
<input type='hidden' id='{$id}' name='{$id}'>
<br><br>
</div>
EOH;

return $html;

}

function generate_select_search( $id, $table_name, $count, $text, $value ) {

$title = ucfirst( $id );

$html = "<div class='searchcontent'>" . $title . "<br><select id='@{$id}_select' name='@{$id}_select'>";

for ( $n=0; $n < $count; $n++ ) {
	$option = "<option value='" . $value[$n] . "'>" . $text[$n] . "</option>";
	$html .= $option;
}

$html .= <<<EOH
</select>
<input name="add_{$id}" value="Add" type="button" onclick="add_value_from_select('{$id}')"><br>
<textarea readonly='readonly' name='@{$id}' id='@{$id}' cols='20' rows='3'></textarea>
<input type='hidden' id='*{$id}' name='*{$id}' value='{$table_name}'>
<input type='hidden' id='{$id}' name='{$id}'>
<br><br>
</div>
EOH;

return $html;

}

function generate_date_search( $id, $table_name, $count, $text, $value ) {

$title = ucfirst( $id );
$html = "<div class='searchcontent'>" . $title . "<br><select id='@{$id}_select' name='@{$id}_select'>";
for ( $n=0; $n < $count; $n++ ) {
	$option = "<option value='" . $value[$n] . "'>" . $text[$n] . "</option>";
	$html .= $option;
}

$html .= <<<EOH
</select>
From <input id='@{$id}_from' name='@{$id}_from' type='text' size='10'> To <input id='@{$id}_to' name='@{$id}_to' type='text' size='10'>
<input name="add_{$id}" value="Add" type="button" onclick="add_value_from_dates('{$id}')"><br>
<textarea readonly='readonly' name='@{$id}' id='@{$id}' cols='40' rows='3'></textarea>
<input type='hidden' id='*{$id}' name='*{$id}' value='{$table_name}'>
<input type='hidden' id='{$id}' name='{$id}'>
<br><br>
</div>
EOH;

return $html;

}


if ( isset( $_POST['@submit'] ) ) {
	//print_r( $_POST );
	$table = array();
	foreach ( $_POST as $key => $field )	 {
		if ( substr( $key, 0, 1) == "@" ) {
			continue;
		}
		echo $field . "<br>";
		if ( $field == "" ) {
			continue;
		}
		if ( substr( $key, 0, 1 ) == "*" ) {
			$table_id = $field . "." . substr( $key, 1, strlen( $key ) - 1 );
		 	if ( !isset( $table[ $table_id ] ) ) {
				$table[ $table_id ] = array();
			}
		}
		$list = explode(",",$field);
		for ( $n = 0; $n < ( count($list) - 1 ); $n++ ) {
			$table[ $table_id ][] .= $list[$n];
		}
		if ( count( $table[ $table_id ] ) == 0 ) {
			unset( $table[ $table_id ] );
		}
	}
	$_SESSION['mode'] = "list";
	$_SESSION['search'] = $table;
	header( "location: list.php" );
	return;

}

$_SESSION['javascript'] = <<<EOD
function add_value_from_select( column ) {
 	
 	var field = document.getElementById( "@" + column + "_select");
	var value = field.options[field.selectedIndex].value;
	var text = field.options[field.selectedIndex].text;
	
	field = document.getElementById('@' + column );
	var newvalue = field.value + text + "\\r\\n";
	field.value = newvalue;
	
	field = document.getElementById( column );
	newvalue = field.value + value + ",";
	field.value = newvalue;
}

function add_value_from_input( column ) {
 	
 	var field = document.getElementById( "@" + column + "_select");
	var value = field.value;
	
	field = document.getElementById('@' + column );
	var newvalue = field.value + value + "\\r\\n";
	field.value = newvalue;
	
	field = document.getElementById( column );
	newvalue = field.value + value + ",";
	field.value = newvalue;
}

function add_value_from_dates( column ) {

 	var field = document.getElementById( "@" + column + "_select");
	var date_value = field.options[field.selectedIndex].value;
	var date_text = field.options[field.selectedIndex].text;
	
	field = document.getElementById('@' + column + '_from');
	var from_date = field.value;

	field = document.getElementById('@' + column + '_to');
	var to_date = field.value;

	field = document.getElementById( '@' + column );
	newvalue = date_text + " (" + from_date + ' to ' + to_date + ' )';
	field.value = newvalue;
	
	field = document.getElementById( column );
	newvalue = date_value + ";" + from_date + ';' + to_date + ',';
	field.value = newvalue;
 
 	 	
}

EOD;

include "header.php";

?>
<form name='search' action='search.php' method='post'>
<?

echo generate_input_search("title", "package");
echo generate_input_search("owner", "package");
echo generate_input_search("process", "asset_log");

$text = array( "Processing", "Completed" );
$value = array( "P", "C" );
echo generate_select_search("status", "package", 2, $text, $value );

$text = array( "Low", "High" );
$value = array( "0", "1" );
echo generate_select_search("priority", "package", 2, $text, $value );

$text = array();
$value = array();
$machine = $db->retrieve( "machine", "select * from machine" );
foreach( $machine as $row ) {
	$text[] .= $row['name'];
	$value[] .= $row['machine_id'];
}
echo generate_select_search("machine_id", "asset_log", count( $machine ), $text, $value );

$value = array( "date_received", "date_requested", "date_completed" );
$text = array( "Received", "Requested", "Completed" );
echo generate_date_search( "date", "package", 3, $text, $value );

?>

<div class='searchcontent'>
<input type="submit" name="@submit" value="Search">
<input type="reset" name="@reset" value="Reset">
</div>
</form>
<? include "footer.php"; ?>