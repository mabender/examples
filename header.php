<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title></title>
	<link href="default.css" rel="stylesheet" type="text/css" />
	<script src="func.js"></script>
<?php 

if ( isset( $_SESSION['javascript'] ) ) {
	echo "<script type=\"text/javascript\">" . $_SESSION['javascript'] . "</script>";
	unset( $_SESSION['javascript'] );
}

if ( isset( $_SESSION['css'] ) ) {
	echo "<style type='text/css'>" . $_SESSION['css'] . "</style>";
	unset( $_SESSION['css'] );
}

?>
</head>

<?php

if ( isset( $_POST['option'] ) ) {
	$_SESSION['mode'] = $_POST['option'];
}

if ( isset( $_SESSION['onload'] ) ) {
 	echo "<body onload=\"" . $_SESSION['onload'] . ";\">";
 	unset( $_SESSION['onload'] );
} else {
	echo "<body>";
}

?>

<div id="container">

	<div id="logo">
	</div>

	<div id="menu">
	<?php
	
	function set_option( $name ) {
	 	if ( $_SESSION['mode'] == "notes" ) {
			return "disabled='disabled'";
		}
		if ( $name == $_SESSION['mode'] ) {
			return "checked='checked'";
		}
		return "";
	}

	?>
	
	<form name='menu' method='post' action='menu.php'>
	<input <? echo set_option('new'); ?> name="option" value="new" type="radio" onclick="this.form.submit();">New
	<input <? echo set_option('list'); ?> name="option" value="list" type="radio" onclick="this.form.submit();">List
	<input <? echo set_option('search'); ?> name="option" value="search" type="radio" onclick="this.form.submit();">Search
	<?
	
	if ( $_SESSION['admin'] == "Y" ) {
		echo "<input " . set_option('admin') . " name='option' value='admin' type='radio' onclick='this.form.submit();'>Admin";
	}
	
	?>
	<input <? echo set_option('logout'); ?> name="option" value="logout" type="radio" onclick="this.form.submit();">Logout
	
	</form>
		
	</div>
	<hr style='color: #000266;background-color: #000266;height: 8px;'>