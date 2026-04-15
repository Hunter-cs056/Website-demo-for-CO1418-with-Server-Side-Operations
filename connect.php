<?php
//Security headers

//Terminate error display inside the website that could expose variable names and file pathways
error_reporting(E_ALL);
ini_set('display_errors','0');
ini_set('log_errors','0');			//Will be left on 0 until the project is ready so testing can be performed

//Harden the session cookie:
//httponly -> so JavaScript cannot read the cookie(mitigates XSS session theft)
//secure-> cookie only sent over HTTPS
//samesite-> browser will not send a cookie on a cross-site POSTs(mitigates CSRF)
session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'httponly' => true,
	'secure' => true,
	'samesite' => 'Lax' ]);

//Create variables for our credentials
$host="localhost";
$username="x";
$password="x";
$database="x";

//Create connection using our credentials
$conn = mysqli_connect($host,$username,$password,$database);

//Check our connection
//Instead of using echo we use die in order to immedietly stop the execution of our script
//after printing the error message
if(!$conn){
	die("Connection failed: ".mysqli_connect_error());
}
?>
