<?php
	//Helper functions for the cookie-based cart
	//This will be included on every page that displays the cart badge or reads cart contents
	
	//Read the cart cookie and return it as an associative array (product_id => quantity)
	function getCart(){
		if(!isset($_COOKIE['cart']) || $_COOKIE['cart'] === ''){
			return [];
		}
		$cart = json_decode($_COOKIE['cart'], true);
		//If decode fails or returns a non-array, treat as empty
		if(!is_array($cart)){
			return [];
		}
		return $cart; 
	}
	
	//Save the cart array back to a cookie with a 30-day expiry 
	//This must be called before any output
	function saveCart($cart){
		setcookie('cart', json_encode($cart), time() + (60*60*24*30), '/');
	}
	
	//Calculate the total item count
	function getCartCount(){
		$cart = getCart();
		$count = 0;
		foreach($cart as $qty){
			$count += (int)$qty;
		}
		return $count;
	}
	
	//Render the item count badge to be used in all pages
	function getCartBadge(){
		$count= getCartCount();
		if($count> 0){
			return '<span class="cart-badge">' . $count . '</span>';
		}
		return '';
	}
	
	//DISCOUNT-CODE HELPERS
	//The applied code will have its own cookie alogside the cart, but we are going to re-validate it against
	//tbl_offers on every render sp tampered/expired codes prove invalid and not affect the total's calculation
	
	//First read the applied discound code from the uppercased & trimmed cookie(defaults to empty if it doesnt exist)
	function getDiscountCode(){
		if(!isset($_COOKIE['discount_code']) || $_COOKIE['discount_code'] === ''){
			return '';
		}
		return strtoupper(trim($_COOKIE['discount_code']));
	}
	
	//Save the discount code in a cookie with the same 30expiry date as the cart
	function saveDiscountCode($code){
		setcookie('discount_code', $code, time() + (60*60*24*30), '/');
	}
	
	//Clear the discount code by expiring the cookie
	function clearDiscountCode(){
		setcookie('discount_code', '', time() - 3600, '/');
	}
	
	//Using a prepared statement, we validate the discount code against tbl_offers
	function validateDiscountCode($conn, $code){
		if($code === ''){
			return null;
		}
		$stmt= mysqli_prepare($conn, "SELECT offer_code, offer_discount,
		offer_title FROM tbl_offers WHERE offer_code = ? AND offer_discount IS NOT NULL LIMIT 1");	
		mysqli_stmt_bind_param($stmt, 's', $code);
		mysqli_stmt_execute($stmt);
		$result= mysqli_stmt_get_result($stmt);
		$row =mysqli_fetch_assoc($result);
		mysqli_stmt_close($stmt);
		if(!$row){
			return null;
		}
		
		//Set a limit to a range so a bad DB value cabt naje the total go negative
		$pct= max(0, min(100, (float)$row['offer_discount']));
		return['code' => $row['offer_code'],'discount_pct' => $pct,'title' => $row['offer_title']];
	}
?>
