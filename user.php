<?php
session_start();

$_SESSION['form' ] = 1;
include "connect.php";
include "db.php";

$db = new db();

if ( isset( $_POST['@submit'] ) ) {
 	//print_r( $_POST );
 	$form_table = $_POST['form_table'];
 	$form_key = $_POST['form_key'];
 	unset( $_POST['form_table'] );
 	unset( $_POST['form_key'] );
 	$id = "";
 	$values = array();
	foreach ( $_POST as $key => $value ) {
	 	if ( $key == "@submit" ) {
			$temp = $key;			
		} else {
			$arr = explode( "," , $key );
			$temp = $arr[0];
		}
		if ( $temp == "X" ) {
		 	$sql = "delete from " . $form_table . " where " . $form_key . " = " . $arr[1];
		 	$db->send($sql);
		 	continue;
			// delete from table where arr[1]
			// send db
			// continue
		}
		if ( $temp != $id ) {
		 	if ( count( $values ) > 0 ) {
		 	 	//print_r( $values );
				if ( $command == "insert" ) {
					$sql_values = "";
					$sql_columns = "";
					$n = 0;
					foreach( $values as $column => $data ) {
						$n++;
						$sql_columns .= $column;
						$sql_values .= "'" . $data . "'";
						if ( $n < count( $values ) ) {
							$sql_columns .= ", ";
							$sql_values .= ", ";
						}
					}
					$sql = "insert into " . $form_table . " ( " . $sql_columns . " ) values ( " . $sql_values . " )";
				} else {
					$sql = "update " . $form_table . " set ";
					$n = 0;
					foreach ( $values as $column => $data ) {
						$n++;
						$sql .= $column . " = '" . $data . "'";
						if ( $n < count( $values ) ) {
							$sql .= ", ";
						}
					}
					$sql .= " where " . $form_key . " = " . $id;
				}
				echo $sql;
				$db->send( $sql );
			}
			if ( $temp == "@submit" ) {
				continue;
			}
			$id = $temp;
			if ( $id > 0 ) {
				$command = "update";
			} else {
				$command = "insert";
			}
			unset( $values );
			$values = array();
		}
		if ( $arr[2] == "H" ) {
			continue;
		}
		$column_name = $arr[1];
		if ( $command == "insert" ) {
			$values[ $column_name ] = $value;
			continue;
		}
		if ( $arr[2] == "I" ) {
			$initial = $value;
			continue;
		}
		if ( $initial != $value ) {
			$values[ $column_name ] = $value;
		}
	}
	$db->log_mod();
}

$js = "";

function get_form_field( $id, $field, $value, $type ) {
 	$ret = array();
	
	if ( $type == 0 ) {
	 	$ret[0] = "<input type='hidden' name='$id,$field,H' value='$value'>";
		$ret[1] = "";
	} else {
	 	$ret[0] = "<input type='hidden' name='$id,$field,I' value='$value'>";
		$ret[1] = "<input type='text' name='$id,$field,A' value='$value' size='10'>";
	}
	return $ret;
	
}

function get_form( $arr, $key ) {
 	
 	$fields = array();
 	foreach ( $arr as $row ) {
		$id = $row[$key];
		foreach ( $row as $field => $value ) {
		 	if ( $field == $key or $field == "password" ) {
				$type = 0;
			} else {
				$type = 1;
			}
			$fields[ $id ][$field]  = get_form_field( $id, $field, $value, $type );
		}
	}
	return $fields;
}

$form_name = $db->check_value( "form_name", "select form_name from form_template where form_id = " . $_SESSION['form'] );
$form_table = $db->check_value( "form_table", "select form_table from form_template where form_id = " . $_SESSION['form'] );
$form_query = $db->check_value( "form_query", "select form_query from form_template where form_id = " . $_SESSION['form'] );
$form_key = $db->check_value( "form_key","select form_key from form_template where form_id = " . $_SESSION['form'] );
$column_count = $db->check_value("column_count","select column_count from form_template where form_id = " . $_SESSION['form'] );

