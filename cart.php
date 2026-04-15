<?php
session_start();
require_once 'cart_helper.php';
require_once 'connect.php';

//Read the cart cookie
$cart= getCart();
$cart_total=0;
$cart_items= [];

//Check for any flash message left by cart_actions.php and then clear it 
$flash_message= $_SESSION['cart_message'] ?? '';
$flash_type=$_SESSION['cart_message_type'] ?? '';
unset($_SESSION['cart_message'], $_SESSION['cart_message_type']);


//If the cart is not empty, query the datavase for each product(a signle query witht IN clause)
if(!empty($cart)){
	//Now we will create  safe IN clausse - cast all keys to int so we dont take in raw cookie values
	$ids= array_map('intval', array_keys($cart));	
	$placeholders =implode(',', array_fill(0, count($ids), '?'));	
	$types=str_repeat('i', count($ids));	
	$stmt =mysqli_prepare($conn, "SELECT * FROM tbl_products WHERE product_id IN ($placeholders)");	
	mysqli_stmt_bind_param($stmt, $types, ...$ids);
	mysqli_stmt_execute($stmt);	
	$result= mysqli_stmt_get_result($stmt);	
	
	while($row = mysqli_fetch_assoc($result)){
		$pid = (int)$row['product_id'];
		$qty = (int)$cart[$pid];
		$subtotal =(float)$row['product_price'] * $qty;
		$cart_total +=$subtotal;
		$row['quantity'] = $qty;
		$row['subtotal']= $subtotal;
		$cart_items[] = $row;
	}
	mysqli_stmt_close($stmt);	
}

//Re-validate any applied discount code on every render(this is done to prevent manimupated cookies bypassing the offers table check)
$applied_discount= validateDiscountCode($conn, getDiscountCode());
$discount_amount = 0;
if($applied_discount && $cart_total > 0){
	$discount_amount = $cart_total * ($applied_discount['discount_pct'] / 100);
}
$grand_total = $cart_total - $discount_amount;

mysqli_close($conn);
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
	<div id="cart-container">
		<h2 id='Cart-title'>Shopping Cart</h2>
		
		<!-- Display any flash message left by cart_actions.php -->
		<?php if($flash_message !== ''): ?>
			<div class="<?php echo $flash_type === 'success' ? 'success-message' : 'error-message'; ?>" style="max-width:800px;margin:1em auto;">
				<?php echo htmlspecialchars($flash_message); ?>
			</div>
		<?php endif; ?>
		
		<!-- If the cart is empty display a text and a link to redirect the user to continue shopping -->
		<?php if(empty($cart_items)): ?>
		<p id="empty-cart">Your cart is empty</p>
		<div style="text-align:center;margin-top:1em;">
			<a href="products.php" class="view-button">Continue shopping</a>
		</div>
		<!-- If the cart is not empty display the cart items -->
		<?php else: ?>		
		<div class="cart-header">
			<span>Item</span>
			<span>Name</span>
			<span>Price</span>
			<span>Quantity</span>
			<span></span>
		</div>
		<div id="cart-items">
			<?php foreach($cart_items as $item): ?>
				<div class="cart-row">
					<div class="cart-cell">
						<img src="<?php echo htmlspecialchars($item['product_src']); ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>" class="cart-image">
					</div>
					
					<div class="cart-cell">
						<span><?php echo htmlspecialchars($item['product_title']); ?></span><br>
						<a href="item.php?id=<?php echo (int)$item['product_id']; ?>" class="view-button">View More</a>
					</div>
					
					<div class="cart-cell">
						<span>£<?php echo number_format($item['product_price'], 2); ?></span>
					</div>
					
					<div class="cart-cell">
						<form method="POST" action="cart_actions.php" style="display:inline;">
							<input type="hidden" name="action" value="update">
							<input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
							<input type="number" name="quantity" class="qty-input" min="1" value="<?php echo $item['quantity']; ?>" aria-label="Quantity for <?php echo htmlspecialchars($item['product_title']); ?>">
							<button type="submit" class="qty-btn">Update</button>
						</form>
					</div>
					
					<div class="cart-cell">
						<form method="POST" action="cart_actions.php" style="display:inline;">
							<input type="hidden" name="action" value="remove">
							<input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
							<button type="submit" class="remove-btn">Remove</button>
						</form>
					</div>
					
				</div>
			<?php endforeach; ?>
		</div>
		
		<!-- Discount code area -->
		<div class="cart-discount">
			<?php if($applied_discount): ?>
				<!-- If a code is currently applied — show it with a remove button -->
				<div class="discount-applied">
					<span>✓ Code <strong><?php echo htmlspecialchars($applied_discount['code']); ?></strong> applied (<?php echo (int)$applied_discount['discount_pct']; ?>% off)</span>
					<form method="POST" action="cart_actions.php" style="display:inline;">
						<input type= "hidden" name="action" value="remove_code">
						<button type="submit" class="discount-remove-btn">Remove</button>
					</form>	
				</div>
				<?php else: ?>
					<!-- If no code is applied — show the input form -->
					<form method="POST" action="cart_actions.php" class="discount-form">
						<input type="hidden" name="action" value="apply_code">
						<input type="text" id="code-input" name="discount_code" placeholder="Enter offer code" maxlength="50" required>
						<button type="submit" id="discount-button">Apply</button>
					</form>
				<?php endif; ?>	
		</div>
		
		<!-- Total price breakdown -->
		<div class="cart-totals">
			<div class="cart-actions-left">
				<form method="POST" action="cart_actions.php" style="display:inline;">
					<input type="hidden" name="action" value="empty">
					<button type="submit" id="empty-cart-buttton">Empty basket</button>
				</form>
			</div>
			
			<div class="cart-summary">
				<p>Subtotal: <strong>£<?php echo number_format($cart_total, 2); ?></strong></p>
				<?php if($discount_amount > 0): ?>
					<p class="discount-line">Discount (<?php echo htmlspecialchars($applied_discount['code']); ?>
					&mdash; <?php echo (int)$applied_discount['discount_pct']; ?>% off): <strong>-£<?php echo number_format($discount_amount, 2); ?></strong></p>
				<?php endif; ?>
				<p class="grand-total"><strong>Total: £<?php echo number_format($grand_total, 2); ?></strong></p>
				
				
				<!-- Checkout button for logged-in users -->
				<?php if(isset($_SESSION['user_id'])): ?>
					<form method="POST" action="checkout.php" style="display:inline;">
						<input type="hidden" name="action" value="place_order">
						<button type="submit" class="checkout-button">Checkout</button>
					</form>	
				<?php else: ?>
					<a href="login.php" class="login-redirect">Login to checkout</a>
				<?php endif; ?>
			</div>
		</div>
		
		<div style="text-align:center;margin-top:1em;">
			<a href="products.php" class="view-button">Continue shopping</a>
		</div>
		<?php endif; ?>
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
