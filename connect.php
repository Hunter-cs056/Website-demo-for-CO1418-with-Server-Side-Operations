<?php
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
