//CORE JS FUNCTIONALITY
//Contains only the most essential functions for the theme. No jQuery.

//MOBILE MENU TOGGLE
document.addEventListener('DOMContentLoaded', function(){
    var menu_element = document.getElementById('menu-mobile-open');
	var menu_exists = !!menu_element;
	if(menu_exists){
		menu_element.addEventListener('click', function(){
			document.body.classList.add('menu-mobile-active');
		});

		document.getElementById('menu-mobile-close').addEventListener('click', function(){
			document.body.classList.remove('menu-mobile-active');
		});
	}

	var dataLayer = window.dataLayer || [];
	var CTA_btn = document.querySelectorAll('.ctsc-button');

	for (var i = 0; i < CTA_btn.length; i++) {
		CTA_btn[i].addEventListener('click', function(event){
			var eventName = '';
			event.target.classList.contains('book-cta') ? eventName = 'Book_CTA_Click' : eventName = 'CTA_Click';
			var targetURL = event.target.href;
			dataLayer.push({
				event: eventName,
				targetURL: targetURL
			});
		});
	}
});


// Fixed menu on scroll

// var header = document.getElementById('header');
// var headerOffset = header.offsetTop;

// if (document.scrollingElement.scrollTop >= headerOffset) {
// 	if (document.querySelector('body').classList.contains('logged-in')) {
// 		!header.classList.contains('fixed-while-logged-in') ? header.classList.add('fixed-while-logged-in') : null;	
// 	}
// 	!header.classList.contains('is-fixed') ? header.classList.add('is-fixed') : null;
// }

// document.addEventListener('scroll', function(e){
// 	var pageOffset = e.target.scrollingElement.scrollTop;
// 	if (pageOffset >= headerOffset) {
// 		if (document.querySelector('body').classList.contains('logged-in')) {
// 			!header.classList.contains('fixed-while-logged-in') ? header.classList.add('fixed-while-logged-in') : null;	
// 		}
// 		!header.classList.contains('is-fixed') ? header.classList.add('is-fixed') : null;
// 	}
// 	else {
// 		if (document.querySelector('body').classList.contains('logged-in')) {
// 			header.classList.contains('fixed-while-logged-in') ? header.classList.remove('fixed-while-logged-in') : null;	
// 		}
// 		header.classList.contains('is-fixed') ? header.classList.remove('is-fixed') : null;
// 	}
// });