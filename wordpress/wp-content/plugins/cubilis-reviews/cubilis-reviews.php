<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://forward.si
 * @since             1.0.0
 * @package           Cubilis_Reviews
 *
 * @wordpress-plugin
 * Plugin Name:       Cubilis Reviews
 * Plugin URI:        http://forward.si/
 * Description:       Display reviews of your rooms or apartments from Cubilis CRM on the front-end of your site.
 * Version:           1.0.0
 * Author:            Forward
 * Author URI:        http://forward.si/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cubilis-reviews
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUBILIS REVIEWS', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cubilis-reviews-activator.php
 */
function activate_cubilis_reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cubilis-reviews-activator.php';
	Cubilis_Reviews_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cubilis-reviews-deactivator.php
 */
function deactivate_cubilis_reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cubilis-reviews-deactivator.php';
	Cubilis_Reviews_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cubilis_reviews' );
register_deactivation_hook( __FILE__, 'deactivate_cubilis_reviews' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cubilis-reviews.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cubilis_reviews() {

	$plugin = new Cubilis_Reviews();
	$plugin->run();

}
run_cubilis_reviews();
