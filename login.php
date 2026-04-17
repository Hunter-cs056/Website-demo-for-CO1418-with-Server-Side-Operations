<?php
session_start();
require_once 'connect.php';
require_once 'cart_helper.php';

//Check if the user is already logged in and redirect him to the homepage
if(isset($_SESSION['user_id'])){
	header('Location: index.php');
	exit();
}
//Create an empty error variable to use later to prevent undefined-variable warning on a fresh GET
$error='';
$email = '';

//Form submission proccess
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	$email= trim($_POST['email']?? '');		// '??' operator is simplier than an if statement
	$password= $_POST['password']?? '';		//we use it on both cases to check if they exist otherwise an empty string is returned
	
	//Validate the input
	if ($email === '' || $password === ''){
		$error = 'Please fill-in both fields';
	}else {
		//Now we will use a prepared statement to prevent SQL injection
		$stmt = mysqli_prepare($conn, "SELECT user_id, user_name, user_pass FROM tbl_users WHERE user_email= ? LIMIT 1");
		mysqli_stmt_bind_param($stmt, 's', $email);	//safely attach email as a string to the ? placeholder created above
		mysqli_stmt_execute($stmt);	//Sends both the prepared and bound queries to MySQL
		mysqli_stmt_store_result($stmt); //Store the result
		//Verify that the user exists
		if (mysqli_stmt_num_rows($stmt) ===1){
			mysqli_stmt_bind_result($stmt, $user_id, $user_name, $hashed_password); //Bind the results to declared variables
			mysqli_stmt_fetch($stmt);	//Assign the declared variables to hold the actual data
			
			//Verify the password against the encrypted password
			if(password_verify($password,$hashed_password)){
				//Regenerate sessionID for security(our session is not fixed)
				session_regenerate_id(true);
				
				//Store user information inside our session
				$_SESSION['user_id']=$user_id;
				$_SESSION['user_name']=$user_name;
				$_SESSION['user_email']=$email;
				
				mysqli_stmt_close($stmt);
				mysqli_close($conn);
				
				//Redirect to homepage
				header('Location: index.php');
				exit();
			}else {
				$error='Invalid email or password';
			}
		}else 
		{
			$error='Invalid email or password';
		}
		mysqli_stmt_close($stmt);
	}
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.css">
	
</head>
<body>
	
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
			<div class="left-side">
				<div class="logo"><img src="./images/logo_reverse.png" alt="UCLan logo"></div>
				<div class="HeaderText"><h2>Student Shop</h2></div>
		    </div>	
		    <div class="right-side">
				<ul class="nav-menu">
					<li><a href="index.php">Home</a></li>
					<li><a href="products.php">Products</a></li>
					<li><a href="cart.php">Cart <?php echo getCartBadge(); ?></a></li>
					<?php if(isset($_SESSION['user_id'])):?>
						<li><a href="logout.php">Logout</a></li>
					<?php else: ?>
						<li><a href="login.php">Login</a></li>
					<?php endif; ?>	
				</ul>
				<div class="hamburger">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>
			
        </div>
    </nav>
	
	<!-- Login Form -->
	<div class="form-container">
		<h2>Login to your account</h2>
		
		<!-- Show a  success message after user successful registration -->
		<?php if(isset($_GET['registered']) && $_GET['registered'] === '1'): ?>
			<div class="success-message">
				Account created successfully!Please log in.
			</div>
		<?php endif;?>
		
		<!--Check for and display any error -->
		<?php if ($error !== ''): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif;?>
		
		<!--Display email input -->
		<form method="POST" action="login.php">
			<div class="form-details">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? '');?>"
				placeholder="Enter your email">
			</div>
			
		<!--Display password input -->
		<div class="form-details">
			<label for="password">Password:</label>
			<input type="password" id ="password" name="password" required placeholder="Enter your password">
		</div>
		
		<!--Display a submit/login button-->
		<button type="submit" class="form-button">Login</button>
		</form>
		
		<!--Display a sign-up button-->
		<p class="form-link">Dont have an account? <a href="register.php">Register here</a></p>
	</div>

    <!-- Footer -->
    <footer>
	<div class="container">
			
            <div class="links">
				<h3>Links</h3>
				<p><a href="https://www.lancashiresu.co.uk/">Student's Union</a></p>
			</div>
			<div class="links">
				<h3>Contact</h3>
				<p><a href="mailto:info@uclancyprus.ac.cy">info@uclancyprus.ac.cy</a></p>
				<p>Call us: +357 24694000</p>
			</div>
			<div class="links">
				<h3>Location</h3>
				<p>12–14 University Avenue Pyla, 7080 Larnaka, Cyprus</p>
			</div>
	</div>		
    </footer>
	<script src="myScript.js"></script>
</body>
</html>	
