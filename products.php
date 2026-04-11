<?php
session_start();
require_once 'connect.php';

//First we read the filter value from the URL, which is 'all' by defualt
$filter = $_GET['filter'] ?? 'all';

//Create an array of valid filters to prevent any SQL tampering attack
$valid_filters=['all','good-stock', 'low-stock', 'out-of-stock'];
if(!in_array($filter, $valid_filters)){
	$filter= 'all';
}

//Build the query using prepared statements based on the applied Filter 
if($filter ==='all'){
	$stmt= mysqli_prepare($conn, "SELECT * FROM tbl_products ORDER BY product_id");
}
else {
	$stmt = mysqli_prepare($conn, "SELECT * FROM tbl_products WHERE product_stock = ? ORDER BY product_id");
	mysqli_stmt_bind_param($stmt,'s',$filter);
}
mysqli_stmt_execute($stmt);
$result= mysqli_stmt_get_result($stmt);
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
	<!-- Modify our select filter area to a form select that uses the GET method so we server-side control -->
	<div class="filter-bar">
		<form method="GET" action="products.php">
			<label for="stock-filter">Filter by stock:</label>
			<select id="stock-filter" name="filter" onchange="this.form.submit()">
				<option value="all" <?php if($filter==='all') echo 'selected'; ?>>All</option>
				<option value="good-stock" <?php if($filter==='good-stock') echo 'selected'; ?>>Good stock</option>
				<option value="low-stock" <?php if($filter==='low-stock') echo 'selected'; ?>>Last few</option>
				<option value="out-of-stock"<?php if($filter==='out-of-stock') echo 'selected'; ?>>Out of stock</option>
			</select>
		</form>		
	</div>
		
	<!-- Product list rendered from database -->
	<div class="product-content">
		<div id="product-list"  class="product-container">
			<?php
			if(mysqli_num_rows($result) > 0){
				while($row =mysqli_fetch_assoc($result)){
					//Now we create a JS-safe object literal so addToCart can use it inline
					$product_js= htmlspecialchars(json_encode([
					'id' => (int)$row['product_id'],
					'name' => $row['product_title'],
					'price' => '£' . number_format($row['product_price'],2),
					'stock' => $row['product_stock'],
					'imgSrc' => $row['product_src'],
					'desc' => $row['product_desc'] ])
					,ENT_QUOTES);
					?>
				<div class="product-itself">
					<img src="<?php echo htmlspecialchars($row['product_src']); ?>" alt="<?php echo htmlspecialchars($row['product_title']); ?>">
					<h3><?php echo htmlspecialchars($row['product_title']); ?></h3>
					<p><?php echo htmlspecialchars($row['product_desc']); ?></p>
					<p><strong>£<?php echo number_format($row['product_price'], 2); ?></strong></p>
					<p class="stock-status <?php echo htmlspecialchars($row['product_stock']); ?>"><?php echo str_replace('-', ' ', htmlspecialchars($row['product_stock'])); ?></p>
					<a href="item.php?id=<?php echo (int)$row['product_id']; ?>" class="view-button">View More</a>
					<?php if($row['product_stock'] !== 'out-of-stock'): ?>
						<?php if(isset($_SESSION['user_id'])): ?>
							<button class="product-page-button" onclick="addToCart(<?php echo $product_js; ?>)">Add to Cart</button>
						<?php else: ?>
							<a href="login.php" class="product-page-button login-redirect">Login to add</a>
						<?php endif; ?>	
					<?php endif; ?>
				</div>
				<?php
				}
			}
			else {
				echo '<p>No products found for this filter!</p>';
			}
			mysqli_stmt_close($stmt);
			mysqli_close($conn);
			?>
		</div>
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
