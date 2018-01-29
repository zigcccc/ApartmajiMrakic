<?php
/**
 * Easy Digital Downloads Theme Updater
 *
 * @package EDD Sample Theme
 */

// Includes the files needed for the theme updater
if ( !class_exists( 'EDD_Theme_Updater_Admin' ) ) {
	include( dirname( __FILE__ ) . '/theme-updater-admin.php' );
}

$theme_version_from_stylesheet = wp_get_theme();

// Loads the updater classes
$updater = new EDD_Theme_Updater_Admin(

	// Config settings
	$config = array(
		'remote_api_url' => 'https://cpothemes.com', // Site where EDD is hosted
		'item_name'      => 'Brilliance Pro', // Name of theme
		'theme_slug'     => 'brilliance_pro', // Theme slug
		'version'        => $theme_version_from_stylesheet->get('Version'), // The current version of this theme
		'author'         => 'CPOThemes', // The author of this theme
		'download_id'    => '', // Optional, used for generating a license renewal link
		'renew_url'      => '', // Optional, allows for a custom license renewal link
		'beta'           => false, // Optional, set to true to opt into beta versions
	),

	// Strings
	$strings = array(
		'theme-license'             => __( 'Theme License', 'brilliance' ),
		'enter-key'                 => __( 'Enter your theme license key.', 'brilliance' ),
		'license-key'               => __( 'License Key', 'brilliance' ),
		'license-action'            => __( 'License Action', 'brilliance' ),
		'deactivate-license'        => __( 'Deactivate License', 'brilliance' ),
		'activate-license'          => __( 'Activate License', 'brilliance' ),
		'status-unknown'            => __( 'License status is unknown.', 'brilliance' ),
		'renew'                     => __( 'Renew?', 'brilliance' ),
		'unlimited'                 => __( 'unlimited', 'brilliance' ),
		'license-key-is-active'     => __( 'License key is active.', 'brilliance' ),
		'expires%s'                 => __( 'Expires %s.', 'brilliance' ),
		'expires-never'             => __( 'Lifetime License.', 'brilliance' ),
		'%1$s/%2$-sites'            => __( 'You have %1$s / %2$s sites activated.', 'brilliance' ),
		'license-key-expired-%s'    => __( 'License key expired %s.', 'brilliance' ),
		'license-key-expired'       => __( 'License key has expired.', 'brilliance' ),
		'license-keys-do-not-match' => __( 'License keys do not match.', 'brilliance' ),
		'license-is-inactive'       => __( 'License is inactive.', 'brilliance' ),
		'license-key-is-disabled'   => __( 'License key is disabled.', 'brilliance' ),
		'site-is-inactive'          => __( 'Site is inactive.', 'brilliance' ),
		'license-status-unknown'    => __( 'License status is unknown.', 'brilliance' ),
		'update-notice'             => __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update.", 'brilliance' ),
		'update-available'          => __('<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.', 'brilliance' ),
	)

);
