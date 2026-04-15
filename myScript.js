/* ========================================
   navbar-hamburger MENU
   ======================================== */
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
if(hamburger && navMenu){
	hamburger.addEventListener('click', () => {
		navMenu.classList.toggle('active');
	});
}

/* ========================================
   BACK TO TOP BUTTON
   ======================================== */
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


	
