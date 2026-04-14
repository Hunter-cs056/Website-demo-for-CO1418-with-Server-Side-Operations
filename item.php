<?php
session_start();
require_once 'connect.php';

//Retrieve the product ID from the URL using the GET form method
$product_id= isset($_GET['id']) ? (int)$_GET['id'] : 0;

//If the product id is not valid return to the products page;
if($product_id<=0){
	header('Location: products.php');
	exit();
}

//Variables for the review submission
$review_errors=[];
$review_success= false;

//Handle review form submission before we query reviews so it appears faster
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])){
	//Inform the user that he must be logged-in to leave a review
	if(!isset($_SESSION['user_id'])){
		$review_errors[]= 'You must be logged in to post a review!';
	}
	else {
		//Trim and retrieve the review inputs
		$review_title= trim($_POST['review_title'] ?? '');
		$review_desc= trim($_POST['review_desc'] ?? '');
		$review_rating= $_POST['review_rating'] ?? '';
		
		//Server-side validation
		if($review_title === ''){
			$review_errors[]= 'Review title is required!';
		}
		if($review_desc === ''){
			$review_errors[]= 'Review description is required!';
		}
		if(!in_array($review_rating, ['1','2','3','4','5'])){
			$review_errors[]= "Please select a rating between 1 and 5!";
		}
		
		//If all validation checks are pasted, insert the new review using a prepared statement
		if(empty($review_errors)){
			$insert_stmt = mysqli_prepare($conn, "INSERT INTO tbl_reviews
			(user_id, product_id, review_title, review_desc,review_rating) VALUES (?,?,?,?,?)");
			mysqli_stmt_bind_param($insert_stmt, 'iisss', $_SESSION['user_id'], $product_id, $review_title, $review_desc, $review_rating);
			
			if(mysqli_stmt_execute($insert_stmt) && mysqli_stmt_affected_rows($insert_stmt) === 1){
				$review_success =true;
			}else {
				$review_errors[]= 'Something went wrong saving your review. Please try again';
			}
			mysqli_stmt_close($insert_stmt);
		}
	}
}

//Create a prepared statement to query the product details
$stmt= mysqli_prepare($conn, "SELECT * FROM tbl_products WHERE product_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result= mysqli_stmt_get_result($stmt);
$product= mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

//Create a prepared statement to query all reviews for the selected product along with the user table to show the reviewer's name
$reviews= [];
$avg_rating= 0;
$review_count=0;

if($product){
	$reviews_stmt= mysqli_prepare($conn, "SELECT r.review_title, r.review_desc, r.review_rating, r.review_timestamp, u.user_name
	FROM tbl_reviews r JOIN tbl_users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.review_timestamp DESC");
	mysqli_stmt_bind_param($reviews_stmt, 'i', $product_id);
	mysqli_stmt_execute($reviews_stmt);
	$reviews_result =mysqli_stmt_get_result($reviews_stmt);
	while($row= mysqli_fetch_assoc($reviews_result)){
		$reviews[] = $row;
	}
	mysqli_stmt_close($reviews_stmt);
	
	//Calculate the average rating for each item using a SQL query
	$avg_stmt= mysqli_prepare($conn,"SELECT AVG(review_rating) AS avg_rating, COUNT(*) AS review_count FROM tbl_reviews WHERE product_id = ?");
	mysqli_stmt_bind_param($avg_stmt, 'i', $product_id);
	mysqli_stmt_execute($avg_stmt);
	$avg_result= mysqli_stmt_get_result($avg_stmt);
	$avg_data= mysqli_fetch_assoc($avg_result);
	$avg_rating= $avg_data['avg_rating'] !== null ? round((float)$avg_data['avg_rating'], 1): 0;
	$review_count= (int)$avg_data['review_count'];
	mysqli_stmt_close($avg_stmt);
}	
mysqli_close($conn);

//Function to render the rating in stars
function renderStars($rating){
	$rating= (int)round($rating);
	$output= '';
	for($i = 1; $i<= 5; $i++){
		if($i <= $rating){
			$output .= '<span class="star filled">★</span>';
		}else
		{
			$output .= '<span class="star">☆</span>';
		}
	}
	return $output;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['product_title']) : 'Product Not Found'; ?></title>
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
	<div id="item-content">
		<?php if(!$product): ?>
			<!-- Product not found -->
			<div class="product-detail-card" >
				<h2>Product Not Found</h2>
				<p>The product you are looking for does not exist or has been removed.</p>
				<a href="products.php" class="view-button">Back to products</a>
			</div>
			
		<?php else:	
			//If the product exists, create a JS-safe object to addToCart
			$product_js=htmlspecialchars(json_encode([
			'id'=> (int)$product['product_id'],
			'name'=> $product['product_title'],
			'price'=> '£' . number_format($product['product_price'], 2),
			'stock'=> $product['product_stock'],
			'imgSrc'=> $product['product_src'],
			'desc'=> $product['product_desc']
			]), ENT_QUOTES);
		?>	
			<!-- Item -->
			<div class="product-detail-card" >
				<img src="<?php echo htmlspecialchars($product['product_src']); ?>" alt="<?php echo htmlspecialchars($product['product_title']); ?>">
				<h2><?php echo htmlspecialchars($product['product_title']); ?></h2>
				<p><?php echo htmlspecialchars($product['product_desc']); ?></p>
				<p><strong>£<?php echo number_format($product['product_price'], 2); ?></strong></p>
				<p class="stock-status <?php echo htmlspecialchars($product['product_stock']); ?>">
					<?php echo str_replace('-', ' ', htmlspecialchars($product['product_stock'])); ?> </p>
					
				<div class="average-rating">
					<?php if($review_count >0): ?>
					<div class="stars-display"><?php echo renderStars($avg_rating); ?></div>
					<p><strong><?php echo $avg_rating; ?> / 5</strong> based on <?php echo $review_count; ?> review<?php echo $review_count !== 1 ? 's' : ''; ?></p>
					<?php else: ?>
					<p class="no-reviews">No reviews yet. Be the first to review this product!</p>
					<?php endif; ?>
				</div>	
					
				<!-- Display an add to cart button only for logged-in users and in-stock items -->
				<?php if($product['product_stock'] !== 'out-of-stock'): ?>
					<?php if(isset($_SESSION['user_id'])): ?>
						<button onclick="addToCart(<?php echo $product_js; ?>)">Add to Cart</button>
					<?php else: ?>
						<a href="login.php" class="login-redirect">Login to add to cart</a>
					<?php endif; ?>
				<?php endif; ?>	
			</div>
			
			
			<!-- Display the reviews -->
			<div class="reviews-section">
				<h2>Customer Reviews</h2>
				<?php if(empty($reviews)): ?>
					<p class="no-reviews-msg">No reviews yet for this product.</p>
				<?php else: ?>
					<?php foreach($reviews as $r): ?>
						<div class="review-card">
							<div class="review-header">
								<h3><?php echo htmlspecialchars($r['review_title']); ?></h3>
								<div class="stars-display"><?php echo renderStars($r['review_rating']); ?></div>
							</div>
							<p class="review-meta">
								By <strong><?php echo htmlspecialchars($r['user_name']); ?></strong>
								on <?php echo date('d M Y', strtotime($r['review_timestamp'])); ?>
							</p>
							<p class="review-body"><?php echo htmlspecialchars($r['review_desc']); ?></p>
							</div> 
					<?php endforeach; ?>	
				<?php endif; ?>
			
				
				<!-- Review submission form (only for logged-in users) -->
				<?php if(isset($_SESSION['user_id'])): ?>
					<div class="review-form-container">
						<h3>Post your own review</h3>
						
						<?php if($review_success): ?>
							<div class="success-message">Review posted successfully!</div>
						<?php endif; ?>
						
						<!-- Display any review errors -->
						<?php if(!empty($review_errors)): ?>
							<div class="error-message">
								<ul>
									<?php foreach($review_errors as $err): ?>
									<li><?php echo htmlspecialchars($err); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						
						<form method="POST" action="item.php?id=<?php echo $product_id; ?>" onsubmit="return validateReviewForm()">
							<div class="form-details">
								<label for="review_title">Title:</label>
								<input type="text" id="review_title" name="review_title" required maxlength="100" placeholder="Sum up your experience">
								<span class="client-error" id="err-review-title"></span>
							</div>
						
							<div class="form-details">
								<label for="review_rating">Rating:</label>
								<select id="review_rating" name="review_rating" required>
									<option value="">-- Select rating --</option>
									<option value="5">★★★★★ (5)</option>
									<option value="4">★★★★ (4)</option>
									<option value="3">★★★ (3)</option>
									<option value="2">★★ (2)</option>
									<option value="1">★ (1)</option>
								</select>
								<span class="client-error" id="err-review-rating"></span>
							</div>
						
							<div class ="form-details">
								<label for ="review_desc">Your review:</label>
								<textarea id="review_desc" name="review_desc" rows="4" required placeholder="Share your thoughts on this product"></textarea>
								<span class="client-error" id="err-review-desc"></span>
							</div>
						
						<button type="submit" name="submit_review" class="form-button">Submit review</button>
						
						</form>	
				</div>
			<?php else: ?>	
				<p class="login-prompt"><a href="login.php">Login</a> to post a review.</p>
			<?php endif; ?>		
		</div>	
		<?php endif; ?>
	</div>
	
<!-- Inline client-side review validation -->
	<script>
		function validateReviewForm(){
			document.getElementById('err-review-title').textContent = '';
			document.getElementById('err-review-rating').textContent = '';
			document.getElementById('err-review-desc').textContent = '';
			
			const title= document.getElementById('review_title').value.trim();
			const rating =document.getElementById('review_rating').value;
			const desc =document.getElementById('review_desc').value.trim();
			
			let valid= true;
			
			if(title === ''){
				document.getElementById('err-review-title').textContent = 'Title is required.';
				valid =false;
			}
			if(rating === ''){
				document.getElementById('err-review-rating').textContent = 'Please rate the item.';
				valid =false;
			}
			if(desc === ''){
				document.getElementById('err-review-desc').textContent = 'Review text is required.';
				valid = false;
			}
			return valid;
		}
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
