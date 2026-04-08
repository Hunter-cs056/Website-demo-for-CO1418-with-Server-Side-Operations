<?php
	session_start();
	//Upon logout empty all session variables
	$_SESSION = array();
	
	//Delete the session
	session_destroy();
	
	//Redirect to login page
	header('Location: login.php');
	exit();
?>
