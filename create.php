<?php
session_start();
$user_id = $_SESSION['user_id'];
?>

<html>
<head>
<title></title>
<style type="text/css">

body {
	background-color: #ffffff; 
}
th {
}

td {
}

.lefttable {
}
</style>
<?php

// if modify - load values; set flag
// include "connect.php";


?>
</head>
<body>
<form name="create" method="post" action="create.php">
<table style="text-align: left; width: 850px;" border="0" cellpadding="0" cellspacing="0">
	<tr>
    	<td style="vertical-align: top;">
      		<table style="text-align: left; width: 350px;" border="0" cellpadding="2" cellspacing="2">
		        <tr>
		            <td>Title:</td>
		            <td><input name="title" value=""></td>
		        </tr>
		        <tr>
        		    <td>Date Received:</td>
		            <td><input name="date_received" value=""></td>
	            </tr>
		        <tr>
            		<td>Date Requested:</td>
		            <td><input name="date_requested" value=""></td>
	            </tr>
          		<tr>
		            <td>Owner:</td>
            		<td><input name="owner_account" value=""></td>
	            </tr>
			</table>
		</td>
      	<td style="text-align: left; vertical-align: top;">
      	</td>
  </tr>
</table>
</form>
</body>
</html>
