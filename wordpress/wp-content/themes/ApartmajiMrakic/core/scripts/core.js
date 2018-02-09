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
			var targetURL = event.target.href;
			dataLayer.push({
				event: 'CTA_Click',
				targetURL: targetURL
			});
		});
	}
});