<?php if(!isset($content_width)) $content_width = 640;
define('CPOTHEME_ID', 'ApartmajiMrakic');
define('CPOTHEME_NAME', 'Apartmaji Mrakic');
define('CPOTHEME_VERSION', '1.0.0');
//Other constants
define('CPOTHEME_LOGO_WIDTH', '170');
define('CPOTHEME_USE_SLIDES', true);
define('CPOTHEME_USE_FEATURES', true);
define('CPOTHEME_USE_PORTFOLIO', true);
define('CPOTHEME_USE_SERVICES', true);
define('CPOTHEME_USE_TESTIMONIALS', true);
//define('CPOTHEME_USE_TEAM', true);
//define('CPOTHEME_USE_CLIENTS', true);
define('CPOTHEME_THUMBNAIL_WIDTH', '400');
define('CPOTHEME_THUMBNAIL_HEIGHT', '400');
		
//Load Core; check existing core or load development core
$core_path = get_template_directory().'/core/';
if(defined('CPOTHEME_CORE')) $core_path = CPOTHEME_CORE;
require_once $core_path.'init.php';

$include_path = get_template_directory().'/includes/';

//Main components
require_once($include_path.'setup.php');

// Add epsilon framework
require get_template_directory() . '/includes/libraries/epsilon-framework/class-epsilon-autoloader.php';
$epsilon_framework_settings = array(
		'controls' => array( 'toggle' ), // array of controls to load
		'sections' => array( 'recommended-actions' ), // array of sections to load
		'path'     => '/includes/libraries'
	);
new Epsilon_Framework( $epsilon_framework_settings );

// Add welcome screen
require get_template_directory() . '/core/welcome-screen/welcome-page-setup.php';

// Add custom translation strings
//                   Name          String                 Group			Multiline
pll_register_string('basic_info', 'Osnovne informacije', 'Apartma', false);
pll_register_string('extras', 'Dodatna oprema', 'Apartma', false);
pll_register_string('rooms', 'O sobah', 'Apartma', false);



// Add custom options page for Cubillis
if (function_exists('acf_add_options_page')) {
	acf_add_options_page(array(
		'page_title' => 'Cubilis',
		'menu_title' => 'Cubilis',
		'menu_slug' => 'Cubilis',
		'capability' => 'edit_posts',
		'position' => 40,
		'icon_url' => 'dashicons-media-code'
	));
}

function am_header_overlay($wp_customize) {
	$wp_customize -> add_section( 'header_overlay', array(
		'title' => __('Header overlay'),
		'priority' => 20
	));
	$wp_customize -> add_setting('header_overlay_amount', array(
		'type' => 'theme_mod',
		'transport' => 'refresh',
		'capability' => 'edit_theme_options'
	));
	$wp_customize -> add_control('header_overlay_amount', array(
		'type' => 'number',
		'priority' => 20,
		'section' => 'header_overlay',
		'label' => __('Set intensity of header overlay'),
		'input_attrs' => array(
			'min' => 0,
			'max' => 100,
			'step' => 5
		)
	));
}
add_action( 'customize_register', 'am_header_overlay' );