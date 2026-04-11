/* ========================================
   navbar-hamburger MENU
   ======================================== */
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
});

/* ========================================
    PRODUCT PAGE
   ======================================== */
//Products are now rendered server-side via PHP in products.PHP
//The fitler is also handled in the same way using PHP

//Lets add a back to top button functionality
const backToTopBtn= document.getElementById('back-to-top');
document.addEventListener('DOMContentLoaded', ()=>{
if(backToTopBtn){
//Initialy hide the button and show only on certain page height
window.addEventListener('scroll', ()=>{
	if(window.scrollY>=200){
		backToTopBtn.style.display = 'block';
	}
	else{
		backToTopBtn.style.display="none";
	}
});
//When the button is clicked we scroll to top
backToTopBtn.addEventListener('click', ()=>{
	window.scrollTo({top:0, behavior: 'smooth'})
});
	}
});
/* ========================================
    ITEM PAGE
   ======================================== */
//Use the saved index
const selectedIndex = sessionStorage.getItem('selectedProduct');

//Use the container
const detailContainer = document.getElementById('product-detail');
if(detailContainer){
	if(selectedIndex !== null){
	//Destructure product data
	const[name,color,price,stock,imgSrc,desc] = tshirts[selectedIndex];
	//Load the Product 
	detailContainer.innerHTML=`
	<div class="product-detail-card">
	<img src="${imgSrc}" alt="${name} - ${color}">
	<h2>${name} - ${color}</h2>
	<p>${desc}</p>
	<p><strong>${price}</strong></p>
	<p class="stock-status ${stock}"> ${stock.replace(/-/g, ' ')}</p>
	${stock !== 'out-of-stock' ?
	`<button onclick="addToCart(${selectedIndex})">Add to Cart</button>` : '' }
	</div>
	`;
	}
	else	{
	detailContainer.innerHTML = '<p> No product selected</p>';
	}}
/* ========================================
    ADD TO CART BUTTON'S FUNCTION
   ======================================== */
function addToCart(index){
	//Access the product data from the array
	const[name,color,price,stock,imgSrc,desc]=tshirts[index];
	//Load cart from our localstorage or start with an empty array
	const cart = JSON.parse(localStorage.getItem('cart')) || [];
	//Create a unique key to hold the product's  name + color combo
	const key=`${name}-${color}`;
	//Search our cart for the recently added product(return-1 if not found or the number otherwise)
	const existingIndex = cart.findIndex(item => item.key === key);
	//Now check if it already exist and increase its quantity
	if(existingIndex !== -1){
		cart[existingIndex].quantity += 1;
	}
	//Now if the product does not exist, we add it with quantity 1
	else {
		const item = {key, index, name, color, price, stock, imgSrc, desc, quantity:1};
		cart.push(item);
	}
	//Save the updated cart to localstorage
	localStorage.setItem('cart',JSON.stringify(cart));
	//Create alert box
	alert(`${name} - ${color} has been added to your Cart!`);
}
/* ========================================
   CART PAGE(DISPLAY CART ITEMS)
   ======================================== */
//Access the html containers 
const cartContainer = document.getElementById('cart-items');
const emptyCartMsg= document.getElementById('empty-cart');
const cartTotal = document.getElementById('cart-total')

// CALCULATING AND DISPLAYING TOTAL
//Calculate
	function calculateTotal(cart){
	return cart.reduce((sum, item) => {
		//Here we remove the currency symbol and convert to number type
		const price= parseFloat(item.price.replace(/[^\d.]/g, ''));
		return sum + price * item.quantity;
	}, 0);
	}
