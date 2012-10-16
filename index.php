<?php
session_start();

print_r ( $_POST );
if ( isset( $_POST['submit'] ) ) {
	
	$user_id = $_POST['user_id'];
	$password = $_POST['password'];
	include "connect.php";
	$sql = "select * from users where user_id = '" . $user_id . "' and password = md5('" . $password . "')";
	$query = mysql_query( $sql );
	if ($query) {
	 	echo "selected";
		$row = mysql_fetch_array( $query, MYSQL_ASSOC );
		if ($row) {
		 	echo "logged";
		 	//print_r( $_POST );
			$_SESSION['user_id'] = $row['user_id'];
			$_SESSION['mode'] = $_POST['mode'];
			$_SESSION['admin'] = $row['admin'];
			$_SESSION['machine'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			// include "db.php";
			// $db = new db();
			$location = $_SESSION['mode'] . ".php"; // based on login option
			header( "location: " . $location );
			return;
		}
	}
}

?>

<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
  <title>MediaManager Login</title>
  <link href="default.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="centercolumn">
	<form name="login" method="post" action="index.php">
	<table style="text-align: left; width: 700px" border="0" align="center">
	<tr>
		<td style="height: 50px; text-align: center; vertical-align: top;"
	 	colspan="2">
	 	<img style="width: 700px; height: 50px;" src="mm.png"></td>
	    </tr>
	<tr>
		<td style="background-color: rgb(192, 192, 192); height: 10px;" colspan="2"></td>
	<tr>
		<td>
		<table width="50%" align="center" cellspacing="3" border="0">
			<tr>
				<td colspan="2"><b>Enter your user id and password to login:<br><br></b>
			<tr>
				<td style="vertical-align: top; text-align: right;">User</td>
			    <td ><input name="user_id"></td>
		    </tr>
			<tr>
				<td style="text-align: right;">Password</td>
			    <td><input name="password" type="password"></td>
			</tr>
		</table>
	<tr>
		<td style="text-align: center;" colspan="2" rowspan="1"><br>
		<input name="mode" value="new" type="radio" checked="true">New<br>
		<input name="mode" value="list" type="radio">List<br>
		<input name="mode" value="search" type="radio">Search<br><br>
		<input name="submit" value="Login" type="submit"></td>
	</tr>
	</table>
	</form>
</div>
</body>
</html>
