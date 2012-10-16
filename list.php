<?php
session_start();

include "connect.php";
include "db.php";

$db = new db();

$machine_list = array();
$machine_list = $db->retrieve( "machine", "select * from machine" );

if ( isset( $_POST['@submit'] ) ) {
	$package_id = $_POST['@package_id'];
	unset( $_POST['@package_id'] );
	unset( $_POST['@submit'] );
	$n = 0;
	$complete = array();
	$asset_started = array();
	$asset_check = "";
	$asset_count = 0;
	foreach ( $_POST as $key => $value ) {
	 	if ( substr( $key, 0, 1 ) == "@" ) {
	 	 	$complete[] .= $value;
			continue;
		}
		if ( $n == 0 ) {
			$process = $value;
			$n++;
		} elseif ( $n == 1 ) {
			$machine_id = $value;
			$n++;			
		} else {
			$temp = explode( ",", $key );
			$n = 0;
			$sql = "delete from asset_log where package_id = " . $package_id . " and asset_id = " . $temp[1] . " and log_id = " . $temp[2];
			$db->send( $sql );
			$sql = "insert into asset_log ( package_id, asset_id, log_id, process, date_modified, status, machine_id ) values ( " . $package_id . ", " . $temp[1] . ", " . $temp[2] . ", '" . $process . "', '" . $db->get_current_datetime() . "', '" . $value . "', " . $machine_id . " )";
			if ( $temp[1] != $asset_check ) {
				$asset_check = $temp[1];
				$asset_count++;
				if ( !isset( $asset_started[ $asset_check ] ) ) {
					$asset_started[ $asset_check ] = 0;
				}
				$asset_started[ $asset_check ]++;
			}			
		    $db->send( $sql );
		}
	}
	// 
	if ( count( $asset_started ) > 0 ) {
		foreach ( $asset_started as $key => $value ) {
			//$check = $db->check_value( "asset_work"
			// set date started if asset_work = 0
		}
	}
	$sql = "update package set asset_work = " . $asset_count . " where package_id = " . $package_id;
	$db->send( $sql );
	if ( count($complete) > 0 ) {
		foreach( $complete as $value ) {
			$sql = "update asset set status = 'C', date_completed = '" . $db->get_current_datetime() . "' where package_id = '" . $package_id . "' and asset_id = '" . $value . "'";
			$db->send( $sql );
		}
		$sql = "select count(*) from asset where package_id = " . $package_id . " and status <> 'C'";
		if ( $db->check_value( "count(*)", $sql ) == 0 ) {
			$sql = "update package set status = 'C', date_completed = '" . $db->get_current_datetime() . "' where package_id = '". $package_id . "'";
			$db->send( $sql );
			// form submit package_id to send.php
		}
	}
	$db->log_mod();
}

function filter_rows($array, $column, $value) {
	$ret = array();
	foreach ( $array as $row ) {
		if ( $row[$column] == $value ) {
			$ret[] = $row;
		}
	}
	return $ret;
}

function generate_search_where( $list ) {
 	global $db;
 	
 	//print_r( $list );
 	$column_type = array();
 	$column_operator = array();
 	$types = $db->retrieve("seach_types", "select * from search_types" );
 	foreach ( $types as $row ) {
		$column_type[ $row['column_name' ] ] = $row['column_type'];
		$column_operator[ $row['column_name'] ] = $row['search_operator'];
	}
 	$sql = "";
 	$i = 0;
 	$count = count( $list );
	foreach ( $list as $table_name => $values ) {
	 	$i++;
	 	$sql .= " ( ";
	 	for ( $n=0; $n < count( $values ); $n++ ) {
	 	 	switch ( $column_type[ $table_name ] ) {
				case "V":
					$sql .= $table_name . " = '" . $values[$n] . "'";
					break;
				case "C":
					$sql .= $table_name . " = '" . $values[$n] . "'";
					break;
				case "I":
					$sql .= $table_name . " = " . $values[$n];
					break;
				case "D":
					$arr = explode( ";", $values[$n] );
					$sql .=	"package." . $arr[0] . " between  '" . $arr[1] . "' and '" . $arr[2] . "'";
					break;
			}//
			if ( $n < count( $values ) - 1 ) {
				$sql .= " or ";
			}
		}
		$sql .= " ) ";
		if ( $i < $count  ) {
			$sql .= " and ";
		}
	}
	return $sql;
}

