<?php
session_start();
require_once 'cart_helper.php';
require_once 'connect.php';

//Only accept POST requests to be more secure
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
	header('Location: cart.php'); 
	exit();
}

//Read the current cart from the cookie
$cart= getCart();
$action= $_POST['action'] ?? '';

//Deside where to redirect after the action(defaults to cart.php)
$redirect = $_POST['redirect'] ?? 'cart.php';

//Whitelist redirect targets prevent open-redirect attacks
$allowed_redirects = ['cart.php', 'products.php', 'index.php'];
$is_item_redirect = strpos($redirect, 'item.php?id=') === 0;
if(!in_array($redirect, $allowed_redirects) && !$is_item_redirect){
	$redirect = 'cart.php';
}

//Implement the add to cart button for items that are in-stock
if($action === 'add'){
	//Only logged-in users can add to cart,guests redirect to login
	if(!isset($_SESSION['user_id'])){
		mysqli_close($conn);
		header('Location: login.php');
		exit();
	}
	
	$product_id= (int)($_POST['product_id'] ?? 0);
	if($product_id > 0){
		//Verify the product exists and is not out-of-stock (server-side check)
		$stmt = mysqli_prepare($conn, "SELECT product_stock FROM tbl_products WHERE product_id = ? LIMIT 1");
		mysqli_stmt_bind_param($stmt, 'i', $product_id);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_assoc($res);
		mysqli_stmt_close($stmt);
		
		if($row && $row['product_stock'] !== 'out-of-stock'){
			if(isset($cart[$product_id])){
				$cart[$product_id]++;
			}else{
				$cart[$product_id] = 1;
			}
			saveCart($cart);
		}
	}
}
//Implement the product's quantity manipulation
elseif($action=== 'update'){
	$product_id= (int)($_POST['product_id'] ?? 0);
	$qty = (int)($_POST['quantity'] ?? 0);
	if($product_id > 0){
		if($qty <= 0){
			unset($cart[$product_id]);
		}
		else {
			$cart[$product_id] = $qty;
		}
		saveCart($cart);
	}
}
//Implement the remove button functionality that removes the item from the cart
elseif($action === 'remove'){
	$product_id= (int)($_POST['product_id'] ?? 0);
	if($product_id > 0 && isset($cart[$product_id])){
		unset($cart[$product_id]);
		saveCart($cart);
	}
}
elseif($action === 'empty'){
	saveCart([]);
	clearDiscountCode(); //When the basket is wiped also remove applied discount codes
}
//Apply discount code which is server-side validated against tbl_offers
elseif($action === 'apply_code'){
	$code =strtoupper(trim($_POST['discount_code'] ?? ''));
	if($code === ''){
		$_SESSION['cart_message'] = 'Please enter a discount code.';
		$_SESSION['cart_message_type'] = 'error';
	}else {
		$valid= validateDiscountCode($conn, $code);
		if($valid){
			saveDiscountCode($valid['code']);
			$_SESSION['cart_message'] = 'Code "' . $valid['code'] . '" applied — ' . (int)$valid['discount_pct'] . '% off!';
			$_SESSION['cart_message_type']= 'success';
		}else  {
			$_SESSION['cart_message'] = 'Invalid or expired discount code.';
			$_SESSION['cart_message_type'] ='error';
		}
	}
}
//Action that lets the user remove a previously applied discount code
elseif($action === 'remove_code'){
	clearDiscountCode();
	$_SESSION['cart_message']= 'Discount code removed.';
	$_SESSION['cart_message_type']= 'success';
}

mysqli_close($conn);
header('Location: ' . $redirect);
exit();
?>
