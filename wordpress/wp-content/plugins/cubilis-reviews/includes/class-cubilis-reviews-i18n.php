<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://forward.si/
 * @since      1.0.0
 *
 * @package    Cubilis_Reviews
 * @subpackage Cubilis_Reviews/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Cubilis_Reviews
 * @subpackage Cubilis_Reviews/includes
 * @author     Forward <info@forward.si>
 */
class Cubilis_Reviews_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cubilis-reviews',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
