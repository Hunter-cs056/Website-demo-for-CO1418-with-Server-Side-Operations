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
		$count = getCartCount();
		if($count> 0){
			return '<span class="cart-badge">' . $count . '</span>';
		}
		return '';
	}
?>