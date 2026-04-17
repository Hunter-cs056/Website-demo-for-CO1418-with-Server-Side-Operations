<?php
session_start();
require_once 'connect.php';
require_once 'cart_helper.php';

//Check if we are already logged-in and redirect to homepage if we are
if(isset($_SESSION['user_id'])){
	header('Location: index.php');
	exit();
}

//Initialise variables to use later on
$errors= [];
$name='';
$email='';
$address='';

//Upon submit check the Form
if($_SERVER['REQUEST_METHOD']=== 'POST'){
	//First read and trim the inputs
	$name =trim($_POST['name'] ?? '');
	$email =trim($_POST['email'] ?? '');
	$password= $_POST['password'] ?? '';
	$confirm_password = $_POST['confirm_password'] ?? '';
	$address = trim($_POST['address'] ?? '');	
	
	
	//Server-side validation
	//Validate the name entered to exist and be atleast 2 characters
	if($name === ''){
		$errors[]= 'Name is required.';
	}elseif(strlen($name)< 2){
		$errors[]= 'Name must be atleast 2 characters.';
	}
	
	//Validate that the email entered to exist and be valid
	if($email === ''){
		$errors[]= 'Email is required.';		
	}elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		$errors[]= 'A valid email is required.';
	}
	
	//Validate a strong password, the requirements will be:8+ characters, 1number, 1uppercase letter
	if(strlen($password)< 8){
		$errors[]= 'Password must be at least 8 characters long.';
	} elseif(!preg_match('/[0-9]/', $password)){
		$errors[]= 'Password must contain at least one number.';
	} elseif(!preg_match('/[A-Z]/', $password)){
		$errors[]= 'Password must contain at least one uppercase letter.';
	}	
	//Check if the password entered is matching the confirmed password entered
	if($password !== $confirm_password){
		$errors[] ='Passwords do not match.';
	}
	
	//Validate that the address entered exists
	if($address === ''){
		$errors[] = 'Address is required.';
	}
	
	//Now we will use prepared statement to check if the email is unique
	if(empty($errors)){
		$check_stmt= mysqli_prepare($conn, "SELECT user_id FROM tbl_users WHERE user_email= ? LIMIT 1");
		mysqli_stmt_bind_param($check_stmt, 's', $email);
		mysqli_stmt_execute($check_stmt);
		mysqli_stmt_store_result($check_stmt);
		
		//If there already is a user with that email we advise the user to login
		if(mysqli_stmt_num_rows($check_stmt) >0){
			$errors[]='This email is already registered. Please login instead.';
		}
		mysqli_stmt_close($check_stmt);
	}
	
	//Add the new user if no errors appear and if it passed the previous validation checks
	if(empty($errors)){
		$hashed_password= password_hash($password, PASSWORD_BCRYPT);	//Hash the password using bcrypt
		
		//Insert the hashed pasword along with the other user info, into the table using a prepared statement
		$insert_stmt = mysqli_prepare($conn,"INSERT INTO tbl_users (user_name, user_email, user_pass, user_address) VALUES (?, ?, ?, ?)");
		mysqli_stmt_bind_param($insert_stmt,'ssss',$name,$email,$hashed_password,$address);
		
		//Verify that the account got inserted into the DB
		if(mysqli_stmt_execute($insert_stmt)){
			//Verify successful record creation by checking affected rows
			if(mysqli_stmt_affected_rows($insert_stmt) === 1){
				mysqli_stmt_close($insert_stmt);
				mysqli_close($conn);
				//Redirect to login page with success flag
				header('Location: login.php?registered=1');
				exit();
			}
			else{
				$errors[] = 'Something went wrong creating the account.';
			}
		}else {
			$errors[] = 'Database error: could not register account.';
		}
		mysqli_stmt_close($insert_stmt);
	}
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
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
					<li><a href="login.php">Login</a></li>
				</ul>
				<div class="hamburger">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>
        </div>
    </nav>
	
	<!-- Register Form -->
	<div class= "form-container">
		<h2>Create your account</h2>
		
		<!-- Display server-side errors -->
		<?php if(!empty($errors)): ?>
		<div class ="error-message">
			<ul> 
				<?php foreach($errors as $err): ?>
					<li><?php echo htmlspecialchars($err); ?></li>
				<?php endforeach;	?>
			</ul>	
		</div>
		<?php endif; ?>
		
		<!-- Display the registration form's inputs -->
		<form method="POST" action="register.php" id="register-form" onsubmit="return validateRegisterForm()">
		
			<!-- Get the username -->
			<div class="form-details">
				<label for="name">Full Name:</label>
				<input type="text" id="name" name="name" required minlength="2"
				value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter your name">
				<span class="client-error" id="err-name"></span>
			</div>
		
			<!-- Get the email -->		
			<div class="form-details">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" required
				value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email">
				<span class="client-error" id="err-email"></span>
			</div>
		
			<!-- Get the password -->		
			<div class="form-details">
				<label for="password">Password:</label>
				<input type="password" id="password" name="password" required minlength="8"
				placeholder="Atleast 8 chars,1 number,1uppercase">
				<span class="client-error" id="err-password"></span>
				<div id="password-strength"></div>
			</div>		
		
			<!-- Confirm the password -->		
			<div class="form-details">
				<label for="confirm_password">Confirm password:</label>
				<input type="password" id="confirm_password" name="confirm_password" required 
				placeholder="Re-enter your password">
				<span class="client-error" id="err-confirm"></span>
			</div>			
		
			<!-- Get the address -->		
			<div class="form-details">
				<label for="address">Address:</label>
				<input type="text" id="address" name="address" required 
				value="<?php echo htmlspecialchars($address); ?>" placeholder="Enter your address">
				<span class="client-error" id="err-address"></span>
			</div>			
		
			<button type="submit" class="form-button">Register</button>	
		</form>
		<!-- Display a link so the user with an account can avoid the sign-up -->
		<p class="form-link">Already have an account? <a href="login.php">Login here</a></p>
	</div>
	
	<!-- Inline client-side validation script -->
	<script>
	function validateRegisterForm(){
		//First clear previous errors
		document.getElementById('err-name').textContent = ''; 
		document.getElementById('err-email').textContent = '';
		document.getElementById('err-password').textContent = '';
		document.getElementById('err-confirm').textContent = '';
		document.getElementById('err-address').textContent = '';
		
		const name = document.getElementById('name').value.trim();
		const email = document.getElementById('email').value.trim();
		const password = document.getElementById('password').value;
		const confirm = document.getElementById('confirm_password').value;
		const address = document.getElementById('address').value.trim();
		
		//We assume valid until proven otherwise
		let valid= true;
		
		//Check the length of the name to be atleast of length 2
		if(name.length <2){
			document.getElementById('err-name').textContent='Name must be atleast 2 characters long!';
			valid = false;
		}
	
		//Verify that the email is valid
		if(email.indexOf('@') === -1 || email.indexOf('.') === -1 ){
			document.getElementById('err-email').textContent='Please enter a valid email!';
			valid = false;
		}	
	
		//Check if the password is alteast of length 8 characters
		if(password.length < 8 || !/[0-9]/.test(password) || !/[A-Z]/.test(password)){
			document.getElementById('err-password').textContent='Password must contain 8+ chars including a number and an uppercase letter!';
			valid = false;
		}		
	
		//Check that the password and the confirm-password match
		if(password !== confirm){
			document.getElementById('err-confirm').textContent='Passwords do not match!';
			valid = false;
		}			
	
		//Verify that an address is entered
		if(address === ''){
			document.getElementById('err-address').textContent='Address is required!';
			valid = false;
		}			
	
		return valid;
	}
	
	//Live password strength indicator
	document.addEventListener('DOMContentLoaded', function(){
		const passwrd = document.getElementById('password');
		const indicator= document.getElementById('password-strength');
		if(passwrd && indicator){
			passwrd.addEventListener('keyup',function(){
				const val = passwrd.value;
				let score = 0;
				//Increase the score whenever extra security is added
				if(val.length >= 8)
				{
					score++;
				}
				if(/[0-9]/.test(val))
				{
				score++;
				}
				if(/[A-Z]/.test(val)){
					score++;
				}
				//Consider the score to display the password's strength
				if(val.length === 0){
					indicator.textContent = '';
				}
				else if(score === 1){
					indicator.textContent = 'Strength: Weak';
					indicator.style.color ='red';
				}
				else if(score=== 2){
					indicator.textContent = 'Strength: Medium';
					indicator.style.color = 'orange';
				}
				else {
					indicator.textContent ='Strength: Strong';
					indicator.style.color ='green';
				}				
				
			});
		}
	});
	
	</script>
	
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
</body>
</html>	
