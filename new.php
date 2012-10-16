<?php
session_start();

include "connect.php";
include "db.php";

$db = new db();

function get_package( $col ) {
	global $db;
	
	if ( $db->table_exists("package") ) {
		return $db->get( "package", 0, $col );
	}
	return "";
}


function get_priority() {
	global $db;
	
	$hchecked = "";
	$lchecked = "";
	if ( $db->table_exists("package") ) {
		$value = $db->get( "package", 0, "priority" );
		if ( $value == 1 ) {
		 	$hchecked = "selected";
		} else {
		 	$lchecked = "selected";
		}
	}
	return "<option value='0' " . $lchecked . ">Low<option value='1' " . $hchecked . ">High";
}


function init_media( $array, $default ) {
	$media = '<option value="">';
	for ( $n = 0; $n < count( $array ); $n++ ) {
		$media .= '<option value="' . $array[$n]['media_id'] . '"';
		if ( $default == "Y" ) {
			if ( $array[$n]['default_media'] == 'Y' ) {
				$media .= ' selected';	
			}
		}
		$media .= '>' . $array[$n]['description'];
	}
	return $media;
}

function set_selected_media( $media, $value ) {
	$p = strpos( $media, "value=\"" . $value . "\"" );
	return substr_replace( $media, " selected", $p - 1, 0 );
}

if ( isset( $_POST['@delete'] ) ) {
 	$package_id = $_POST['@package_id'];
	$db->send("delete from package where package_id = " . $package_id );
	$db->send("delete from asset where package_id = " . $package_id );
	$db->send("delete from asset_log where package_id = " . $package_id );
	$db->log_mod();
	include "header.php";
	return;
	// header to search/list screen
}

if ( isset( $_POST['@open'] ) ) {
 	$package_id = $_POST['@package_id'];
	$db->send("update package set status = 'P', date_completed = null where package_id = " . $package_id );
	$db->send("update asset set status = 'P', date_completed = null where package_id = " . $package_id );
	$db->send("update asset_log set status = 'P' where package_id = " . $package_id );
	$db->log_mod();
	include "header.php";
	return;
	// header to search/list screen
}

if ( isset( $_POST['@submit'] ) ) {
	
	$package_sql = "";
	$asset = array();
	foreach( $_POST as $key => $value ) {
	 	$table = substr( $key, 0, 1);
	 	if ( $table != "@" ) {
	 	 	$key = substr( $key, 2 );
	 	 	if ( $table == "p" ) {
	 	 	 	if ( $value != "" ) {
		 	 	 	$package_sql .= $key . " = '" . $value . "', "; 
				}
			} else {
			 	$mode = substr( $key, strlen( $key ) - 2, 1 );
			 	$asset_id = substr( $key, strlen( $key ) - 1 );
			 	$p = strpos( $key, $mode );
			 	$key = substr( $key, 0, $p );
				$asset[$mode][$asset_id][$key] = $value;
			}
		}
	}
	if ( $_POST['@package_id']  == "" ) {
	 	$sql = "insert into package () values ()";
	 	$db->send( $sql );
	 	$package_id = mysql_insert_id();
	 	$package_sql .= "asset_work = 0, ";
	} else {
		$package_id = $_POST['@package_id'];
	}
	$asset_count = 0;	
	foreach ( $asset as $mode => $asset ) {
		foreach ( $asset as $id => $row ) {
		 	if ( $mode == "+" ) {
				$sql = "insert into asset ( package_id ) values ( " . $package_id . " )";
				$db->send( $sql );
				$id = mysql_insert_id();
			}
		 	$sql = "";
		 	$n = 1;
		 	foreach ( $row as $key => $value ) {
	 	 	 	$sql .= $key . " = '" . $value . "'";
	 	 	 	if ( $n < count( $row ) ) {
					$sql .= ", ";
				}
	 	 	 	$n++;
			}
			$sql = "update asset set " . $sql . " where package_id = " . $package_id . " and asset_id = " . $id;
			$db->send($sql);
			$asset_count++;
		}
	}
	$package_sql .= "status = 'P', asset_count = " . $asset_count;
	$sql = "update package set " . $package_sql . " where package_id = " . $package_id;
	$db->send( $sql );
	
	// if change to date requested and/or priority
	//    check for assigned - notify/move assignment up in users list
	// deal with assets already assigned and processing ? add asset_id & delete field to innerthml of cell
	$db->log_mod();
}

$media_assigned = $db->retrieve( "media", "select * from media_assigned" );
$media_requested = $db->retrieve( "media", "select * from media_requested" );

$asset = array();
$tblAsset = "";