function renderCart(){
	//Load cart from our localstorage or start with an empty array
	const cart = JSON.parse(localStorage.getItem('cart')) || [];
	//If its empty show message , clear items and exit the function
	if(cart.length ==0){
		cartContainer.innerHTML='';
		emptyCartMsg.style.display='block';
		cartTotal.innerHTML= '';
		return;
	}
	//If not empty hide the empty Message
	emptyCartMsg.style.display ='none';
	cartContainer.innerHTML= '';
	
	//Loop through each item in the CART
	cart.forEach(item => 
	{	
		//Create a row for the product and give it a class name
		const row= document.createElement('div');
		row.className= 'cart-row'
		
		//Load inside each row
		row.innerHTML=`
		<div class="cart-cell">
			<img src="${item.imgSrc}" alt="${item.name} - ${item.color}" class="cart-image">
		</div>
		<div class="cart-cell">
			<span>${item.name} - ${item.color}</span>
			<br>
			<a href="item.php" class="view-button" onclick="sessionStorage.setItem('selectedProduct', ${item.index})">View More</a>
		</div>
		<div class="cart-cell">
			<span>${item.price}</span>
		</div>
		<div class="cart-cell">
			<button class="qty-btn" onclick="changeQuantity('${item.key}',-1)">-</button>
			<input type="number" id="qty-input" class="qty-input" min="1" value="${item.quantity}" onchange="setQuantity('${item.key}', this.value)">
			<button class="qty-btn" onclick="changeQuantity('${item.key}', 1)">+</button>
		</div>
		<div class="cart-cell">
			<button class="remove-btn" onclick="removeItem('${item.key}')">Remove</button>
		</div>
		`;
		cartContainer.appendChild(row);
	});

	//Display Total
	const total= calculateTotal(cart); 
	cartTotal.innerHTML = `<strong>Total: £${total.toFixed(2)} </strong>`;
}
//Create a discount code function that runs when the Apply button is clicked
function applyCode(){
	//Access the user input value and remove any extra spaces
	const code =document.getElementById('code-input').value.trim();
	//Load cart from our localstorage or start with an empty array
	const cart = JSON.parse(localStorage.getItem('cart')) || [];
	const total = calculateTotal(cart);
	//Check if the user input matches in order to apply disount
	if(code == "SAVE10"){
		const discountedTotal =total*0.9;
		cartTotal.innerHTML=`<strong>Discounted Total: £${discountedTotal.toFixed(2)}</strong>`;
	}
	//If the input does not match we display an alert
	else{
		alert("Invalid code ( reminder the code is: SAVE10 )");
		cartTotal.innerHTML=`<strong>Total: £${total.toFixed(2)}</strong>`;
	}
}
//Function that runs when the empty cart button is pressed
function emptyCart(){
	localStorage.removeItem('cart');
	renderCart();
}
//Function to an item regardless of quantity
function removeItem(key){
	//Load cart from our localstorage or start with an empty array
	let cart = JSON.parse(localStorage.getItem('cart')) || [];
	cart= cart.filter(item=> item.key !== key); //remove the selected item 
	localStorage.setItem('cart', JSON.stringify(cart)); // upload saved Cart
	renderCart();
}
//Change quantity using the symbols function 
function changeQuantity(key, delta){
	//Load cart from our localstorage or start with an empty array
	let cart = JSON.parse(localStorage.getItem('cart')) || [];
	const index = cart.findIndex(item=> item.key == key);
	//Identify which button was clicked and modify quantity
	if (index !== -1){
		cart[index].quantity += delta;
	//If we drop the quantity to 0, remove the ITEM
	if (cart[index].quantity <=0){
		cart.splice(index, 1);
	}
	//Update Cart
	localStorage.setItem('cart', JSON.stringify(cart));
	renderCart();
	}
}
//Change quantity by providing an input
function setQuantity(key, newQty){
	//Load cart from our localstorage or start  with an empty array
	let cart = JSON.parse(localStorage.getItem('cart')) || [];
	const index= cart.findIndex(item=> item.key == key);
	

	if(index !==-1){
		let qty = parseInt(newQty, 10);
		
		// if the input is not valid reset to previous value
		if(isNaN(qty) || qty < 1){
			renderCart();
			return
		}
		//Update our cart with valid quantity
		cart[index].quantity = qty;
		localStorage.setItem('cart' , JSON.stringify(cart));
		renderCart();
	}
}
//	Initial Render
	renderCart();
	
