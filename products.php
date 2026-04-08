<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
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
					<li><a href="cart.php">Cart</a></li>
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
	
	<!-- Content -->
	<div class="filter-bar">
		<label for="stock-filter">Filter by stock:</label>
		<select id="stock-filter">
			<option value="all">All</option>
			<option value="good-stock">Good stock</option>
			<option value="last-few">Last few</option>
			<option value="out-of-stock">Out of stock</option>
		</select>	
	</div>
	<div class="product-content">
			<div id="product-list"  class="product-container"></div>
	</div>	
	<!-- Back to top button -->
	<button id="back-to-top" class="back-to-top">↑ Back to top</button>
	
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
				<p>Call us: +357 24694000<p>
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
