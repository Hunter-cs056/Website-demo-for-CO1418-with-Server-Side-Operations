<?php
require_once 'connect.php';
session_start();
require_once 'cart_helper.php';

//Implement the checkout
//If POST: action= place_order: re-verify cart and code server-side,
//then INSERT into tbl_orders, redirect to GET(PRG pattern)
//IF GET: show confirmation if order_summary is in session,otherwise go back to cart page

if($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_order'){
	//Check if the user is logged-in in order to place an Order
	if(!isset($_SESSION['user_id'])){
		mysqli_close($conn);
		header('Location: login.php');
		exit();
	}
	
	$cart = getCart();
	if(empty($cart)){
		mysqli_close($conn);
		header('Location: cart.php');
		exit();
		
	}
	
	//Fetch again products from the database to not rely on prices/titles from the cookies(more secure)
	$ids =array_map('intval', array_keys($cart));
	$placeholders=implode(',', array_fill(0, count($ids), '?'));	
	$types = str_repeat('i', count($ids));
	$stmt = mysqli_prepare($conn, "SELECT product_id, product_title, product_price FROM tbl_products WHERE product_id IN ($placeholders)");
	mysqli_stmt_bind_param($stmt, $types, ...$ids);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	
	$items =[];
	$subtotal = 0;
	while($row = mysqli_fetch_assoc($result)){
		$pid =(int)$row['product_id'];
		$qty =(int)$cart[$pid];
		$line_total= (float)$row['product_price'] * $qty;
		$subtotal += $line_total;
		$items[] = ['product_id' => $pid,'product_title' => $row['product_title'],
					'product_price' => (float)$row['product_price'],'quantity' => $qty,'line_total' => $line_total];
	}
	mysqli_stmt_close($stmt);
	
	//Check if every cart item was deleted from the DB after the cookie was set for safety
	if(empty($items)){
		saveCart([]);
		clearDiscountCode();
		mysqli_close($conn);
		$_SESSION['cart_message'] = 'Your cart contained items that are no longer available.';
		$_SESSION['cart_message_type'] = 'error';
		header('Location: cart.php');
		exit();
	}
	
	//Verify again any applied discount code against tbl_offers
	$discount_pct =0;
	$discount_amount =0;
	$discount_code= '';
	$valid = validateDiscountCode($conn, getDiscountCode());
	if($valid){
		$discount_pct =$valid['discount_pct'];
		$discount_code =$valid['code'];
		$discount_amount= $subtotal * ($discount_pct / 100);
	}	
	$total =$subtotal - $discount_amount;
	
	//Now we will create a compact "id:qty,id:qty" string for the varchar(255) product_ids column
	$parts = [];
	foreach($items as $it){
		$parts[]= $it['product_id'] . ':' . $it['quantity'];
	}
	$product_ids_string = implode(',', $parts);
	
	//Truncate just in case (255-char column limit)
	if(strlen($product_ids_string) > 255){
		$product_ids_string=substr($product_ids_string, 0, 255);
	}
	
	//Now we will use a prepare statement to insert the order into the DB
	$user_id =(int)$_SESSION['user_id'];
	$insert_stmt = mysqli_prepare($conn, "INSERT INTO tbl_orders (user_id, product_ids) VALUES (?, ?)");
	mysqli_stmt_bind_param($insert_stmt, 'is', $user_id, $product_ids_string);
	$exec_ok = mysqli_stmt_execute($insert_stmt);
	$rows_ok = mysqli_stmt_affected_rows($insert_stmt) === 1;
	$order_id= ($exec_ok && $rows_ok) ? mysqli_insert_id($conn) : 0;
	mysqli_stmt_close($insert_stmt);
	mysqli_close($conn);
	
	//IF the order successfully created a record,clear the cart and  discount, store summary for confirmation page
	if($order_id >0){
		saveCart([]);
		clearDiscountCode();
		$_SESSION['order_summary'] = ['order_id' => $order_id,'items' => $items,'subtotal' => $subtotal,
								'discount_pct' => $discount_pct,'discount_code' => $discount_code,
								'discount_amount' => $discount_amount,'total' => $total,'placed_at' => date('Y-m-d H:i:s')
		];
		header('Location: checkout.php');
		exit();
	//If the record hasnt been inserted correctly into the database display an error
	}else{
		$_SESSION['cart_message'] ='Sorry, your order could not be processed. Please try again.';
		$_SESSION['cart_message_type'] = 'error';
		header('Location: cart.php');
		exit();
	}	
}

//GET request — show confirmation if we have a recently placed order, else redirect to cart page
if(!isset($_SESSION['order_summary'])){
	mysqli_close($conn);
	header('Location: cart.php');
	exit();
}

//Clean the summary so a refresh sends the user back to cart page
$summary = $_SESSION['order_summary'];
unset($_SESSION['order_summary']);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
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
	
	<!-- Order confirmation -->
	<div id="checkout-content">
		<div class="order-confirmation">
			<h1>Thank you for your order!</h1>
			<p class="order-number">Order #<?php echo (int)$summary['order_id']; ?></p>
			<p class="order-date">Placed on <?php echo htmlspecialchars($summary['placed_at']); ?></p>
			
			<h2>Order Summary</h2>
			<table class="order-table">
				<thead>
					<tr>
						<th>Item</th>
						<th>Qty</th>
						<th>Price</th>
						<th>Subtotal</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($summary['items'] as $it): ?>
						<tr>
							<td><?php echo htmlspecialchars($it['product_title']); ?></td>
							<td><?php echo (int)$it['quantity']; ?></td>
							<td>£<?php echo number_format($it['product_price'], 2); ?></td>
							<td>£<?php echo number_format($it['line_total'], 2); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<div class= "order-totals">
				<p>Subtotal: <strong>£<?php echo number_format($summary['subtotal'], 2); ?></strong></p>
				<?php if($summary['discount_amount'] > 0): ?>
					<p class="discount-line">Discount (<?php echo htmlspecialchars($summary['discount_code']); ?>
					&mdash; <?php echo (int)$summary['discount_pct']; ?>% off): <strong>-£<?php echo number_format($summary['discount_amount'], 2); ?></strong></p>
				<?php endif; ?>	
				<p class="grand-total"><strong>Total: £<?php echo number_format($summary['total'], 2); ?></strong></p>
			</div>
			
			
			<p class="thank-you-msg">A copy of this order has been recorded. Enjoy!</p>
			<a href="products.php" class="view-button">Continue shopping</a>
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
	
