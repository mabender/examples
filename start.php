<?php
session_start();

include "connect.php";
include "header.php";

$user_id = $_SESSION['user_id'];
if ( !$user_id ) {
	$user_id = "mbender";
}

if ( isset( $_POST['option'] ) ) {
	$_SESSION['mode'] = $_POST['option'];
}
$mode = $_SESSION['mode'];
if (!$mode) {
	$mode = "list";
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

define( "priority_bg", "#FF999E");
define( "requested_bg", "#FFD9D9");
define( "standard_bg", "#F3F3F3");


if ($mode == "user") {
	$sql_package = "select distinct package.package_id as package_id, package.title as title, unix_timestamp( package.date_received ) as date_received, unix_timestamp( package.date_requested ) as date_requested, package.owner_account as owner_account, package.notes as notes, package.asset_count as asset_count, package.priority as priority from package, asset_assignment where package.package_id = asset_assignment.package_id and asset_assignment.user_id = '" . $user_id . "' and package.date_completed is null order by package.priority desc, package.date_requested desc, package.date_received asc";	
	$sql_asset = "SELECT asset.package_id as package_id, asset.asset_id as asset_id, asset.date_assigned as date_assigned, asset.date_completed as date_completed, media_a.description as media_requested, media_b.description as media_received, asset.log_count as log_count FROM asset_assignment, asset  
			  	Inner Join media AS media_a ON asset.media_id_requested = media_a.media_id
			  	Inner Join media AS media_b ON asset.media_id_received = media_b.media_id
				where asset.package_id = asset_assignment.package_id and asset.asset_id = asset_assignment.asset_id and asset_assignment.user_id = '" . $user_id . "'";
	$sql_log = "select * from asset_log order by step asc";
} else {
	$sql_package = "select package_id, title, unix_timestamp( date_received ) as date_received, unix_timestamp( date_requested ) as date_requested, owner_account, notes, asset_count, priority from package where date_completed is null order by priority desc, date_requested desc, date_received asc";
	$sql_asset = "SELECT asset.package_id as package_id, asset.asset_id as asset_id, asset.date_assigned as date_assigned, asset.date_completed as date_completed, media_a.description as media_requested, media_b.description as media_received, asset.log_count as log_count FROM asset
			  	Inner Join media AS media_a ON asset.media_id_requested = media_a.media_id
			  	Inner Join media AS media_b ON asset.media_id_received = media_b.media_id";
	$sql_log = "select * from asset_log order by step asc";
	$sql_assigned = "select * from asset_assignment";
	$query_assigned = mysql_query( $sql_assigned ) or die( mysql_error() );
	$assigned = array();
	while ( $row = mysql_fetch_array( $query_assigned, MYSQL_ASSOC ) ) {
		$assigned[] = $row;
	}
}


$query_package = mysql_query( $sql_package ) or die( mysql_error() );
$queue = array();
$query_asset = mysql_query( $sql_asset ) or die (mysql_error());
$asset = array();
$query_log = mysql_query( $sql_log ) or die (mysql_error());
$log = array();

$temp = array();
$process = array();

while ( $row = mysql_fetch_array( $query_asset, MYSQL_ASSOC ) ) {
	$asset[] = $row;
}

while ( $row = mysql_fetch_array( $query_log, MYSQL_ASSOC ) ) {
	$log[] = $row;
}

while ( $row = mysql_fetch_array( $query_package, MYSQL_ASSOC ) ) {
	if ( $row ) {
		if ( $row['asset_count'] == 1 ) {
			$temp = filter_rows( $asset, "package_id", $row['package_id'] );
			$row['media_received'] = $temp[0]['media_received'];
			$row['media_requested'] = $temp[0]['media_requested'];
		} else {
			$row['media_received'] = "multiple";
			$row['media_requested'] = "multiple";
		}
	}
	$queue[] = $row;
}

if ( isset( $_SESSION['form_id'] ) ) {
	echo "<body onLoad=\"showHide('" . $_SESION['form_id'] . "');\">";
} else {
	echo "<body>";
}

?>

<center>
<table style="text-align: left; width: 850px;" border="1" cellpadding="0" cellspacing="0">
	<tr>
		<td style="background-color: #8C0000; height: 75px;" colspan="1"><span class="pagetitle">MediaManager: </span>
			<span class="radio">
				<?php
					$uchecked = "";
					$lchecked = "";
					$nchecked = "";
					switch ($mode) {
						case "user":
							$uchecked = "checked='checked' ";
							break;
						case "list":
							$lchecked = "checked='checked' ";
							break;
						case "new":
							$nchecked = "checked='checked' ";
							break;
					}
					echo "<form name='menu' method='post' action='menu.php'>";
					echo '<input ' . $uchecked . ' name="option" value="user" type="radio" onclick="this.form.submit();">User List';
					echo '<input ' . $lchecked . ' name="option" value="list" type="radio" onclick="this.form.submit();">Package List';
					echo '<input ' . $nchecked . ' name="option" value="new" type="radio" onclick="this.form.submit();">New Package';
					echo '<input name="option" value="Logout" type="radio">Logout';
					echo '</form>';
				?>
			</span>
		</td>
    <tr>
		<td colspan="1" style="background-color: rgb(192, 192, 192); height: 10px;">
    </tr>
    <tr>
		<td colspan="1" style="background-color: rgb(255,255,255);" rowspan="1">
			<center>
			<table style="text-align: left; width: 850px;">
				<tr>
					<th>PACKAGE</th>
					<th>RECEIVED</th>
					<th>REQUESTED</th>
					<th>MEDIA</th>
					<th>DESIRED</th>
					<th>OWNER</th>
					<th>PRIORITY</th>
				</tr>
			</table>
			<div style="overflow-y: scroll; overflow-x: none; width: 850px; height: 650px;">
			<table border="0" style="text-align: left; width: 850px;">
				<?php
					for ( $n = 0; $n < count( $queue ); $n++ ) {
						$row = $queue[ $n ];
						if ( $row['priority'] != 0 ) {
							$bgcolor = priority_bg;
						} elseif ( $row['date_requested'] != "" ) {
							$bgcolor = requested_bg;
						} else {
							$bgcolor = standard_bg;
						}
						$td = "<td>"; // style='background-color: $bgcolor'>";
						echo "<tr bgcolor=\"" . $bgcolor . "\">";
						// echo $td . "<a onclick=\"return showHide('id$n');\">" .$row['title'] . "</a>";
						echo $td . "<input name='expand' alt='expand' src='exp2.jpg' height='11' type='image' width='7' onclick=\"return showHide('id" . $row['package_id'] . "');\">" . " " .$row['title'];
						echo $td . date( 'm/d/y', $row['date_received'] );
						echo $td; 
						if ( $row['date_requested'] != "" ) {
							echo date( 'm/d/y', $row['date_requested'] );
						}
						echo $td . $row['media_received'];
						echo $td . $row['media_requested'];
						echo $td . $row['owner_account'];
						echo $td . $row['priority'];
						echo "<tr id='id" . $row['package_id'] . "' style='display:none;'><td colspan='7'><table border='0' width='850px' cellpadding='5' cellspacing='5'>";
						$temp = filter_rows( $asset, "package_id", $row['package_id'] );
						for ( $i = 0; $i < count( $temp ); $i++ ) {
							echo "<tr>";
							echo "<td valign='top' style='width: 100px; background-color: #dddddd;'><b>From: </b>" . $temp[$i]['media_received'];
							echo "<br><b>To: </b>" . $temp[$i]['media_requested'];
							if ( $temp[$i]['date_completed'] == "" ) {
								if ( $mode != "user") {
									if ( !filter_rows( filter_rows( $assigned, "package_id", $row['package_id'] ), "asset_id", $temp[$i]['asset_id'] ) ) {
										echo "<br><br><center><form name='assign_'" . $n . "_" . $i . "' method='post' action='assign.php'><input type='hidden' name='package_id' value='" . $row['package_id'] . "'><input type='hidden' name='asset_id' value='" . $temp[$i]['asset_id'] . "'><input name='assign' value='Assign' type='submit'></form></center>";
									}
								} else {
									if ($temp[$i]['log_count'] > 0 ) {
										echo "<br><br><center><form name='release_'" . $n . "_" . $i . "' method='post' action='release.php'><input type='hidden' name='package_id' value='" . $row['package_id'] . "'><input type='hidden' name='asset_id' value='" . $temp[$i]['asset_id'] . "'><input name='release' value='Release' type='submit'></form></center>";
									}
								}
							}
							echo "<td valign='top' style='width: 750px; background-color: #dddddd;'>";
							if ( $temp[$i]['date_assigned'] != "" ) {
								if ( $temp[$i]['date_completed'] != "" ) {
									echo "<b>Process: completed " . $temp[$i]['date_completed'] . "</b>";
								} else {
									echo "<b>Process: assigned " . $temp[$i]['date_assigned'] . "</b>";
								}
								echo "<br><form name='update_" . $n . "_" . $i . "' action='update.php' method='post'>";
								echo "<table style='width: 700px; background-color: #eeeeee;' cellspacing='3' id='asset_" . $n . "_" . $i . "'>";
								echo "<tr><td>Process<td>Date Started<td>Date Complete";
								$process = filter_rows( filter_rows( $log, "package_id", $row['package_id']), "asset_id", $temp[$i]['asset_id']);
								if ( $process ) {
									for ( $p = 0; $p < count( $process); $p++ ) {
										$step = $process[$p]['step'];
										echo "<tr id='step_" .$n . "_" . $i . "," . $step ."'><td><input name='process:" .$n . "_" . $i . "," . $step ."' value='" . $process[$p]['process'] . "'>";
										echo "<td><input name='date_started:" . $n . "_" . $i . "," . $step . "'  value='" . $process[$p]['date_started'] . "'>";
										echo "<td><input name='date_completed:" . $n. "_" . $i . "," . $step . "' value='" . $process[$p]['date_completed'] . "'>";
										echo "<img name='del$step' alt='del$step' src='del.jpg' height='15' width='15' onclick=\"Del('" .$n . "_" . $i . "," . $step . "');\" onMouseOver=\"style.cursor='hand'\"><input id='@delete_". $n . "_" . $i . "," . $step . "' name='@delete_". $n . "_" . $i . "," . $step . "' value='0' type='hidden'>";
									}
								}
								echo "<input id='@form_id' name='@form_id' value='id" . $row['package_id'] . "' type='hidden'>";
								echo "<input id='@count_" . $n . "_" . $i . "' name='@count_" . $n . "_" . $i . "' value='" . count( $process ). "' type='hidden'>";
								echo "<input id='@package_id' name='@package_id' value='" . $row['package_id'] . "' type='hidden'>";
								echo "<input id='@asset_id' name='@asset_id' value='" . $temp[$i]['asset_id'] . "' type='hidden'>";									
								echo "</table><br><input name='add' value='Add Step' type='button' onclick=\"addStep('" . $n . "_" . $i . "');\">";
								echo "<br><center><input name='@submit_" . $n . "_" . $i . "' value='Update' type='submit'></center></form>";
							} else {
								echo "<b>Process: unassigned</b>";
							}
						}
						if ( $row['notes'] ) {
							echo "<tr><td colspan='2' style='background-color: #545454; color: #ffffff;'><i>* " . $row['notes'];
						}
						echo "</table></tr>";
					}
				?>
			</table>
			</div>
			</center>
		</td>
    </tr>
<?php
	include "footer.php";
	mysql_close( $link );
	unset( $queue );
	unset( $asset );
	unset( $log );
	unset( $assigned );
	unset( $temp );
	unset( $process );
?>
