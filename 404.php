<?php
session_start();
require_once 'cart_helper.php';
http_response_code(404);		//Sends the proper status to make sure the browsers treats it as a "not found" page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
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
	
	<!-- Content -->
	<div class="not-found-container">
		<h1>404</h1>
		<h2>Page Not Found</h2>
		<p>Sorry, the page you are looking for does not exist or has been moved.</p>
		<p>You can head back to the homepage or browse our products instead!</p>
		<div class="not-found-actions">
			<a href="index.php" class="view-button">Back to homepage</a>
			<a href="products.php" class="view-button">Browse products</a>
		</div>
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