<?php 

//set settings defaults
add_filter('cpotheme_customizer_controls', 'cpotheme_customizer_controls');
function cpotheme_customizer_controls($data){ 
	
	//Layout
	$data['home_order']['default'] = 'slider,tagline,features,portfolio,services,clients,testimonials,content';
	$data['slider_height']['default'] = 700;
	$data['features_columns']['default'] = 4;
	$data['portfolio_columns']['default'] = 4;
	$data['services_columns']['default'] = 3;
	//Layout
	$data['home_features']['default'] = '';
	$data['home_portfolio']['default'] = '';
	$data['home_services']['default'] = '';
	$data['home_testimonials']['default'] = '';
	//Typography
	$data['type_headings']['default'] = 'Open+Sans:700';
	$data['type_nav']['default'] = 'Open+Sans:700';
	$data['type_body']['default'] = 'Open+Sans';
	//Colors
	$data['primary_color']['default'] = '#ff2255';
	$data['secondary_color']['default'] = '#444444';
	$data['type_headings_color']['default'] = '#555566';
	$data['type_widgets_color']['default'] = '#555566';
	$data['type_nav_color']['default'] = '#ffffff';
	$data['type_body_color']['default'] = '#888899';
	$data['type_link_color']['default'] = '#22aacc';
	
	return $data;
}


add_action('wp_head', 'cpotheme_styling_custom', 19);
function cpotheme_styling_custom(){
	$primary_color = cpotheme_get_option('primary_color'); ?>
	<style type="text/css">
		<?php if($primary_color != ''): ?>
		.service .service-icon:link,
		.service .service-icon:visited,
		.footermenu .menu-footer > li > a,
		.menu-main .current_page_ancestor > a,
		.menu-main .current-menu-item > a { color:<?php echo $primary_color; ?>; }
		.menu-portfolio .current-cat a,
		.pagination .current { background-color:<?php echo $primary_color; ?>; }
		<?php endif; ?>
    </style>
	<?php
}


add_filter('wp_enqueue_scripts', 'cpotheme_theme_scripts');
function cpotheme_theme_scripts(){ 
	wp_register_script('cpotheme-brilliance', get_template_directory_uri().'/core/scripts/general.js', array('jquery'), false, true);
}