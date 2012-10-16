function toggle_row(id) {

	var tbl = document.getElementById(id);
	
/*	
    var tr = document.getElementById(id);
    var rows = document.getElementById("myTable2").rows;
    for ( n=0; n < rows.length; n++ ) {
    	rowid = rows[n].id;
    	if ( rowid.match("package_id:" + id) != null ) {
		   	if ( navigator.appName.indexOf( "Microsoft" ) > -1 ) {
		   		rows[n].style.display = 'block';
		   	} else {
		   		rows[n].style.display = 'table-row';
		   	} 
    	} else {
    		if ( rowid != "work" ) {
	    	   	rows[n].style.display = 'none';
    	   	}
   	   	}
    }
*/
}

function delete_log_row(id) {

	var ts = document.getElementById('step_' + id);
  	var td = document.getElementById('@delete_' + id);
   	if (td) {
   		ts.style.display='none';
   		td.value = '1';
  	} else {
  		ts.parentNode.deleteRow( ts.rowIndex );
   	}
}


function add_log_row(id) {
	
    var tbl = document.getElementById('asset_' + id);
    var count_form = document.getElementById('@count_' + id);
    
	var log_count = count_form.value;
    var newid = id + "," + log_count + "*";
	log_count = parseInt( log_count.value ) + 1;
	count_form.value = log_count;
	
    var newRow = tbl.insertRow(-1);
	newRow.id = 'step_' + newid;
	
    var oCell = newRow.insertCell(-1);
	oCell.innerHTML = '<input type="text" name="process:' + newid + '">';
    oCell = newRow.insertCell(-1);
	oCell.innerHTML = '<input type="text" name="date_started:' + newid + '">';
    oCell = newRow.insertCell(-1);
	oCell.innerHTML = '<input type="text" name="date_completed:' + newid + '"><img name="del' + log_count + '" id="del' + log_count + '" src="del.jpg" onclick="delete_log_row(\'' + newid + '\');">';
	    
}


function remove_asset_row(id) {
	
	var oRow = document.getElementById(id);

	document.getElementById("tblAsset").deleteRow(oRow.rowIndex);
	refresh();
}

function refresh_asset_rows() {
	
	var oRows = document.getElementById("tblAsset").rows;
	for ( n = 1; n < oRows.length; n++ ) {
		oCells = oRows[n].cells;
		oCells[0].innerHTML = n;
	}
	
}

