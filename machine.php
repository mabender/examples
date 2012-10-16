<?php
session_start();

include "connect.php";
include "db.php";

$db = new db();

function filter_rows($array, $column, $value) {
	$ret = array();
	foreach ( $array as $row ) {
		if ( $row[$column] == $value ) {
			$ret[] = $row;
		}
	}
	return $ret;
}


// $sql_package = "select package_id, title, unix_timestamp( date_received ) as date_received, unix_timestamp( date_requested ) as date_requested, owner_account, notes, asset_count, priority, assigned, status from package where date_completed is null order by priority desc, date_requested desc, date_received asc";
$sql_asset = "SELECT asset.package_id as package_id, asset.asset_id as asset_id, asset.date_assigned as date_assigned, asset.date_completed as date_completed, media_a.description as media_requested, media_b.description as media_received, asset.log_count as log_count FROM asset
		  	Inner Join media AS media_a ON asset.media_id_requested = media_a.media_id
		  	Inner Join media AS media_b ON asset.media_id_received = media_b.media_id";
$sql_log = "select * from asset_log order by package_id asc, asset_id asc, log_id asc";

$asset = $db->retrieve( "asset", $sql_asset );
$log = $db->retrieve( "log", $sql_log );
$package = $db->retrieve( "package", $sql_package );

$_SESSION['javascript'] = <<<EOD
/************************************************************************************************************
	(C) www.dhtmlgoodies.com, November 2005
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/	
	var arrayOfRolloverClasses = new Array();
	var arrayOfClickClasses = new Array();
	var activeRow = false;
	var activeRowClickArray = new Array();
	
	function highlightTableRow()
	{
		var tableObj = this.parentNode;
		if(tableObj.tagName!='TABLE')tableObj = tableObj.parentNode;

		if(this!=activeRow){
			this.setAttribute('origCl',this.className);
			this.origCl = this.className;
		}
		this.className = arrayOfRolloverClasses[tableObj.id];
		
		activeRow = this;
		
	}
	
	function clickOnTableRow()
	{
		var tableObj = this.parentNode;
		if(tableObj.tagName!='TABLE')tableObj = tableObj.parentNode;		
		
		if(activeRowClickArray[tableObj.id] && this!=activeRowClickArray[tableObj.id]){
				activeRowClickArray[tableObj.id].className= this.origCl;
		}
		this.className = arrayOfClickClasses[tableObj.id];
		
		activeRowClickArray[tableObj.id] = this;
				
	}
	
	function resetRowStyle()
	{
		var tableObj = this.parentNode;
		if(tableObj.tagName!='TABLE')tableObj = tableObj.parentNode;

		if(activeRowClickArray[tableObj.id] && this==activeRowClickArray[tableObj.id]){
			this.className = arrayOfClickClasses[tableObj.id];
			return;	
		}
		
		var origCl = this.getAttribute('origCl');
		if(!origCl)origCl = this.origCl;
		this.className=origCl;
		
	}
		
	function addTableRolloverEffect(tableId,whichClass,whichClassOnClick)
	{
		arrayOfRolloverClasses[tableId] = whichClass;
		arrayOfClickClasses[tableId] = whichClassOnClick;
		
		var tableObj = document.getElementById(tableId);
		var tBody = tableObj.getElementsByTagName('TBODY');
		if(tBody){
			var rows = tBody[0].getElementsByTagName('TR');
		}else{
			var rows = tableObj.getElementsByTagName('TR');
		}
		for(var no=0;no<rows.length;no++){
			rows[no].onmouseover = highlightTableRow;
			rows[no].onmouseout = resetRowStyle;
			
			if(whichClassOnClick){
				rows[no].onclick = clickOnTableRow;	
			}
		}
		
	}
	
	function init() {
		addTableRolloverEffect('myTable','tableRollOverEffect1','tableRowClickEffect1');
	}
EOD;

$_SESSION['onload'] = "init()";

include "header.php";

?>

<div id="listheader">
<table>
	<tr>
		<th>Asset</th>
		<th>Process</th>
		<th>Machine</th>
		<th>Status</th>
	</tr>
</table>
</div>

<div class="toplist">
<table class="listtable" id="myTable2">
<?

/// hide all rows when package clicked, then show current row

foreach ( $log as $row ) {
	echo "<tr id='package:" . $row["package_id"] . "," . $row["asset_id"] . "," . $row["log_id"] . "' style='display:none;'><td>" . $row['asset_id'] . $add . "<td>" . $row['process'] . "<td>" . $row['machine'] . "<td>" . $row['status'];
	// out row as hidden	
}

?>
</table>
</div>

<div id="listheader">
<table>
	<tr>
		<th>Package</th>
		<th>Received</th>
		<th>Requested</th>
		<th>Media</th>
		<th>Desired</th>
		<th>Owner</th>
		<th>Priority</th>
	</tr>
</table>
</div>


<div class="bottomlist">
<table class="listtable" id="myTable">
				<?php
					// click to show asset work info 

				
					for ( $n = 0; $n < count( $package ); $n++ ) {
						$row = $package[ $n ];
						$td = "<td>"; // style='background-color: $bgcolor'>";
						echo "<tr class='tableOrigBg'>";
						echo "<td><a href='#' onclick=\"toggle_row('" . $row['package_id'] . "')\">" . $row['title'] . "</a>";
						echo $td . date( 'm/d/y', $row['date_received'] );
						echo $td; 
						if ( $row['date_requested'] != "" ) {
							echo date( 'm/d/y', $row['date_requested'] );
						}
						echo $td; //. $row['media_received'];
						echo $td; //. $row['media_requested'];
						echo $td . $row['owner_account'];
						echo $td . $row['priority'];
						echo "</tr>";
					}
				?>
			</table>
</div>
<?php
	include "footer.php";
	mysql_close( $link );
?>
