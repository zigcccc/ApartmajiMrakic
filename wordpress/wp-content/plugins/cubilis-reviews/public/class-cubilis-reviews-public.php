<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://forward.si/
 * @since      1.0.0
 *
 * @package    Cubilis_Reviews
 * @subpackage Cubilis_Reviews/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cubilis_Reviews
 * @subpackage Cubilis_Reviews/public
 * @author     Forward <info@forward.si>
 */
class Cubilis_Reviews_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $Cubilis_Reviews    The ID of this plugin.
	 */
	private $Cubilis_Reviews;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Cubilis_Reviews       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Cubilis_Reviews, $version ) {

		$this->Cubilis_Reviews = $Cubilis_Reviews;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cubilis_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cubilis_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->Cubilis_Reviews, plugin_dir_url( __FILE__ ) . 'css/cubilis-reviews-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cubilis_Reviews_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cubilis_Reviews_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->Cubilis_Reviews, plugin_dir_url( __FILE__ ) . 'js/cubilis-reviews-public.js', array( 'jquery' ), $this->version, false );

	}

}