function get_package_select( $search ) {
 	if ( $search == "" ) {
		$sql_package = "select package_id, title, unix_timestamp( date_received ) as date_received, unix_timestamp( date_requested ) as date_requested, owner, notes, asset_count, priority, status, asset_count, asset_work from package where status <> 'C' order by priority desc, date_requested desc, date_received asc";	
	} else {
		$sql_package = "select distinct package.package_id, package.title, unix_timestamp( package.date_received ) as date_received, unix_timestamp( package.date_requested ) as date_requested, unix_timestamp( package.date_completed ) as date_completed, package.owner, package.asset_count, package.priority, package.status, package.asset_count, package.asset_work from package left join asset_log on package.package_id = asset_log.package_id where " . $search . " order by priority desc, date_requested desc, date_received asc";
	}
	return $sql_package;
}


function get_asset_select( $package_id ) {
	$sql_asset = "SELECT asset.package_id as package_id, asset.asset_id as asset_id,
		asset.date_completed as date_completed, asset.status as status, asset.title as title, media_a.description as media_assigned,
		media_b.description as media_requested, asset.notes_set as notes_set FROM asset
		Inner Join media_assigned AS media_a ON asset.media_id_assigned = media_a.media_id
		Inner Join media_requested AS media_b ON asset.media_id_requested = media_b.media_id
		WHERE asset.package_id = " . $package_id . "
		order by asset.package_id asc, asset.asset_id asc";
	return $sql_asset;
}

function get_log_select( $package_id, $asset_id ) {
	$sql_log = "select * from asset_log where package_id = " . $package_id . " and asset_id = " . $asset_id . " order by package_id asc, asset_id asc, log_id asc";	
	return $sql_log;
}

function create_machine_select( $name, $status, $value ) {
 	global $machine_list;
 	
 	$select = "<select name='" . $name . "'";
 	if ( $status == "C" ) {
		$select .= " disabled='disabled'";
	}
	$select .= ">";
 	foreach ( $machine_list as $row ) {
 		$select .= "<option value='" . $row['machine_id'] . "'";
		if ( $value == $row['machine_id'] ) {
			$select .= " selected";
		} 
		$select .= ">" . $row['name'];
 	}
 	$select .= "</select>";
 	return $select;
}

$_SESSION['javascript'] = <<<EOD

	var counter = new Array();
	var machine = new Array();
				
	function init( row ) {
		toggle_row( row );
	}
	
	function init_count( package, asset, log_count ) {
		var p = parseInt(package);
		var a = parseInt(asset);
		if ( typeof(counter[p]) == 'undefined' ) {
			counter[p] = new Array();
		}
		counter[p][a] = log_count;
	}
	
	function init_machine( id, name ) {
		machine[id] = name;				
	}

	function add_step(package,asset,assigned,requested,title) {
	
		var tbl = document.getElementById('package_id:' + package );
		var rowid = document.getElementById("package_id:" + package + ",asset_id:" + asset + ",addstep").rowIndex;
		var frm = document.getElementById('submit:' + package );

		var log_id = parseInt( counter[parseInt(package)][parseInt(asset)] ) + 1;
		counter[parseInt(package)][parseInt(asset)] = log_id;

		var newRow = tbl.insertRow(rowid);
		newRow.id =  'package_id:' + package + ',asset_id:' + asset + ',log_id:'+ log_id;
		
		// oCell = newRow.insertCell(-1);
		// oCell.innerHTML = title;
		
		// oCell = newRow.insertCell(-1);
		// oCell.innerHTML = '(' + assigned + ' -> ' + requested + ')';
	
		oCell = newRow.insertCell(-1);
		oCell.innerHTML = log_id;
	
		var oCell = newRow.insertCell(-1);
		
		var newField = frm.appendChild( document.createElement('input'));

		newField.setAttribute('type', 'text' );
		newField.setAttribute('name', 'p,' + asset + ',' + log_id   );
		
		oCell.appendChild( newField );

		var oCell = newRow.insertCell(-1);

		var newField = frm.appendChild( document.createElement('select'));

		newField.setAttribute('name', 'm,' + asset+ ',' + log_id );

		for ( i in machine ) {
			newField.options[newField.options.length] = new Option( machine[i] , i );
		}		

		oCell.appendChild( newField );

		var oCell = newRow.insertCell(-1);

		var newField = frm.appendChild( document.createElement('select'));

		newField.setAttribute('name', 's,' + asset + ',' + log_id   );
		// newField.setAttribute('value', 'P' );
		
		newField.options[newField.options.length] = new Option('P','P' );
		newField.options[newField.options.length] = new Option('C','C' );

		oCell.appendChild( newField );

	}

	function toggle_row(id) {
		
		for ( n=0; n < counter.length; n++ ) {
			if ( typeof( counter[n] ) != "undefined" ) {
				tbl = document.getElementById('package_id:' + n );
				if ( n != id ) {
					tbl.style.display = 'none';
				} else {
					tbl.style.display = 'block';
				}	
			}	
		}	
	}

	function view_notes(package,asset) {
	 	
	 	var frm = document.getElementById('package_id:' + package + ',asset_id:' + asset + ',notes' );
	 	frm.submit();
		// open window wih vars as post?		
		// store each note form in a var and post at end of package form
		// have button with onclick to submit each form
	}