if ( isset( $_REQUEST['edit_package_id'] ) ) {
	$media_a_option = init_media( $media_assigned, "N" );
	$media_r_option = init_media( $media_requested, "N" );
	
	$package_id = $_REQUEST['edit_package_id'];
	$db->retrieve( "package", "select * from package where package_id = " . $package_id );
	$asset = $db->retrieve( "asset", "select * from asset where package_id = " . $package_id . " order by asset_id asc" );
	$tblAsset = "";
	for ( $n = 0; $n < count( $asset ); $n++ ) {
		$option_assigned = set_selected_media( $media_a_option, $asset[$n]['media_id_assigned'] );
		$option_requested = set_selected_media( $media_r_option, $asset[$n]['media_id_requested'] );
		$tblAsset .= "<tr id='asset" . $n . "'><td style='width: 125px;'><input type='text' size='11' name='a:title-" . $asset[$n]['asset_id'] . "' value='" . $asset[$n]['title'] . "'>";
		$tblAsset .= "<td style='width: 115px;'><select name='a:media_id_assigned-" . $asset[$n]['asset_id'] . "'>" . $option_assigned . "<td style='width: 115px;'><select name='a:media_id_requested-" . $asset[$n]['asset_id'] . "'>" . $option_requested . "</select>";
		$tblAsset .= "<td style='width: 20px;'><img name='del$n' alt='del$n' src='del.jpg' height='15' width='15' onclick=\"remove_asset_row('asset" . $n . "');\">";
	}
} else {
 	$media_a_option = init_media( $media_assigned, "Y" );
	$tblAsset = "";
	$_SESSION['onload'] = "add_asset_row()";
}


$_SESSION['javascript'] = <<<EOD

function add_asset_row() {
	// adds a row to the asset table

	var tbl = document.getElementById("tblAsset");
	var count = document.getElementById("@count");
	
	num = parseInt( count.value ) + 1;
	count.value = num;
	
	var newRow = tbl.insertRow(-1);
	newRow.id = 'asset' + num;
	
	oCell = newRow.insertCell(-1);
	oCell.width = 125;
	oCell.innerHTML = '<input type="text" name="a:title+' + num + '" size="11">';

	oCell = newRow.insertCell(-1);
	oCell.width = 115;
	oCell.innerHTML = '<select name="a:media_id_assigned+' + num + '">{$media_a_option}</select>';

	oCell = newRow.insertCell(-1);
	oCell.width = 115;
	oCell.innerHTML = '<select name="a:media_id_requested+' + num + '">{$media_r_option}</select>';

	var oCell = newRow.insertCell(-1);
	oCell.width = 20;
	oCell.innerHTML = '<img name="del' + num + '" alt="del' + num + '" src="del.jpg" height="15" width="15" onclick="remove_asset_row(\'asset' + num + '\');" onMouseOver="style.cursor=\'hand\'">';
	//oCell.innerHTML = num;
	// add asset_id and delete form to num

}
EOD;

include "header.php";

?>

<form name="new" action="<? $_SERVER['PHP_SELF']; ?>" method="post">

<div class="contentheader">
	Title<br><br>
	Date Received<br><br>
	Date Requested<br><br>
	Owner Account<br><br>
	Priority<br><br>
</div>

<div class="leftcontent">
	<input type="text" name="p:title" size="13" value="<? echo get_package("title"); ?>"><br><br>
	<input type="text" name="p:date_received" size="13" value="<? echo get_package("date_received"); ?>"><br><br>
	<input type="date_requested" name="p:date_requested" size="13" value="<? echo get_package("date_requested"); ?>"><br><br>
	<input type="text" name="p:owner" size="13" value="<? echo get_package("owner"); ?>"><br><br>
	<select name="p:priority"><? echo get_priority(); ?></select><br><br>
</div>
<div class="rightcontent">
	<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td align="center"><input type="button" name="add" value="Add Assignment" onclick="add_asset_row();">
	</table>
	<table border="1" cellpadding="0" cellspacing="0">		
	<tr>
		<td width="125">Title</td>
		<td width="115">Med. Assigned</td>
		<td width="115">Med. Desired</td>
		<td width="20">(del)</td>
	</tr>
	</table>
	<div id="assetlist">
		<table border="1" cellpadding="0" cellspacing="0" id="tblAsset" name="tblAsset">
			<? echo $tblAsset; ?>
		</table>
	</div>
</div>
<div class="centercontent">
Notes<br>
<textarea rows="5" name="p:notes" cols="75"><? echo get_package("notes"); ?></textarea><br>
<input type="hidden" name="@package_id" id="@package_id" value="<? echo get_package("package_id"); ?>">
<input type="hidden" name="@count" id="@count" value="<? echo count($asset); ?>">
<?php
	if ( isset( $_POST['@submit'] ) ) {
		echo "Package saved successfully.<br>";
	}
?>
<input type="submit" name="@submit" value="Save">
</form>

<?

if ( isset( $_REQUEST['edit_package_id'] ) ) {
	?>

	<form name="new" action="<? $_SERVER['PHP_SELF']; ?>" method="post">
	<input type="hidden" name="@package_id" value="<? echo $_REQUEST['edit_package_id']; ?>">
	<input type="submit" name="@delete" value="Delete"><br>
	<input type="submit" name="@open" value="ReOpen">
	</form>

	<?
}
?>
</div>

<? include 'footer.php'; ?>