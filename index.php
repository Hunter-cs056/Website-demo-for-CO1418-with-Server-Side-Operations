<?php
session_start();
require_once 'connect.php';
require_once 'cart_helper.php';

//Get the offers query from the database
$sql="SELECT * FROM tbl_offers";
$result = mysqli_query($conn,$sql);
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
	
	<!-- Content -->
	<div class="content">
			<?php if(isset($_SESSION['user_name'])): ?>
			<div class = "welcome-banner">
				<p>Welcome back, <strong> <?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!</p>
			</div>
			<?php endif; ?>
			<h1>Where opportunity Creates Success</h1>
			
			<div class="content-text">
				<p>Every student at The University of Central Lancashire is automatically a member of the Student's Union.
				We are here to make life better for students-inspiring you to succeed and achieve your goals.</p>
			
			<p>Everything you need to know about UCLan Student's Union.Your membership starts here.</p>
			</div>
			<!-- Offers Section -->
			<h2>Limited Time Offers!</h2>
			<div class="offers-container">
				<?php
				if(mysqli_num_rows($result)>0){			//make sure the table is not empty
					while($row = mysqli_fetch_assoc($result)){
					echo '<div class="offer-card">';
					echo '<h3>' .htmlspecialchars($row['offer_title']).'</h3>';
					echo '<p>' .htmlspecialchars($row['offer_desc']).'</p>';
					echo '</div>';
					}
				}else{
					echo '<p>No current offers available</p>';
				}
				?>
			</div>
			
			
			<h2>Together</h2>
			<div class="contentVid1">
			<video  src="./video/video.mp4" title="Welcome Video"
			controls>Your browser does not support HTML5 
			</video>
			</div>
			
			<h2>Join our global Community</h2>
			
			<div class="contentVid2">
			<iframe title="vimeo-player"
			src="https://player.vimeo.com/video/1071072056?h=d4263dcc56" 
			 referrerpolicy="strict-origin-when-cross-origin" 
			allow="autoplay; fullscreen; picture-in-picture;
			clipboard-write; encrypted-media; web-share"
			allowfullscreen></iframe>
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
<?php 
//Close the database connection
mysqli_close($conn);
?>