EOD;

if ( !isset( $_SESSION['package_id'] ) ) {
	$_SESSION['package_id'] = 0;
}


$_SESSION['onload'] = "init('" . $_SESSION['package_id'] . "')";
$_SESSION['package_id'] = 0;

include "header.php";

?>
<div class="bottomlist" style="height: 150px">
<?

foreach ( $machine_list as $row ) {
	echo "<script language='javascript'>init_machine('" . $row['machine_id'] . "','" . $row['name'] . "');</script>";				
}

if ( isset( $_SESSION['search'] ) ) {
	$search = generate_search_where( $_SESSION['search'] ); 
	unset( $_SESSION['search'] );
} else {
	$search = "";
}

$package = array();
$package = $db->retrieve( "package", get_package_select( $search ) );

$notes_form = "";
foreach ( $package as $row ) {
	$package_id = $row['package_id'];
	
	echo "<form id='submit:" . $package_id . "' name='submit:" .$package_id . "' method='post' action='list.php'>";
	echo "<table id='package_id:" . $package_id . "' cellspacing='0' cellpadding='0' align='center'>";
	$asset = $db->retrieve( "asset", get_asset_select( $package_id ) );
	for ( $n = 0; $n < count( $asset ); $n++ ) {
		$asset_id = $asset[$n]['asset_id'];
		if ( $asset[$n]['status'] == 'C' ) {
			$disabled = " disabled='true'";
		} else {
			$disabled = "";
		}
		echo "<tr><th>Step</th><th>Process</th><th>Machine</th><th>Status</th>";
		$log = $db->retrieve( "log", get_log_select( $package_id, $asset_id ) );
		foreach( $log as $row2 ) {
			$log_id = $row2['log_id'];
			if ( $row2['status'] != "C" ) {
				$select = "<option selected value='P'>P</option><option value='C'>C</option>";
			} else {
				$select = "<option value='P'>P</option><option selected value='C'>C</option>";
			}
			echo "<tr id='package_id:" . $package_id . ",asset_id:" . $asset_id . ",log_id:" . $log_id . "'><td>" . $log_id . "<td><input " . $disabled . " type='text' name='p," . $asset_id . "," . $log_id . "' value='" . $row2['process'] . "'><td>" . create_machine_select( "m," . $asset_id . "," . $log_id , $asset[$n]['status'], $row2['machine_id'] ) . "<td><select name='s," . $asset_id . "," . $log_id . "', '" . $asset[$n]['status'] . "' " . $disabled . ">" . $select . "</select>";			
		}
		echo "<script language='javascript'>init_count('" . $package_id . "','" . $asset_id . "','" . count( $log ) . "');</script>";
 	 	echo "<tr id='package_id:" . $package_id . ",asset_id:" . $asset_id . ",addstep' class='titleline'><td>" . $asset[$n]['title'] . " (" . $asset[$n]['media_assigned'] . " -> " . $asset[$n]['media_requested'] . " )";
		if ( $asset[$n]['status'] != 'C' ) {
			echo "<td colspan='1'><input type='button' name='addstep' value='Add Step' onclick=\"add_step('" . $package_id . "','" . $asset_id . "', '" . $asset[$n]['media_assigned'] . "', '" . $asset[$n]['media_requested'] . "', '" . $asset[$n]['title'] . "')\">";
			echo "<td colspan='1'><b>Conversion complete?</b><input type='checkbox' name='@complete," . $asset_id . "' value='" . $asset_id . "'>";
			if ( $asset[$n]['notes_set'] == 'Y' ) {
				$notes = " <img src='postit.png' alt='notes'>";
			} else {
				$notes = "";
			}
			echo "<td colspan='1'><input type='button' name='notes' value='Notes' onclick=\"view_notes('" . $package_id . "', '" . $asset_id . "')\">" . $notes;
			$notes_form .= "<form id='package_id:" . $package_id . ",asset_id:" . $asset_id . ",notes' action='notes.php' method='post'><input type='hidden' name='package_id' value='" . $package_id . "'><input type='hidden' name='asset_id' value='" . $asset_id . "'><input type='hidden' name='@note' value='@note'></form>";
		}
		if ( $n < count ( $asset ) ) {
			echo "<tr id='package_id:" . $package_id . ",asset_id:" . $asset_id . ",hr1'><td colspan='4' height='10' style='background-color:#ffffff;'></tr>";
		}
		
	}
	if ( $row['status'] != 'C' ) {
		echo "<tr id='package_id:" . $package_id . ",asset_id:" . $asset_id . ",submit'><td colspan='6'><input type='submit' name='@submit' value='submit'><input type='hidden' name='@package_id' value='" . $package_id . "'>";
	}
	echo "</table></form>" . $notes_form;	
}

