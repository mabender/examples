<html>
<head>
<style>

table.datasheet {
	width:100%;
}
.datasheet th {
	padding:3px;
	background-color:#ddd;
	border-top:1px solid #eef;
	border-left:1px solid #eef;
	border-right:1px solid #999;
	border-bottom:1px solid #999;
	color:#003;
	font-size:.9em;
	font-weight:bold;
}
.datasheet th {
	text-align:left;
}
.datasheet tr {
	vertical-align:top;
}
.datasheet td {
	padding:0px;
	border-right:1px solid #999;
	border-bottom:1px solid #999;
	background-color:#fff;
	font-size:.9em;
}
.datasheet td input {
	border:0px none;
	width:100%;
	height:100%;
	//width:90%;
	//height:90%;
}

</style>
<body>
<?php

$htm = <<<EOD
<form name='test' action='data.htm' method='post'>
<table id='table_user' class='datasheet' cellspacing='0' cellpadding='0'>
<tr id='1'>
<td><input type='text' name='1,user_id,A' value='mbender' size='13' /></td>
<td><input type='text' name='1,first_name,A' value='Mike' size='13' /></td>
<td><input type='text' name='1,last_name,A' value='Bender' size='13' /></td>
<td><input type='text' name='1,email,A' value='mbender@tamu.edu' size='13' /></td>
<td><input type='text' name='1,admin,A' value='Y' size='13' /></td>
</tr>
</table>
</form>
EOD;

echo $htm;
?>

</body>
</html>