$width = round( 600 / $column_count );

$cols = $db->retrieve( "template_form_cols", "select * from form_template_cols where form_id = " . $_SESSION['form'] );

$js = "var frm; var tbl; var newRow; var oCell; var newField;";
$js .= "frm = document.getElementById('form_" . $form_name . "' );";
$js .= "tbl = document.getElementById('table_" . $form_name . "' );";
$js .= "counter--;";
$js .= "newRow = tbl.insertRow(-1);";
$js .= "newRow.id = counter;";

foreach ( $cols as $row ) {
 	$hidden = "";
	$js .= "newField = frm.appendChild( document.createElement('input'));";
	if ( $row['input_type'] == 'H' ) {
		$js .= "newField.setAttribute('type', 'hidden' );";
		$js .= "newField.setAttribute('name', counter + '," . $row['column_name'] . ",H' );";
	} else {
		$js .= "oCell = newRow.insertCell(-1);";
		$js .= "newField.setAttribute('type', 'text' );";
		$js .= "newField.setAttribute('name', counter + '," . $row['column_name'] . ",A' );";
		$js .= "oCell.appendChild( newField );";

	}
}
$js .= "oCell = newRow.insertCell(-1);";
$js .= "oCell.innerHTML = '<a href=\"#\" onclick=\"delete_form_row(counter)\"><img src=\"del.jpg\" alt=\"delete\" border=\"0\"></a>';";

$form_data = array();
$form_data = $db->retrieve( "form_data", $form_query );

$_SESSION['css'] = "." .$form_name . "_table { width: 650px; } " . "." . $form_name . "_table.td { width: " . $width . "px; }";
	
$form = get_form( $form_data, $form_key );

$_SESSION['javascript'] = <<<EOD

var counter = 0;

function add_form_row() {
	{$js}
}

function delete_form_row(id) {
 	var row = document.getElementById( id );
 	var tbl = document.getElementById( 'table_{$form_name}' );
 	tbl.deleteRow( row.rowIndex );
 	
 	var frm = document.getElementById( 'form_{$form_name}' );
 	var newField = frm.appendChild( document.createElement('input') );
 	newField.setAttribute('type', 'hidden' );
 	newField.setAttribute('name', 'X,' + id );
}

EOD;

include "header.php";

echo "<form id='form_" . $form_name . "' name='form_" . $form_name . "' action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
echo "<input type='button' value='Add Row' name='add_row' onclick='add_form_row()'><br><br>";
echo "<table style='width: 650px' border='1'><tr>";	
foreach ( $cols as $column ) {
 	if ( $column['input_type'] != "H" ) {
		echo "<th style='width: " . $width . "px;'>" . $column['column_title'];
	}
}
echo "<th style='width: 50px;'>(rm)";
echo "</table>";
echo "<div class='listheader'>";
echo "<table id='table_" . $form_name . "' class='" . $form_name . "_table'>";
$n = 0;
$hidden = "";
foreach ( $form as $row ) {
 	echo "<tr id='" . $form_data[$n][$form_key] . "'>";
	foreach ( $cols as $column ) {
	 	if ( $column['input_type'] == "H" ) {
	 		$hidden .= $row[$column['column_name']][0];
	 	} else {
	 		echo "<td>" . $row[ $column['column_name'] ][0] . $row[ $column['column_name'] ][1];
		}
	}
	echo "<td><a href='#' onclick='delete_form_row(\"" . $form_data[$n][$form_key] . "\")'><img src='del.jpg' alt='delete' border='0'></a>" . $hidden;
	$n++;
}


?>

</table>
</div>
<input type='hidden' name='form_key' value='<? echo $form_key; ?>'>
<input type='hidden' name='form_table' value='<? echo $form_table; ?>'>
<input type="submit" name="@submit" value="save">
</form>
</center>
</body>
</html>