?>
</div>

<div class="bottomlist" style="height:350	px">
<table class="tablepackage">
	<tr>
	<th>Package</th>
		<th>Received</th>
		<th>Requested</th>
		<? if ( $search != "" ) { echo "<th>Completed</th>"; } ?>
		<th>Owner</th>
		<th>Active/Total</th>
		<th>Priority</th>
	</tr>
	<?php
		for ( $n = 0; $n < count( $package ); $n++ ) {
			$row = $package[ $n ];
			$flag = 0;
			if ( $row['priority'] == 1 ) {
				$row_class = "hp";
			} elseif ( $row['date_requested'] != "" ) {
				$row_class = "dr";
			} else {
				$row_class = "reg";
			}
			echo "<tr class='" . $row_class . "'>";
			echo "<td><span style='float: left'><a href='#' onclick=\"toggle_row('" . $row['package_id'] . "')\">" . $row['title'] . "</a></span><span style='float: right'><a href='new.php?edit_package_id=" . $row['package_id'] . "' class='editlink'><img alt='(edit)' src='edit.png' border='0'></a></span>";
			echo "<td>" . date( 'm/d/y', $row['date_received'] );
			echo "<td>"; 
			if ( $row['date_requested'] != "" ) {
				echo date( 'm/d/y', $row['date_requested'] );
			}
			if ( $search != "" ) {
			 	echo "<td>";
				if ( $row['date_completed'] != "" ) {
					echo date( 'm/d/y', $row['date_completed'] );
				}
			}
			if ( !$row['asset_work'] ) {
				$flag = "<font color='#ff0000'><b>";
			} else {
				$flag = "";
			}
			echo "<td>" . $row['owner'];
			echo "<td>" . $flag . $row['asset_work'] . "/" . $row['asset_count']; // red or bold for assets without work
			echo "<td>" . $row['priority'];
			echo "</tr>";
		}
	?>
</table>
</div>

<?php
	include "footer.php";
	mysql_close( $link );
?>
