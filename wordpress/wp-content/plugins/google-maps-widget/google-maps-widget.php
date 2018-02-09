<?php
/*
Plugin Name: Google Maps Widget PRO
Plugin URI: http://www.gmapswidget.com/
Description: Display a single image super-fast loading Google map in a widget. A larger, full featured map is available as an image replacement or in a lightbox. Includes multiple pins per map, shortcode support and numerous appearance options.
Author: Web factory Ltd
Version: 5.35
Author URI: http://www.webfactoryltd.com/
Text Domain: google-maps-widget
Domain Path: lang

  Copyright 2012 - 2018  Web factory Ltd  (email : gmw@webfactoryltd.com)

  This program is NOT free software; you can't redistribute it and/or modify
  under any terms without written permission from Web Factory Ltd.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/


// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}


if (!class_exists('GMWP')) :

define('GMW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GMW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GMW_BASE_FILE', basename(__FILE__));


require_once GMW_PLUGIN_DIR . 'gmwp-widget.php';
require_once GMW_PLUGIN_DIR . 'gmwp-map-styles.php';
require_once GMW_PLUGIN_DIR . 'gmwp-export-import.php';


class GMWP {
  static $version;
  static $options = 'gmw_options';
  static $licensing_servers = array('http://license.gmapswidget.com/', 'http://license2.gmapswidget.com/');


  // get plugin version from header
  static function get_plugin_version() {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    GMWP::$version = $plugin_data['version'];

    return $plugin_data['version'];
  } // get_plugin_version


  // hook everything up
  static function init() {
    if (is_admin()) {
      // check if minimal required WP version is present
      if (false === GMWP::check_wp_version(4.0)) {
        return false;
      }

      // check if GMW is active
      if (true === class_exists('GMW')) {
        add_action('admin_notices', array('GMWP', 'notice_gmw_active_error'));
        return false;
      }

      // check a few variables
      GMWP::maybe_upgrade();
      add_filter('pre_set_site_transient_update_plugins', array('GMWP', 'update_filter'));
      add_filter('plugins_api', array('GMWP', 'update_details'), 10, 3);

      // aditional links in plugin description
      add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
                 array('GMWP', 'plugin_action_links'));
      add_filter('plugin_row_meta', array('GMWP', 'plugin_meta_links'), 10, 2);

      // enqueue admin scripts
      add_action('admin_enqueue_scripts', array('GMWP', 'admin_enqueue_scripts'), 100);
      add_action('customize_controls_enqueue_scripts', array('GMWP', 'admin_enqueue_scripts'));

      // JS dialog markup
      add_action('admin_footer', array('GMWP', 'admin_dialogs_markup'));

      // register AJAX endpoints
      add_action('wp_ajax_gmw_activate', array('GMWP', 'activate_license_key_ajax'));
      add_action('wp_ajax_gmw_test_api_key', array('GMWP', 'test_api_key_ajax'));
      add_action('wp_ajax_gmw_get_trial', array('GMWP', 'get_trial_ajax'));

      // custom admin actions
      add_action('admin_action_gmw_dismiss_notice', array('GMWP', 'dismiss_notice'));
      add_action('admin_action_gmw_export_widgets', array('GMWP_export_import', 'send_export_file'));

      // add options menu
      add_action('admin_menu', array('GMWP', 'add_menus'));

      // settings registration
      add_action('admin_init', array('GMWP', 'register_settings'));

      // display various notices
      add_action('current_screen', array('GMWP', 'add_notices'));
    } else {
      // enqueue frontend scripts
      add_action('wp_enqueue_scripts', array('GMWP', 'register_scripts'));
      add_action('wp_footer', array('GMWP', 'dialogs_markup'));
    }

    // add shortcode support
    GMWP::add_shortcodes();
  } // init


  // some things have to be loaded earlier
  static function plugins_loaded() {
    GMWP::get_plugin_version();
    load_plugin_textdomain('google-maps-widget', false, basename(dirname(__FILE__)) . '/lang');
  } // plugins_loaded


  // initialize widgets
  static function widgets_init() {
    $options = GMWP::get_options();

    register_widget('GoogleMapsWidget');

    if (!$options['disable_sidebar']) {
      register_sidebar( array(
        'name' => __('Google Maps Widget PRO hidden sidebar', 'google-maps-widget'),
        'id' => 'google-maps-widget-hidden',
        'description' => __('Widgets in this area will never be shown anywhere in the theme. Area only helps you to build maps that are displayed with shortcodes.', 'google-maps-widget'),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
      ));
    } // if activated
  } // widgets_init


  // all settings are saved in one option
  static function register_settings() {
    register_setting(GMWP::$options, GMWP::$options, array('GMWP', 'sanitize_settings'));
  } // register_settings


  // sanitize settings on save
  static function sanitize_settings($values) {
    $new_values = array();
    $old_options = GMWP::get_options();

    // license_key_changed
    if (isset($_POST['submit-license'])) {
      if (empty($values['activation_code'])) {
        $new_values['license_type'] = '';
        $new_values['license_expires'] = '';
        $new_values['license_active'] = false;
        $new_values['activation_code'] = '';
      } else {
        $tmp = GMWP::validate_activation_code($values['activation_code']);
        $new_values['activation_code'] = $values['activation_code'];
        if ($tmp['success']) {
          $new_values['license_type'] = $tmp['license_type'];
          $new_values['license_expires'] = $tmp['license_expires'];
          $new_values['license_active'] = $tmp['license_active'];
          if ($tmp['license_active']) {
            add_settings_error(GMWP::$options, 'license_key', __('License key saved and activated!', 'google-maps-widget'), 'updated');
          } else {
            add_settings_error(GMWP::$options, 'license_key', 'License not active. ' . $tmp['error'], 'error');
          }
        } else {
          add_settings_error(GMWP::$options, 'license_key', 'Unable to contact licensing server. Please try again in a few moments.', 'error');
        }
      }
      $values = $new_values;
    } elseif (isset($_POST['submit'])) { // save settings
      foreach ($values as $key => $value) {
        switch ($key) {
          case 'api_key':
            $values[$key] = str_replace(' ', '', $value);
          break;
          case 'sc_map':
            $values[$key] = sanitize_title_with_dashes($value);
          break;
          case 'activation_code':
            $values[$key] = substr(trim($value), 0, 50);
          break;
          case 'track_ga':
          case 'include_jquery':
          case 'include_gmaps_api':
          case 'include_lightbox_css':
          case 'include_lightbox_js':
          case 'disable_tooltips':
          case 'disable_sidebar':
            $values[$key] = (int) $value;
          break;
        } // switch
      } // foreach

      $values = GMWP::check_var_isset($values, array('track_ga' => '0', 'include_jquery' => '0', 'include_lightbox_js' => '0', 'include_lightbox_css' => '0', 'include_gmaps_api' => '0', 'disable_tooltips' => '0', 'disable_sidebar' => '0'));

      if (strlen($values['api_key']) < 30) {
        add_settings_error(GMWP::$options, 'api_key', __('Google Maps API key is not valid. Access <a href="https://console.developers.google.com/project" target="_blank">Google Developers Console</a> to generate a key for free or read detailed <a href="http://www.gmapswidget.com/documentation/generate-google-maps-api-key/" target="_blank">instructions</a>.', 'google-maps-widget'), 'error');
      }

      if (empty($values['sc_map'])) {
        $values['sc_map'] = 'gmw';
        add_settings_error(GMWP::$options, 'api_key', __('Map Shortcode is not valid. Please enter a valid shortcode name, eg: <i>gmw</i>.', 'google-maps-widget'), 'error');
      }
    } elseif (isset($_POST['submit-import'])) { // import widgets
      $import_data = GMWP_export_import::validate_import_file();
      if (is_wp_error($import_data)) {
        add_settings_error(GMWP::$options, 'import_widgets', $import_data->get_error_message(), 'error');
      } else {
        $results = GMWP_export_import::process_import_file($import_data);
        add_settings_error(GMWP::$options, 'import_widgets', __($results['total'] . ' widgets imported.', 'google-maps-widget'), 'updated');
      }
    } elseif (isset($_POST['submit-import-pins'])) { // import pins
      if (empty($values['widget_id'])) {
        add_settings_error(GMWP::$options, 'import_pins', 'Please select a widget you want to import pins to.', 'error');
        return $old_options;
      }

      if (empty($values['pins_txt']) && empty($_FILES['pins_file']['tmp_name'])) {
        add_settings_error(GMWP::$options, 'import_pins', 'Please provide the pin data in textarea, or upload a file.', 'error');
        return $old_options;
      }

      if (!empty($values['pins_txt'])) {
        $data = array();
        $rows = explode(PHP_EOL, trim($values['pins_txt']));
        foreach ($rows as $row) {
          $data[] = str_getcsv(trim($row), ',', '"', '\\');
        }
        set_transient('pins_import_tmp', $data, HOUR_IN_SECONDS);
        set_transient('pins_import_widget_id', $values['widget_id'], HOUR_IN_SECONDS);
        add_settings_error(GMWP::$options, 'import_pins', 'Please verify the integrity of import data.', 'updated');
      }

      if (!empty($_FILES['pins_file']['tmp_name'])) {
        $data = array();
        $rows = file($_FILES['pins_file']['tmp_name']);
        if (empty($rows)) {
          add_settings_error(GMWP::$options, 'import_pins', 'Unable to read pins file.', 'error');
          return;
        }
        foreach ($rows as $row) {
          if (empty($row)) {
            continue;
          }
          $data[] = str_getcsv(trim($row), ',', '"', '\\');
        }
        set_transient('pins_import_tmp', $data, HOUR_IN_SECONDS);
        set_transient('pins_import_widget_id', $values['widget_id'], HOUR_IN_SECONDS);
        add_settings_error(GMWP::$options, 'import_pins', 'Please verify the integrity of import data.', 'updated');
      }
    } elseif (isset($_POST['submit-import-pins-cancel'])) { // import pins cancel
      set_transient('pins_import_tmp', false);
      set_transient('pins_import_widget_id', false);
      add_settings_error(GMWP::$options, 'import_pins', 'Pins import canceled.', 'error');
      return $old_options;
    } elseif (isset($_POST['submit-import-pins-final'])) { // import pins finalize
      if (!get_transient('pins_import_tmp') || get_transient('pins_import_widget_id') === false) {
        add_settings_error(GMWP::$options, 'import_pins', 'Undocumented error. Please try again.', 'error');
        return $old_options;
      }

      $pins = $out = array();
      $data = get_transient('pins_import_tmp');
      foreach ($data as $pin) {
        $tmp = array();
        $tmp['address'] = $pin[0];
        $tmp['show_on'] = $pin[1];
        $tmp['thumb_pin_color'] = $pin[2];
        $tmp['interactive_pin_img'] = $pin[3];
        $tmp['interactive_pin_click'] = $pin[4];
        $tmp['description'] = $pin[5];
        $tmp['interactive_pin_url'] = $pin[6];
        $tmp['group'] = $pin[7];

        if (preg_match('|^([-+]?\d{1,2}([.]\d+)?),\s*([-+]?\d{1,3}([.]\d+)?)$|', $tmp['address'])) {
          $tmp['latlng'] = true;
          $tmp2 = explode(',', $tmp['address']);
          $tmp['lat'] = trim($tmp2[0]);
          $tmp['lng'] = trim($tmp2[1]);
          $tmp['from_cache'] = false;
          $tmp['cache_address'] = false;
        } else {
          $cache = GMWP::get_coordinates($tmp['address'], false);
          if ($cache) {
            $tmp['lat'] = $cache['lat'];
            $tmp['lng'] = $cache['lng'];
            $tmp['cache_address'] = $cache['address'];
            $tmp['from_cache'] = true;
            $tmp['latlng'] = true;
          } else {
            $tmp['from_cache'] = false;
            $tmp['latlng'] = false;
          }
        }
        $pins[] = $tmp;
      } // foreach

      $instances = get_option('widget_googlemapswidget', array());
      $widget_id = get_transient('pins_import_widget_id');

      $instances[$widget_id]['pins'] = $pins;
      $instances[$widget_id]['multiple_pins'] = 1;
      update_option('widget_googlemapswidget', $instances);

      set_transient('pins_import_tmp', false);
      set_transient('pins_import_widget_id', false);
      add_settings_error(GMWP::$options, 'import_pins', 'Pins imported!', 'updated');
      return $old_options;
    }

    return array_merge($old_options, $values);
  } // sanitize_settings


  // return default options
  static function default_options() {
    $defaults = array('sc_map'               => 'gmw',
                      'api_key'              => '',
                      'track_ga'             => '0',
                      'include_jquery'       => '1',
                      'include_gmaps_api'    => '1',
                      'include_lightbox_js'  => '1',
                      'include_lightbox_css' => '1',
                      'disable_tooltips'     => '0',
                      'disable_sidebar'      => '0',
                      'activation_code'      => '',
                      'license_active'       => '',
                      'license_expires'      => '',
                      'license_type'         => ''
                     );

    return $defaults;
  } // default_settings


  // get plugin's options
  static function get_options() {
    $options = get_option(GMWP::$options, array());

    if (!is_array($options)) {
      $options = array();
    }
    $options = array_merge(GMWP::default_options(), $options);

    return $options;
  } // get_options


  // update and set one or more options
  static function set_options($new_options) {
    if (!is_array($new_options)) {
      return false;
    }

    $options = GMWP::get_options();
    $options = array_merge($options, $new_options);

    update_option(GMWP::$options, $options);

    return $options;
  } // set_options


  // add widgets link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=gmw_options') . '" title="' . __('Google Maps Widget PRO Settings', 'google-maps-widget') . '">' . __('Settings', 'google-maps-widget') . '</a>';
    $widgets_link = '<a href="' . admin_url('widgets.php') . '" title="' . __('Configure Google Maps Widget PRO for your theme', 'google-maps-widget') . '">' . __('Widgets', 'google-maps-widget') . '</a>';

    array_unshift($links, $settings_link);
    array_unshift($links, $widgets_link);

    return $links;
  } // plugin_action_links


  // add links to plugin's description in plugins table
  static function plugin_meta_links($links, $file) {
    $documentation_link = '<a target="_blank" href="http://www.gmapswidget.com/documentation/" title="' . __('View Google Maps Widget PRO documentation', 'google-maps-widget') . '">'. __('Documentation', 'google-maps-widget') . '</a>';
    $support_link = '<a href="mailto:gmw@webfactoryltd.com?subject=GMW%20support" title="' . __('Problems? We are here to help!', 'google-maps-widget') . '">' . __('Support', 'google-maps-widget') . '</a>';
    $review_link = '<a target="_blank" href="https://wordpress.org/support/view/plugin-reviews/google-maps-widget?filter=5#pages" title="' . __('If you like it, please review the plugin', 'google-maps-widget') . '">' . __('Review the plugin', 'google-maps-widget') . '</a>';

    if ($file == plugin_basename(__FILE__)) {
      $links[] = $documentation_link;
      $links[] = $support_link;
      $links[] = $review_link;
    }

    return $links;
  } // plugin_meta_links


  // check if user has the minimal WP version required by GMW
  static function check_wp_version($min_version) {
    if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
        add_action('admin_notices', array('GMWP', 'notice_min_version_error'));
        return false;
    }

    return true;
  } // check_wp_version


  // display error message if WP version is too low
  static function notice_min_version_error() {
    echo '<div class="error"><p>' . sprintf(__('Google Maps Widget PRO <b>requires WordPress version 4.0</b> or higher to function properly. You are using WordPress version %s. Please <a href="%s">update it</a>.', 'google-maps-widget'), get_bloginfo('version'), admin_url('update-core.php')) . '</p></div>';
  } // notice_min_version_error


  // if GMW is active abort
  static function notice_gmw_active_error() {
    echo '<div class="error"><p>Please <b>disable Google Maps Widget (non PRO version)</b> if you want to run the PRO version. Both PRO and non-PRO versions can\'t be active at the same time.<br>License, settings and widgets will be preserved. No need to backup or transfer anything.</p></div>';
  } // notice_gmw_active_error


  // get users maps api key or one of temporary plugin ones
  static function get_api_key($type = 'static') {
    $options = GMWP::get_options();
    $default_api_keys = array('AIzaSyDlGWq9fCAJopqM0FakFvUWH0J52-tr3UM',
                              'AIzaSyArcXkQ15FoOTS2Z7El2SJHDIlTMW7Rxxg',
                              'AIzaSyBVJ4JR63d1JIL8L6b_emat-_jXMcHveR0',
                              'AIzaSyDOobziwX_9-4JuAgqIlTUZgXAss7zIIEM',
                              'AIzaSyBkMYtzfCn08icKNrr_-XySw7o8Bky0P94',
                              'AIzaSyAM8Az0e4CuP9Tuh0wgQZ6INzTgDGxSH_8',
                              'AIzaSyAL4eEFtz2wS9G6F3jz1Gb9j-BPC-ynR7w',
                              'AIzaSyDpV3BwEhOGCQcKmCvtYLre7sVu6wMpz3c',
                              'AIzaSyBwSjoVcKFI6jcgwWAgyS6ZffiJtMeIjDk',
                              'AIzaSyCLreV6CeBf3NDzpDfspK3BNQWIiK4qA80',
                              'AIzaSyAMdoX_dkbALmUcN6vXazWXRoMz-gG8lVI',
                              'AIzaSyAkdW5Zp4O-96nZyFKq13UUgIHY9Yabvg8');

    if ($type == 'test' && strlen($options['api_key']) < 30) {
      return false;
    }

    if (!empty($options['api_key'])) {
        return $options['api_key'];
    } else {
        shuffle($default_api_keys);
        return $default_api_keys[0];
    }
  } // get_api_key


  // checkes if API key is active for all needed API services
  static function test_api_key_ajax() {
    check_ajax_referer('gmw_test_api_key');

    $msg = '';
    $error = false;
    $api_key = trim(@$_GET['api_key']);

    $test = wp_remote_get(esc_url_raw('https://maps.googleapis.com/maps/api/staticmap?center=new+york+usa&size=100x100&key=' . $api_key));
    if (wp_remote_retrieve_response_message($test) == 'OK') {
      $msg .= 'Google Static Maps API test - OK' . "\n";
    } else {
      $msg .= 'Google Static Maps API test - FAILED' . "\n";
      $error = true;
    }

    $test = wp_remote_get(esc_url_raw('https://www.google.com/maps/embed/v1/place?q=new+york+usa&key=' . $api_key));
    if (wp_remote_retrieve_response_message($test) == 'OK') {
      $msg .= 'Google Embed Maps API test - OK' . "\n\n";
    } else {
      $msg .= 'Google Embed Maps API test - FAILED' . "\n\n";
      $error = true;
    }

    if ($error) {
      $msg .= 'Something is not right. Please read the instruction below on how to generate the API key and double-check everything.';
    } else {
      $msg = 'The API key is OK! Don\'t forget to save it ;)';
    }

    wp_send_json_success($msg);
  } // test_api_key


  // build a complete URL for the iframe map
  static function build_lightbox_url($widget) {
    $map_params = array();

    if ($widget['lightbox_mode'] == 'place') {
      $map_params['q'] = $widget['address'];
      $map_params['attribution_source'] = get_bloginfo('name')? get_bloginfo('name'): 'Google Maps Widget';
      $map_params['attribution_web_url'] = get_home_url();
      $map_params['attribution_ios_deep_link_id'] = 'comgooglemaps://?daddr=' . $widget['address'];
      $map_params['maptype'] = $widget['lightbox_map_type'];
      if ($widget['lightbox_zoom'] != 'auto') {
        $map_params['zoom'] = $widget['lightbox_zoom'];
      } else {
        $map_params['zoom'] = 13;
      }
    } elseif ($widget['lightbox_mode'] == 'directions') {
      $map_params['origin'] = $widget['lightbox_origin'];
      $map_params['destination'] = $widget['address'];
      $map_params['maptype'] = $widget['lightbox_map_type'];
      if (!empty($widget['lightbox_unit']) && $widget['lightbox_unit'] != 'auto') {
        $map_params['units'] = $widget['lightbox_unit'];
      }
      if ($widget['lightbox_zoom'] != 'auto') {
        $map_params['zoom'] = $widget['lightbox_zoom'];
      } else {
        $map_params['zoom'] = 13;
      }
    } elseif ($widget['lightbox_mode'] == 'search') {
      if (($coordinates = GMWP::get_coordinates($widget['address'])) !== false) {
        $map_params['center'] = $coordinates['lat'] . ',' . $coordinates['lng'];
      }
      $map_params['q'] = $widget['lightbox_search'];
      $map_params['maptype'] = $widget['lightbox_map_type'];
      if ($widget['lightbox_zoom'] != 'auto') {
        $map_params['zoom'] = $widget['lightbox_zoom'];
      } else {
        $map_params['zoom'] = 13;
      }
    } elseif ($widget['lightbox_mode'] == 'view') {
      if (($coordinates = GMWP::get_coordinates($widget['address'])) !== false) {
        $map_params['center'] = $coordinates['lat'] . ',' . $coordinates['lng'];
      }
      $map_params['maptype'] = $widget['lightbox_map_type'];
      if ($widget['lightbox_zoom'] != 'auto') {
        $map_params['zoom'] = $widget['lightbox_zoom'];
      } else {
        $map_params['zoom'] = 13;
      }
    } elseif ($widget['lightbox_mode'] == 'streetview') {
      if (($coordinates = GMWP::get_coordinates($widget['address'])) !== false) {
        $map_params['location'] = $coordinates['lat'] . ',' . $coordinates['lng'];
      }
      $map_params['heading'] = $widget['lightbox_heading'];
      $map_params['pitch'] = $widget['lightbox_pitch'];
    }

    if ($widget['lightbox_lang'] != 'auto') {
      $map_params['language'] = $widget['lightbox_lang'];
    }
    $map_params['key'] = GMWP::get_api_key('embed');

    $map_url = 'https://www.google.com/maps/embed/v1/' . $widget['lightbox_mode'] . '?';
    $map_url .= http_build_query($map_params, null, '&amp;');

    return $map_url;
  } // build_lightbox_url


  // fetch coordinates based on the address
  static function get_coordinates($address, $force_refresh = false) {
    $address_hash = md5('gmw_' . $address);

    if ($force_refresh || ($data = get_transient($address_hash)) === false) {
      $url = 'https://maps.googleapis.com/maps/api/geocode/xml?address=' . urlencode($address) . '&key=' . GMWP::get_api_key('fallback');
      $result = wp_remote_get(esc_url_raw($url), array('sslverify' => false, 'timeout' => 10));

      if (!is_wp_error($result) && $result['response']['code'] == 200) {
        $data = new SimpleXMLElement($result['body']);

        if ($data->status == 'OK') {
          $cache_value['lat']     = (string) $data->result->geometry->location->lat;
          $cache_value['lng']     = (string) $data->result->geometry->location->lng;
          $cache_value['address'] = (string) $data->result->formatted_address;

          // cache coordinates for 2 months
          set_transient($address_hash, $cache_value, MONTH_IN_SECONDS * 2);
          $data = $cache_value;
          $data['cached'] = false;
        } elseif (!$data->status) {
          return false;
        } else {
          return false;
        }
      } else {
         return false;
      }
    } else {
       // data is cached
       $data['cached'] = true;
    }

    return $data;
  } // get_coordinates


  // print dialogs markup in footer
  static function dialogs_markup() {
     $out = '';
     $multiple_pins = false;
     $js_vars = array();
     $options = GMWP::get_options();
     $measure_title = array('dark', 'sketchtoon', 'darkrimmed', 'fancyoverlay', 'rounded-white', 'noimage');

     if (empty(GoogleMapsWidget::$widgets)) {
       return;
     }

     foreach (GoogleMapsWidget::$widgets as $widget) {
       if ($widget['multiple_pins']) {
         $multiple_pins = true;
         $groups = array();
         $groups_html = '';
         $tmp = sizeof($widget['pins']);

         for ($i = 0; $i < $tmp; $i++) {
           $widget['pins'][$i]['description'] = wpautop(do_shortcode($widget['pins'][$i]['description']));
           if (!empty($widget['lightbox_filtering']) && !empty($widget['pins'][$i]['group'])) {
             $groups = array_merge($groups, explode(',', $widget['pins'][$i]['group']));
           } else {
             $widget['pins'][$i]['group'] = '';
           }
         }

         if (!empty($groups)) {
           $tmp = '';
           for ($i = 0; $i < sizeof($groups); $i++) {
             $groups[$i] = trim($groups[$i]);
           } // for
           $groups = array_values(array_unique($groups));
           for ($i = 0; $i < sizeof($groups); $i++) {
             $tmp .= '<span class="gmw-group-wrap"><input id="gmw-group-' . $widget['id'] . '-' . $i . '" type="checkbox" value="1" data-group-name="' . $groups[$i] . '" checked><label for="gmw-group-' . $widget['id'] . '-' . $i . '">' . $groups[$i] . '</label></span><br>';
           } // for
           $groups_html =  '<div class="gmw-groups-wrap"><b>Filter pins</b><br>' . $tmp . '</div>';
         }
         $js_vars[$widget['id']]['groups_html'] = $groups_html;

         $js_vars[$widget['id']]['pins'] = $widget['pins'];
         if ($widget['lightbox_color_scheme'] == 'custom') {
           $js_vars[$widget['id']]['lightbox_color_scheme'] = $widget['lightbox_color_scheme_custom'];
         } elseif ($widget['lightbox_color_scheme'] != 'default') {
           $js_vars[$widget['id']]['lightbox_color_scheme'] = GMWP_styles::$js_styles[$widget['lightbox_color_scheme']];
         } else {
           $js_vars[$widget['id']]['lightbox_color_scheme'] = false;
         }
         $js_vars[$widget['id']]['lightbox_zoom'] = $widget['lightbox_zoom'];
         $js_vars[$widget['id']]['lightbox_map_type'] = $widget['lightbox_map_type_multiple'];
         $js_vars[$widget['id']]['lightbox_lock'] = array();
         foreach ((array) $widget['lightbox_lock'] as $lock) {
           $js_vars[$widget['id']]['lightbox_lock'][$lock] = true;
         }
         $js_vars[$widget['id']]['lightbox_layer'] = array();
         foreach ((array) $widget['lightbox_layer'] as $layer) {
           $js_vars[$widget['id']]['lightbox_layer'][$layer] = true;
         }
       } else {
         $js_vars[$widget['id']]['map_url'] = GMWP::build_lightbox_url($widget);
       }

       if ($widget['lightbox_fullscreen']) {
         $widget['lightbox_width'] = '100%';
         $widget['lightbox_height'] = '100%';
       }

       $js_vars[$widget['id']]['widget_id'] = $widget['id'];
       $js_vars[$widget['id']]['lightbox_height'] = $widget['lightbox_height'];
       $js_vars[$widget['id']]['lightbox_width'] = $widget['lightbox_width'];
       $js_vars[$widget['id']]['lightbox_clustering'] = (int) @$widget['lightbox_clustering'];
       $js_vars[$widget['id']]['lightbox_filtering'] = (int) @$widget['lightbox_filtering'];
       $js_vars[$widget['id']]['thumb_width'] = $widget['thumb_width'];
       $js_vars[$widget['id']]['thumb_height'] = $widget['thumb_height'];
       $js_vars[$widget['id']]['lightbox_skin'] = $widget['lightbox_skin'];
       $js_vars[$widget['id']]['close_button'] = (int) in_array('close_button', $widget['lightbox_feature']);
       $js_vars[$widget['id']]['multiple_pins'] = (int) $widget['multiple_pins'];
       $js_vars[$widget['id']]['show_title'] = (int) in_array('title', $widget['lightbox_feature']);
       $js_vars[$widget['id']]['measure_title'] = (int) in_array($widget['lightbox_skin'], $measure_title);
       $js_vars[$widget['id']]['close_overlay'] = (int) in_array('overlay_close', $widget['lightbox_feature']);
       $js_vars[$widget['id']]['close_esc'] = (int) in_array('esc_close', $widget['lightbox_feature']);

       $out .= '<div class="gmw-dialog"
                style="display: none;"
                id="gmw-dialog-' . $widget['id'] . '"
                title="' . esc_attr($widget['title']) . '">';

       if ($widget['lightbox_header']) {
         $tmp = str_ireplace(array('{address}'), array($widget['address']), $widget['lightbox_header']);
         $out .= '<div class="gmw-header">' . wpautop(do_shortcode($tmp)) . '</div>';
       }
       $out .= '<div class="gmw-map"></div>';
       if ($widget['lightbox_footer']) {
         $tmp = str_ireplace(array('{address}'), array($widget['address']), $widget['lightbox_footer']);
         $out .= '<div class="gmw-footer">' . wpautop(do_shortcode($tmp)) . '</div>';
       }
       $out .= "</div>\n";
     } // foreach $widgets

     // add CSS and JS in footer
     $js_vars['track_ga'] = $options['track_ga'];
     $js_vars['plugin_url'] = GMW_PLUGIN_URL;
     if ($options['include_lightbox_css']) {
       $js_vars['colorbox_css'] = GMW_PLUGIN_URL . 'css/gmwp.css' . '?ver=' . GMWP::$version;
     } else {
       $js_vars['colorbox_css'] = false;
     }
     if ($options['include_lightbox_js']) {
       wp_enqueue_script('gmw-colorbox');
     }
     if ($multiple_pins && $options['include_gmaps_api']) {
       wp_enqueue_script('gmw-gmaps-api');
     }
     if ($multiple_pins) {
       wp_enqueue_script('gmw-gmap3');
     }
     wp_enqueue_script('gmw');
     wp_localize_script('gmw', 'gmw_data', $js_vars);

     echo $out;
  } // dialogs_markup


  // add plugin menus
  static function add_menus() {
    $title = '<span style="font-size: 11px;">Google Maps Widget <span style="color: #d54e21;">PRO</span></span>';
    add_options_page($title, $title, 'manage_options', GMWP::$options, array('GMWP', 'settings_screen'));
  } // add_menus


  // check availability and register shortcode
  static function add_shortcodes() {
    global $shortcode_tags;
    $options = GMWP::get_options();

    if (isset($shortcode_tags[$options['sc_map']])) {
      add_action('admin_notices', array('GMWP', 'notice_sc_conflict_error'));
    } else {
      add_shortcode($options['sc_map'], array('GMWP', 'do_shortcode'));
    }
  } // add_shortcodes


  // display notice if shortcode name is already taken
  static function notice_sc_conflict_error() {
    $options = GMWP::get_options();

    echo '<div class="error"><p><strong>' . __('Google Maps Widget PRO shortcode is not active!', 'google-maps-widget') . '</strong>' . sprintf(__(' Shortcode <i>[%s]</i> is already in use by another plugin or theme. Please deactivate that theme or plugin, or <a href="%s">change</a> the GMW shortcode.', 'google-maps-widget'), $options['sc_map'], admin_url('options-general.php?page=gmw_options')) . '</p></div>';
  } // notice_sc_conflict_error


  // handle dismiss button for notices
  static function dismiss_notice() {
    if (empty($_GET['notice'])) {
      wp_redirect(admin_url());
      exit;
    }

    if ($_GET['notice'] == 'upgrade') {
      GMWP::set_options(array('dismiss_notice_upgrade2' => true));
    }
    if ($_GET['notice'] == 'rate') {
      GMWP::set_options(array('dismiss_notice_rate' => true));
    }
    if ($_GET['notice'] == 'api_key') {
      GMWP::set_options(array('dismiss_notice_api_key' => true));
    }
    if ($_GET['notice'] == 'license_expires') {
      GMWP::set_options(array('dismiss_notice_license_expires' => true));
    }

    if (!empty($_GET['redirect'])) {
      wp_redirect($_GET['redirect']);
    } else {
      wp_redirect(admin_url());
    }

    exit;
  } // dismiss_notice


  // controls which notices are shown
  static function add_notices() {
    $options = GMWP::get_options();
    $notice = false;

    // license expire notice is always shown
    if ((!$notice && GMWP::is_activated() && empty($options['dismiss_notice_license_expires']) &&
        (strtotime($options['license_expires']) - time() < DAY_IN_SECONDS * 3)) ||
        (!$notice && empty($options['dismiss_notice_license_expires']) &&
        $options['license_expires'] < date('Y-m-d') && $options['license_active'] == true)) {
      add_action('admin_notices', array('GMWP', 'notice_license_expires'));
      $notice = true;
    } elseif ((!$notice && GMWP::is_activated() && GMWP::is_plugin_admin_page('settings') &&
        (strtotime($options['license_expires']) - time() < DAY_IN_SECONDS * 3)) ||
        (!$notice && GMWP::is_plugin_admin_page('settings') &&
        $options['license_expires'] < date('Y-m-d') && $options['license_active'] == true)) {
      add_action('admin_notices', array('GMWP', 'notice_license_expires'));
    } // show license expire notice

    // API key notification is shown if there are active widgets and no key
    if (empty($options['dismiss_notice_api_key']) &&
        !GMWP::get_api_key('test') && GMWP::count_active_widgets() > 0) {
      add_action('admin_notices', array('GMWP', 'notice_api_key'));
      $notice = true;
    } // show api key notice

    // rating notification is shown after 5 days if you have active widgets
    if (!$notice && empty($options['dismiss_notice_rate']) &&
        GMWP::count_active_widgets() > 0 &&
        (current_time('timestamp') - $options['first_install']) > (DAY_IN_SECONDS * 15)) {
      add_action('admin_notices', array('GMWP', 'notice_rate_plugin'));
      $notice = true;
    } // show rate notice
  } // add_notices


  // display message if license will expire in 14 days or less
  static function notice_license_expires() {
    $options = GMWP::get_options();

    $buy_url = admin_url('options-general.php?page=gmw_options&gmw_open_promo_dialog');
    $dismiss_url = add_query_arg(array('action' => 'gmw_dismiss_notice', 'notice' => 'license_expires', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

    $days = strtotime($options['license_expires'] . date(' G:i:m')) - time();
    $days = round($days / DAY_IN_SECONDS);

    $cancel_text = __('I will pay the full price ($39) later', 'google-maps-widget');

    echo '<div id="gmw_license_expires_notice" class="error notice"><p>';
    echo 'Your <b>Google Maps Widget</b> <b style="color: #d54e21;">PRO</b> trial ';
    if ($options['license_expires'] == date('Y-m-d')) {
      echo '<b>expires today</b>!';
      //echo ' A special <b>25% discount coupon</b> is valid only till trial lasts. Don\'t be late, no need to pay the full price.<br>';
      echo ' The maps will stop showing on your site at midnight.';
      echo '<br>If you have any questions please email <a href="mailto:gmw@webfactoryltd.com?subject=Trial%20support">support</a>. They will be happy to assist you.';
      $button_text = 'Buy PRO now to avoid problems with maps';
    } elseif (date('Y-m-d', time() + DAY_IN_SECONDS) == $options['license_expires']) {
      echo '<b>expires tomorrow</b>!';
      //echo ' A special <b>25% discount coupon</b> is valid only till trial lasts. Don\'t be late, no need to pay the full price.<br>';
      echo ' The maps will stop showing on your site tomorrow.';
      echo '<br>If you have any questions please email <a href="mailto:gmw@webfactoryltd.com?subject=Trial%20support">support</a>. They will be happy to assist you.';
      $button_text = 'Buy PRO now to avoid problems with maps';
    } elseif ($days > 1) {
      echo '<b>expires in ' . $days . ' days</b>!';
      //echo ' A special <b>25% discount coupon</b> is valid only till trial lasts. Don\'t be late, no need to pay the full price.<br>';
      echo ' The maps will stop showing on your site when trial expires.';
      echo '<br>If you have any questions please email <a href="mailto:gmw@webfactoryltd.com?subject=Trial%20support">support</a>. They will be happy to assist you.';
      $button_text = 'Buy PRO now to avoid problems with maps';
    } else {
      echo '<b>has expired</b>!';
      echo ' Maps can no longer be edited and have stopped displaying on your site.';
      echo '<br>If you have any questions please email <a href="mailto:gmw@webfactoryltd.com?subject=Trial%20support">support</a>. They will be happy to assist you.';
      $button_text = 'Buy PRO now to keep maps on your site';
      $cancel_text = '';
    }

    echo '<br><a data-target-screen="gmw_dialog_intro" href="' . esc_url($buy_url) . '" style="vertical-align: baseline; margin-top: 15px;" class="open_promo_dialog button-primary">' . $button_text . '</a>';
    if (0 && !GMWP::is_plugin_admin_page('settings')) {
      echo '&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '" class="">' . $cancel_text . '</a>';
    }
    echo '</p></div>';
  } // notice_license_expires


  // display message to rate plugin
  static function notice_rate_plugin() {
    $rate_url = 'https://wordpress.org/support/view/plugin-reviews/google-maps-widget?rate=5#postform';
    $dismiss_url = add_query_arg(array('action' => 'gmw_dismiss_notice', 'notice' => 'rate', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

    echo '<div id="gmw_rate_notice" class="updated notice"><p>' . __('Hi! We saw you\'ve been using <b>Google Maps Widget</b> for a few days and wanted to ask for your help to make the plugin even better.<br>We just need a minute of your time to rate the plugin. Thank you!', 'google-maps-widget');

    echo '<br><a target="_blank" href="' . esc_url($rate_url) . '" style="vertical-align: baseline; margin-top: 15px;" class="button-primary">' . __('Help us out &amp; rate the plugin', 'google-maps-widget') . '</a>';
    echo '&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">' . __('I already rated the plugin', 'google-maps-widget') . '</a>';
    echo '</p></div>';
  } // notice_rate_plugin


  // display message to enter API key
  static function notice_api_key() {
    if (GMWP::is_plugin_admin_page('settings')) {
      // display message to enter API key
      echo '<div id="gmw_api_key_notice" class="error notice"><p>';
      echo '<b>Important!</b> Google rules dictate that you have to register for a <b>free Google Maps API key</b>. ';
      echo 'Please follow our <a href="http://www.gmapswidget.com/documentation/generate-google-maps-api-key/" target="_blank">short instructions</a> to get the key. If you don\'t configure the API key the maps will not work properly.';
      echo '</p></div>';
    } else {
      //return; // disabled to make things less crowded
      $dismiss_url = add_query_arg(array('action' => 'gmw_dismiss_notice', 'notice' => 'api_key', 'redirect' => urlencode($_SERVER['REQUEST_URI'])), admin_url('admin.php'));

      echo '<div id="gmw_api_key_notice" class="error notice"><p>';
      echo '<b>Important!</b> New Google rules dictate that you have to register for a <b>free Google Maps API key</b>. ';
      echo 'Please open Google Maps Widget <a href="' . admin_url('options-general.php?page=gmw_options') . '" title="Google Maps Widget settings">settings</a> and follow instructions on how to obtain it. If you don\'t configure the API key the maps will stop working.';
      echo '<br><a href="' . admin_url('options-general.php?page=gmw_options') . '" style="vertical-align: baseline; margin-top: 15px;" class="button-primary">' . __('Configure the API key', 'google-maps-widget') . '</a>';
      //echo '&nbsp;&nbsp;<a href="' . esc_url($dismiss_url) . '">' . __('Dismiss notice', 'google-maps-widget') . '</a>';
      echo '</p></div>';
    }
  } // notice_api_key


  // register frontend scripts and styles
  static function register_scripts() {
    $options = GMWP::get_options();

    wp_register_style('gmw', GMW_PLUGIN_URL . 'css/gmwp.css', array(), GMWP::$version);

    if ($options['include_jquery']) {
      wp_register_script('gmw-colorbox', GMW_PLUGIN_URL . 'js/jquery.colorbox.min.js', array('jquery'), GMWP::$version, true);
      wp_register_script('gmw', GMW_PLUGIN_URL . 'js/gmwp.js', array('jquery'), GMWP::$version, true);
      wp_register_script('gmw-gmap3', GMW_PLUGIN_URL . 'js/gmwp-core.js', array('jquery'), GMWP::$version, true);
      wp_register_script('gmw-gmaps-api', '//maps.googleapis.com/maps/api/js?key=' . GMWP::get_api_key('fallback'), array('jquery'), null, true);
    } else {
      wp_register_script('gmw-colorbox', GMW_PLUGIN_URL . 'js/jquery.colorbox.min.js', array(), GMWP::$version, true);
      wp_register_script('gmw', GMW_PLUGIN_URL . 'js/gmwp.js', array(), GMWP::$version, true);
      wp_register_script('gmw-gmap3', GMW_PLUGIN_URL . 'js/gmwp-core.js', array(), GMWP::$version, true);
      wp_register_script('gmw-gmaps-api', '//maps.googleapis.com/maps/api/js?libraries=places&key=' . GMWP::get_api_key('fallback'), array(), null, true);
    }
  } // register_scripts


  // enqueue CSS and JS scripts in admin
  static function admin_enqueue_scripts() {
    $options = GMWP::get_options();

    $js_localize = array('activate_ok' => __('Superb! Plugin has been activated.', 'google-maps-widget'),
                         'activate_ok_refresh' => __("Superb! Plugin has been activated.\nThe page will reload.", 'google-maps-widget'),
                         'dialog_map_title' => __('Pick an address by drag &amp; dropping the pin', 'google-maps-widget'),
                         'undocumented_error' => __('An undocumented error has occured. Please refresh the page and try again.', 'google-maps-widget'),
                         'bad_api_key' => __('The API key format does not look right. Please double-check it.', 'google-maps-widget'),
                         'dialog_promo_title' => '<img alt="' . __('Google Maps Widget PRO', 'google-maps-widget') . '" title="' . __('Google Maps Widget PRO', 'google-maps-widget') . '" src="' . GMW_PLUGIN_URL . 'images/gmw-logo-pro-dialog.png' . '">',
                         'dialog_pins_title' => __('Pins Library', 'google-maps-widget'),
                         'plugin_name' => __('Google Maps Widget PRO', 'google-maps-widget'),
                         'id_base' => 'googlemapswidget',
                         'delete_pin_confirm' => __('Are you sure you want to delete the selected pin?', 'google-maps-widget'),
                         'customizer_address_picker' => __('At the moment, the address picker is not available in the theme customizer. Please use it in the admin widget GUI.', 'google-maps-widget'),
                         'customizer_pins_picker' => __('At the moment, the pins library is not available in the theme customizer. Please use it in the admin widget GUI.', 'google-maps-widget'),
                         'customizer_pro_dialog' => __('To see what the PRO version offers please open GMW settings in the admin.', 'google-maps-widget'),
                         'map' => false,
                         'marker' => false,
                         'trial_ok' => __('Your trial has been activated. Enjoy all PRO features for 7 days.' . "\n" . 'Check your email for a special DISCOUNT coupon ;)', 'google-maps-widget'),
                         'settings_url' => admin_url('options-general.php?page=gmw_options'),
                         'pins_library' => GMW_PLUGIN_URL . 'images/pins/',
                         'disable_tooltips' => $options['disable_tooltips'],
                         'is_activated' => GMWP::is_activated(),
                         'nonce_test_api_key' => wp_create_nonce('gmw_test_api_key'),
                         'nonce_get_trial' => wp_create_nonce('gmw_get_trial'),
                         'nonce_activate_license_key' => wp_create_nonce('gmw_activate_license_key'),
                         'deactivate_confirmation' => __('Are you sure you want to deactivate Google Maps Widget PRO?' . "\n" . 'All maps will be removed from the site. If you are removing it because of a problem please contact our support. They will be more than glad to help.', 'google-maps-widget'));

    if (GMWP::is_plugin_admin_page('widgets') || GMWP::is_plugin_admin_page('settings') || is_customize_preview()) {
      wp_enqueue_media();
      wp_enqueue_script('jquery-ui-tabs');
      wp_enqueue_script('jquery-ui-dialog');
      wp_enqueue_script('jquery-ui-accordion');
      wp_enqueue_script('jquery-ui-sortable');
      wp_enqueue_script('wp-color-picker');
      wp_enqueue_script('wp-pointer');
      wp_enqueue_script('gmw-gmap', '//maps.google.com/maps/api/js?key=' . GMWP::get_api_key('fallback'), array(), GMWP::$version, true);
      wp_enqueue_script('gmw-jquery-plugins', GMW_PLUGIN_URL . 'js/gmwp-jquery-plugins.js', array('jquery'), GMWP::$version, true);
      wp_enqueue_script('gmw-admin', GMW_PLUGIN_URL . 'js/gmwp-admin.js', array('jquery'), GMWP::$version, true);

      wp_enqueue_style('wp-jquery-ui-dialog');
      wp_enqueue_style('wp-color-picker');
      wp_enqueue_style('wp-pointer');
      wp_enqueue_style('gmw-select2', GMW_PLUGIN_URL . 'css/select2.min.css', array(), GMWP::$version);
      wp_enqueue_style('gmw-admin', GMW_PLUGIN_URL . 'css/gmwp-admin.css', array(), GMWP::$version);

      wp_localize_script('gmw-admin', 'gmw', $js_localize);

      // fix for agressive plugins
      wp_dequeue_style('uiStyleSheet');
      wp_dequeue_style('wpcufpnAdmin' );
      wp_dequeue_style('unifStyleSheet' );
      wp_dequeue_style('wpcufpn_codemirror');
      wp_dequeue_style('wpcufpn_codemirrorTheme');
      wp_dequeue_style('collapse-admin-css');
      wp_dequeue_style('jquery-ui-css');
      wp_dequeue_style('tribe-common-admin');
      wp_dequeue_style('file-manager__jquery-ui-css');
      wp_dequeue_style('file-manager__jquery-ui-css-theme');
      wp_dequeue_style('wpmegmaps-jqueryui');
      wp_dequeue_style('facebook-plugin-css');
      wp_dequeue_style('facebook-tip-plugin-css');
      wp_dequeue_style('facebook-member-plugin-css');
    } // if

    if (GMWP::is_plugin_admin_page('plugins')) {
      wp_enqueue_script('gmw-admin-plugins', GMW_PLUGIN_URL . 'js/gmwp-admin-plugins.js', array('jquery'), GMWP::$version, true);
      wp_localize_script('gmw-admin-plugins', 'gmw', $js_localize);
    }
  } // admin_enqueue_scripts


  // check if plugin's admin page is shown
  static function is_plugin_admin_page($page = 'widgets') {
    $current_screen = get_current_screen();

    if ($page == 'widgets' && $current_screen->id == 'widgets') {
      return true;
    }

    if ($page == 'settings' && $current_screen->id == 'settings_page_gmw_options') {
      return true;
    }

    if ($page == 'plugins' && $current_screen->id == 'plugins') {
      return true;
    }

    return false;
  } // is_plugin_admin_page


  // check if license key is valid and not expired
  static function is_activated($license_type = false) {
    $options = GMWP::get_options();

    if (isset($options['license_active']) && $options['license_active'] === true &&
        isset($options['license_expires']) && $options['license_expires'] >= date('Y-m-d')) {

      if (mt_rand(0, 1000) > 998 && is_admin()) {
        $tmp = GMWP::validate_activation_code($options['activation_code']);
        if ($tmp['success']) {
          $update['license_type'] = $tmp['license_type'];
          $update['license_expires'] = $tmp['license_expires'];
          $update['license_active'] = $tmp['license_active'];
          GMWP::set_options($update);
        }
      } // random license revalidation

      // check for specific license type?
      if (!empty($license_type)) {
        if (strtolower(trim($license_type)) == strtolower($options['license_type'])) {
          return true;
        } else {
          return false;
        }
      } // check specific license type

      return true;
    } else {
      return false;
    }
  } // is_activated


  // echo markup for dialogs
  static function admin_dialogs_markup() {
    $out = '';
    $options = GMWP::get_options();
    $promo_delta = 3*60*60;

    if (GMWP::is_plugin_admin_page('widgets') || GMWP::is_plugin_admin_page('settings')) {
      $current_user = wp_get_current_user();
      if (empty($current_user->user_firstname)) {
        $name = $current_user->display_name;
      } else {
        $name = $current_user->user_firstname;
      }

      $out .= '<div id="gmw_promo_dialog" style="display: none;">';

      $out .= '<div id="gmw_dialog_intro" class="gmw_promo_dialog_screen">
               <div class="content">
                  <div class="header"><p><a href="#" class="gmw_goto_pro">Learn more</a> about <span class="gmw-pro">PRO</span> features or <a href="#" class="gmw_goto_activation">enter your license key</a></p>';
      if (0 && $options['license_expires'] >= date('Y-m-d') && $options['license_type'] == 'trial' && GMWP::is_activated()) {
        $out .= '<div class="gmw-discount">We\'ve prepared a special <b>25% trial discount</b> for you available <b>only while the trial is active</b>. Discount has been applied on the unlimited license. Be quick &amp; use the buy button below.</div>';
      }
      $out .= '</div>'; // header

      $out .= '<div class="gmw-promo-box gmw-promo-box-agency gmw_goto_activation">
               <div class="gmw-promo-icon"><img src="' . GMW_PLUGIN_URL . 'images/icon-agency.png" alt="Unlimited Agency Lifetime License" title="Unlimited Agency Lifetime License"></div>
               <div class="gmw-promo-description"><h3>Unlimited Agency License</h3><br>
               <span>Unlimited Client &amp; Personal Sites<br>Lifetime Support + Lifetime Upgrades</span></div>';

      $out .= '<div class="gmw-promo-button"><a href="http://www.gmapswidget.com/buy/?p=pro-agency&r=GMW+v' . GMWP::$version . '" data-noprevent="1" target="_blank">BUY $99</a></div>';

      $out .= '</div>';

      $out .= '<div class="gmw-promo-box gmw-promo-box-lifetime gmw_goto_activation gmw-promo-box-hover">
               <div class="gmw-promo-icon"><img src="' . GMW_PLUGIN_URL . 'images/icon-unlimited.png" alt="Unlimited Lifetime License" title="Unlimited Lifetime License"></div>
               <div class="gmw-promo-description"><h3>Unlimited Personal License</h3><br>
               <span>Unlimited personal sites<br>Lifetime support + Lifetime Upgrades</span></div>';
      if (0 && $options['license_expires'] >= date('Y-m-d') && $options['license_type'] == 'trial' && GMWP::is_activated()) {
        $out .= '<div class="gmw-promo-button gmw-promo-button-extra"><a href="http://www.gmapswidget.com/buy/?p=pro-trial2&r=trial-GMW+v' . GMWP::$version . '+' . $options['activation_code'] . '" data-noprevent="1" target="_blank">only <strike>$39</strike> $29</a><span>discount: 25%</span></div>';
      } else {
        $out .= '<div class="gmw-promo-button"><a href="http://www.gmapswidget.com/buy/?p=pro-unlimited2&r=GMW+v' . GMWP::$version . '" data-noprevent="1" target="_blank">BUY $29</a></div>';
      }
      $out .= '</div>';
      $out .= '<div class="gmw-promo-box gmw-promo-box-yearly gmw_goto_activation">
               <div class="gmw-promo-icon"><img src="' . GMW_PLUGIN_URL . 'images/icon-yearly.png" alt="Yearly License" title="Yearly License"></div>
               <div class="gmw-promo-description"><h3>Single Site License</h3><br>
               <span>1 Site + 1 Year of Support &amp; Upgrades</span></div>
               <div class="gmw-promo-button"><a href="http://www.gmapswidget.com/buy/?p=yearly&r=GMW+v' . GMWP::$version . '" data-noprevent="1" target="_blank">$15 /year</a></div>
               </div>';
      if ($options['license_type'] != 'trial') {
        $out .= '<div class="gmw-promo-box gmw-promo-box-trial gmw_goto_trial">
                 <div class="gmw-promo-icon"><img src="' . GMW_PLUGIN_URL . 'images/icon-trial.png" alt="7 Days Free Trial License" title="7 Days Free Trial License"></div>
                 <div class="gmw-promo-description"><h3>7 Days Free Trial</h3><br>
                 <span>Still on the fence? Test PRO for free.</span></div>
                 <div class="gmw-promo-button"><a href="#">Start</a></div>
                 </div>';
      }
      //$out .= '<p class="gmw-footer-intro">Already have a license key? <a href="#" class="gmw_goto_activation">Enter it here</a></p>';
      $out .= '</div></div>'; // dialog intro

      $out .= '<div id="gmw_dialog_activate" style="display: none;" class="gmw_promo_dialog_screen">
                 <div class="content">';
      $out .= '<p class="input_row">
                 <input type="text" id="gmw_code" name="gmw_code" placeholder="Please enter the license key">
                 <span style="display: none;" class="error gmw_code">Unable to verify license key. Unknown error.</span></p>
                 <p class="center">
                   <a href="#" class="button button-primary" id="gmw_activate">Activate PRO features</a>
                 </p>
                 <p class="center">If you don\'t have a license key - <a href="#" class="gmw_goto_intro">Get it now</a></p>
               </div>';
      $out .= '<div class="footer">
                 <ul class="gmw-faq-ul">
                   <li>Having problems paying or you misplaced your key? <a href="mailto:gmw@webfactoryltd.com?subject=Activation%20key%20problem">Email us</a></li>
                   <li>Key not working? Our <a href="mailto:gmw@webfactoryltd.com?subject=Activation%20key%20problem">support</a> is here to help</li>
                 </ul>
               </div>';
      $out .= '</div>'; // activate screen

      $out .= '<div id="gmw_dialog_pro_features" style="display: none;" class="gmw_promo_dialog_screen">
                 <div class="content">';
      $out .= '<h4>See how <span class="gmw-pro-red">PRO</span> features can make your life easier!</h4>';
      $out .= '<ul class="list-left">';
      $out .= '<li>Multiple pins support</li>
               <li>12 thumbnail map skins</li>
               <li>1500+ thumbnail map pins</li>
               <li>4 extra map image formats for even faster loading</li>
               <li>replace thumb with interactive map feature</li>
               <li>extra hidden sidebar for easier shortcode handling</li>
               <li>custom map language option</li>
               <li>4 map modes; directions, view, street & streetview</li>
               <li>fully customizable pin options for thumbnail map</li>
               <li>Advanced cache &amp; fastest loading times</li>
               <li>JS &amp; CSS optimization options</li>
               <li>Continuous updates &amp; new features</li>';
      $out .= '</ul>';
      $out .= '<ul class="list-right">';
      $out .= '<li>Full control over all pins</li>
               <li>3 additional map link types</li>
               <li>fullscreen lightbox mode</li>
               <li>extra lightbox features</li>
               <li>19 lightbox skins</li>
               <li>full shortcode support</li>
               <li>Clone widget feature</li>
               <li>export & import tools</li>
               <li>Google Analytics integration</li>
               <li>no ads</li>
               <li>no promo emails</li>
               <li>premium email support</li>';
      $out .= '</ul>';
      $out .= '  </div>';
      $out .= '<div class="footer">';
      $out .= '<p class="center"><a href="#" class="button-secondary gmw_goto_intro">Go PRO now</a> <a href="#" class="button-secondary gmw_goto_trial">Start a free trial</a><br>
               Or <a href="#" class="gmw_goto_activation">enter the license key</a> if you already have it.</p>';
      $out .= '</div>';
      $out .= '</div>'; // pro features screen

      $out .= '<div id="gmw_dialog_trial" style="display: none;" class="gmw_promo_dialog_screen">
             <div class="content">
             <h3>Fill out the form and get your free trial started <b>INSTANTLY</b>!</h3>';
      $out .= '<p class="input_row">
                 <input value="' . $name . '" type="text" id="gmw_name" name="gmw_name" placeholder="Your name">
                 <span class="error name" style="display: none;">Please enter your name.</span>
               </p>';
      $out .= '<p class="input_row">
                 <input value="' . $current_user->user_email . '" type="text" name="gmw_email" id="gmw_email" placeholder="Your email address" required="required">
                 <span style="display: none;" class="error email">Please double check your email address.</span>
               </p>';
      $out .= '<p class="center">
                 <a id="gmw_start_trial" href="#" class="button button-primary">Start a 7 days free trial</a></p>
                 <p class="center">Already have a license key? <a href="#" class="gmw_goto_activation">Enter it here</a></p>
               </div>';
      $out .= '<div class="footer">
                    <ul class="gmw-faq-ul">
                      <li>Please check your email for a special <b>discount code</b></li>
                      <li>We\'ll never share your email address</li>
                      <li>We hate spam too, so we never send it</li>
                    </ul>
                  </div>';
      $out .= '</div>'; // trial screen

      $out .= '</div>'; // dialog
    } // promo dialog

    // address picker and pins dialog
    if (GMWP::is_plugin_admin_page('widgets')) {
      $out .= '<div id="gmw_map_dialog" style="display: none;">';
      $out .= '<div id="gmw_map_canvas"></div><hr>';
      $out .= '<div id="gmw_map_dialog_footer">';

      // current coordinates
      $out .= '<div class="gmw_dialog_current_coordinates">';
        $out .= 'Current coordinates: <input type="text" id="gmw_map_pin_coordinates" class="regular-text"> <a href="#" class="button-secondary gmw-move-pin" data-location-holder="gmw_map_pin_coordinates">Go</a><br>';
        $out .= '<a href="#" class="button-secondary gmw_close_save_map_dialog" data-location-holder="gmw_map_pin_coordinates">Use selected coordinates</a>';
      $out .= '</div>';

      // closest matching address
      $out .= '<div class="gmw_closest_matching_address">';
        $out .= 'Closest matching address: <input type="text" id="gmw_map_pin_address" class="regular-text"> <a href="#" class="button-secondary gmw-move-pin" data-location-holder="gmw_map_pin_address">Go</a><br>';
        $out .= '<a href="#" class="button-primary gmw_close_save_map_dialog" data-location-holder="gmw_map_pin_address">Use selected address</a>';
      $out .= '</div>';

      $out .= '</div>'; // footer
      $out .= '</div>'; // dialog

      // pins
      $out .= '<div id="gmw_pins_dialog" style="display: none;">';
      $out .= '<div id="search_header"><input type="search" id="pins_search" name="pins_search" placeholder="Search pins by name, eg hotel"><select id="pins_set"><option value="">All icon sets</option><option value="big/">Big icon set</option><option value="restaurants-hotels/">White restaurants & hotels icons</option><option value="white-stores/">White stores icons</option><option value="default/">Default icon set</option></select></div>';
      $out .= '<div id="pins_container">';
      foreach (glob(GMW_PLUGIN_DIR . 'images/pins/*/*.png') as $filename) {
        $filename = str_replace('\\', '/', $filename);
        preg_match('/\/([^\/]+)\/[^\/]+\.png$/i', $filename, $matches);
        if (!empty($matches[1])) {
          $folder = $matches[1];
        } else {
          $folder = 'default';
        }
        $filename = basename($filename);
        $name = str_replace(array('.png', '00_', '-', '_'), array('', '', ' ', ' '), $filename);
        $name = ucfirst($name);
        $filename = $folder . '/' . $filename;
        $out .= '<a href="#" data-filename="' . $filename . '"><img src="" alt="' . $name . '" title="' . $name . '"><span>' . $name . '</span></a>';
      }
      $out .= '<p><i>Default icon set is created by Nicolas Mollet under the Creative Commons Attribution-Share Alike 3.0 Unported license. You can find them on the <a class="skip-search" href="https://mapicons.mapsmarker.com/" target="_blank">Maps Icons Collection</a>.</i></p>';
      $out .= '</div>';
      $out .= '</div>'; // dialog
    } // address picker and pins dialog if activated

    echo $out;
  } // admin_dialogs_markup


  // complete options screen markup
  static function settings_screen() {
    if (!current_user_can('manage_options')) {
      wp_die('Cheating? You don\'t have the right to access this page.', 'Google Maps Widget', array('back_link' => true));
    }

    $options = GMWP::get_options();

    echo '<div class="wrap gmw-options">';
    echo '<h1><img alt="' . __('Google Maps Widget PRO', 'google-maps-widget') . '" title="' . __('Google Maps Widget PRO', 'google-maps-widget') . '" height="55" src="' . GMW_PLUGIN_URL . 'images/gmw-logo-pro.png"></h1>';

    echo '<form method="post" action="options.php" enctype="multipart/form-data">';
    settings_fields(GMWP::$options);

    echo '<div id="gmw-settings-tabs"><ul>';
    echo '<li><a href="#gmw-settings">' . __('Settings', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-import-pins">' . __('Import Pins', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-export">' . __('Export &amp; Import Widgets', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-license">' . __('License', 'google-maps-widget') . '</a></li>';
    echo '</ul>';

    echo '<div id="gmw-settings" style="display: none;">';
    echo '<table class="form-table">';
    echo '<tr>
          <th scope="row"><label for="api_key">' . __('Google Maps API Key', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[api_key]" type="text" id="api_key" value="' . esc_attr($options['api_key']) . '" class="regular-text" placeholder="Google Maps API key" oninput="setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'Please use Google Developers Console to generate an API key and enter it here. It is completely free.\')"> <a href="#" class="button button-secondary gmw-test-api-key">Test API key</a>
          <p class="description">New Google Maps usage policy dictates that everyone using the maps should register for a free API key.<br>
          Detailed instruction on how to generate it are available in the <a href="http://www.gmapswidget.com/documentation/generate-google-maps-api-key/" target="_blank">documentation</a>.<br>If you already have a key make sure the following APIs are enabled: Google Maps JavaScript API, Google Static Maps API, Google Maps Embed API &amp; Google Maps Geocoding API.</p></td>
          </tr>';
    echo '<tr>
          <th scope="row"><label for="sc_map">' . __('Map Shortcode', 'google-maps-widget') . '</label></th>
          <td><input class="regular-text" name="' . GMWP::$options . '[sc_map]" type="text" id="sc_map" value="' . esc_attr($options['sc_map']) . '" placeholder="Map shortcode" required="required" oninvalid="this.setCustomValidity(\'Please enter the shortcode you want to use for Google Maps Widget maps.\')" oninput="setCustomValidity(\'\')">
          <p class="description">If the default shortcode "gmw" is taken by another plugin change it to something else, eg: "gmaps".</p></td>
          </tr>';
    echo '</table>';

    echo '<h3 class="title">Advanced Settings</h3>';
    echo '<table class="form-table">';
    echo '<tr>
          <th scope="row"><label for="track_ga">' . __('Track with Google Analytics', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[track_ga]" type="checkbox" id="track_ga" value="1"' . checked('1', $options['track_ga'], false) . '>
          <span class="description">Each time the interactive map is opened either in lightbox or as a thumbnail replacement a Google Analytics Event will be tracked.<br>You need to have GA already configured on the site. It is fully compatibile with all GA plugins and all GA tracking code versions. Default: unchecked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="include_jquery">' . __('Include jQuery', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[include_jquery]" type="checkbox" id="include_jquery" value="1"' . checked('1', $options['include_jquery'], false) . '>
          <span class="description">If you\'re experiencing problems with double jQuery include disable this option. Default: checked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="include_gmaps_api">' . __('Include Google Maps API JS', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[include_gmaps_api]" type="checkbox" id="include_gmaps_api" value="1"' . checked('1', $options['include_gmaps_api'], false) . '>
          <span class="description">If your theme or other plugins already include Google Maps API JS disable this option. Default: checked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="include_lightbox_js">' . __('Include Colorbox JS', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[include_lightbox_js]" type="checkbox" id="include_lightbox_js" value="1"' . checked('1', $options['include_lightbox_js'], false) . '>
          <span class="description">If your theme or other plugins already include Colorbox JS file disable this option. Default: checked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="include_lightbox_css">' . __('Include Colorbox &amp; Thumbnail CSS', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[include_lightbox_css]" type="checkbox" id="include_lightbox_css" value="1"' . checked('1', $options['include_lightbox_css'], false) . '>
          <span class="description">If your theme or other plugins already include Colorbox CSS disable this option.<br>Please note that widget (thumbnail map) related CSS will also be removed which will cause minor differences in the way it\'s displayed. Default: checked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="disable_tooltips">' . __('Disable Admin Tooltips', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[disable_tooltips]" type="checkbox" id="disable_tooltips" value="1"' . checked('1', $options['disable_tooltips'], false) . '>
          <span class="description">All settings in widget edit GUI have tooltips. This setting completely disables them. Default: unchecked.</span></td></tr>';
    echo '<tr>
          <th scope="row"><label for="disable_sidebar">' . __('Disable Hidden Sidebar', 'google-maps-widget') . '</label></th>
          <td><input name="' . GMWP::$options . '[disable_sidebar]" type="checkbox" id="disable_sidebar" value="1"' . checked('1', $options['disable_sidebar'], false) . '>
          <span class="description">Hidden sidebar helps you to build maps that are displayed with shortcodes. If it bothers you in the admin, disable it. Default: unchecked.</span></td></tr>';
    echo '</table>';

    echo get_submit_button(__('Save Settings', 'google-maps-widget'));
    echo '</div>'; // settings tab

    echo '<div id="gmw-export" style="display: none;">';
    echo '<table class="form-table">';
    echo '<tr>
          <th scope="row"><label for="">' . __('Export widgets', 'google-maps-widget') . '</label></th>
          <td><a href="' . add_query_arg(array('action' => 'gmw_export_widgets'), admin_url('admin.php')) . '" class="button button-secondary">Download export file</a>
          <p class="description">The export file will only containt Google Maps Widget widgets. This includes active (in sidebars) widgets and inactive ones as well.</p></td>
          </tr>';
    echo '<tr>
          <th scope="row"><label for="">' . __('Import widgets', 'google-maps-widget') . '</label></th>
          <td><input type="file" name="gmw_widgets_import" id="gmw_widgets_import" accept=".txt">
          <input type="submit" name="submit-import" id="submit-import" class="button button-secondary button-large" value="Import widgets">';
    echo '<p class="description">Only use TXT export files generated by Google Maps Widget.<br>
          Existing GMW widgets will not be overwritten nor any other widgets touched. If you renamed a sidebar or old one no longer exists widgets will be placed in the inactive widgets area.</p></td>
          </tr>';
    echo '</table>';
    echo '</div>'; // export/import widgets tab

    echo '<div id="gmw-license" style="display: none;">';
    echo '<table class="form-table">';
    echo '<tr>
          <th scope="row"><label for="activation_code">' . __('License Key', 'google-maps-widget') . '</label></th>
          <td><input class="regular-text" name="' . GMWP::$options . '[activation_code]" type="text" id="activation_code" value="' . esc_attr($options['activation_code']) . '" placeholder="12345678-12345678-12345678-12345678">
          <p class="description">License key can be found in the confirmation email you received after purchasing.';
    if (!GMWP::is_activated()) {
      echo '<br>If you don\'t have a license - <a href="#" data-target-screen="gmw_dialog_intro" class="open_promo_dialog">purchase one now</a>';
    }
    echo '</p></td></tr>';
    if (GMWP::is_activated()) {
      if ($options['license_expires'] == '2035-01-01') {
        $valid = 'indefinitely';
      } else {
        $valid = 'until ' . date('F jS, Y', strtotime($options['license_expires']));
        if (date('Y-m-d') == $options['license_expires']) {
          $valid .= '; expires today';
        } elseif (date('Y-m-d', time() + DAY_IN_SECONDS) == $options['license_expires']) {
          $valid .= '; expires tomorrow';
        } elseif (date('Y-m-d', time() + 30 * DAY_IN_SECONDS) > $options['license_expires']) {
          $tmp = (strtotime($options['license_expires'] . date(' G:i:s')) - time()) / DAY_IN_SECONDS;
          $valid .= '; expires in ' . round($tmp) . ' days';
        }
      }
      echo '<tr>
          <th scope="row"><label for="">' . __('License Key Status', 'google-maps-widget') . '</label></th>
          <td><b style="color: green">Active</b><br>
          Type: ' . str_replace('pro', 'PRO', $options['license_type']);
      if ($options['license_type'] == 'trial') {
         echo ' - <a data-target-screen="gmw_dialog_intro" href="' . esc_url(admin_url('options-general.php?page=gmw_options&gmw_open_promo_dialog')) . '" class="open_promo_dialog">buy a lifetime PRO license</a>';
      }
      echo '<br>
          Valid ' . $valid . '</td>
          </tr>';
    } else {
      echo '<tr>
          <th scope="row"><label for="">' . __('License Key Status', 'google-maps-widget') . '</label></th>
          <td><b style="color: red">Inactive</b></td>
          </tr>';
    }
    echo '</table>';
    echo get_submit_button(__('Save and Validate License Key', 'google-maps-widget'), 'primary large', 'submit-license', true, array());
    echo '</div>'; // license tab

    $widgets = array(array('val' => '', 'label' => '- choose a widget to import pins to -'));
    $instances = get_option('widget_googlemapswidget', array());
    foreach ($instances as $instance_id => $instance_data) {
      if (is_numeric($instance_id)) {
        if(empty($instance_data['title'])) {
          $title = 'Widget #' . $instance_id;
        } else {
          $title = $instance_data['title'];
        }
        $widgets[] = array('val' => $instance_id, 'label' => $title);
      }
    } // foreach

    echo '<div id="gmw-import-pins" style="display: none;">';
    if (!self::get_api_key('test')) {
      echo '<p>Please set your API key. Import can\'t work without it.</p>';
    } elseif (get_transient('pins_import_tmp')) {
      echo '<tr><td colspan="2">';
      $data = get_transient('pins_import_tmp');
      echo '<strong>Import data preview. Please verify the data integrity and clik "Finalize Import" if everything looks good.</strong><br><br>';
      echo '<table id="import-preview">';
      echo '<tr>';
      echo '<td>Pin #</td>';
      echo '<td>Address</td>';
      echo '<td>Show on thumb/interactive</td>';
      echo '<td>Thumb pin color</td>';
      echo '<td>Interactive pin</td>';
      echo '<td>On pin click</td>';
      echo '<td>Description</td>';
      echo '<td>URL</td>';
      echo '<td>Group Name</td>';
      echo '</tr>';

      $i = 1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $i . '</td>';
        echo '<td>' . $row[0] . '</td>';
        echo '<td>' . $row[1] . '</td>';
        echo '<td>' . $row[2] . '</td>';
        echo '<td>' . $row[3] . '</td>';
        echo '<td>' . $row[4] . '</td>';
        echo '<td>' . $row[5] . '</td>';
        echo '<td>' . $row[6] . '</td>';
        echo '<td>' . $row[7] . '</td>';
        echo '</tr>';
        $i++;
      } // foreach

      echo '</table>';

      echo '</td></tr>';
      echo '<tr>
          <th scope="row"><br><br><br><input type="submit" name="submit-import-pins-final" id="submit-import-pins-final" class="button button-primary button-large" value="Finalize Import"> &nbsp;&nbsp; <input type="submit" name="submit-import-pins-cancel" id="submit-import-pins" class="button button-secondary button-large" value="Cancel"></th>';
      echo '</tr>';

    } else {
      echo '<table class="form-table">';
      echo '<tr>
            <th scope="row"><label for="widget_id">' . __('Google Maps Widget', 'google-maps-widget') . '</label></th>
            <td><select name="' . GMWP::$options . '[widget_id]" id="widget_id">';
      self::create_select_options($widgets, @$_GET['widget_id']);
      echo '</select><br><span class="description">Choose a widget you want to import pins to. Any existing pins will be overwritten with the new pins. Other widget options will not be altered in any way.</span></td></tr>';

      echo '<tr>
            <th scope="row"><label for="pins_txt">' . __('Pins, copy/paste', 'google-maps-widget') . '</label></th>';
      echo '<td><textarea style="width: 500px;" rows="3" name="' . GMWP::$options . '[pins_txt]" id="pins_txt">';
      echo '</textarea><br><span class="description">Data has to be formatted in a CSV fashion. One pin per line, individual fields double quoted and separated by a comma. All fields have to be included.<br>
      Please refer to the <a href="https://www.gmapswidget.com/documentation/importing-pins/" target="_blank">detailed documentation article</a> or grab the <a href="https://www.gmapswidget.com/wp-content/uploads/2018/02/sample-pins-import.csv" target="_blank">sample import file and modify it.</span></td></tr>';

      echo '<tr>
            <th scope="row"><label for="pins_file">' . __('Pins, upload file', 'google-maps-widget') . '</label></th>';
      echo '<td><input type="file" name="pins_file" id="pins_file">';
      echo '<br><span class="description">See rules noted for the field above.</span></td></tr>';

      echo '<tr>
          <th scope="row" colspan="2"><input type="submit" name="submit-import-pins" id="submit-import-pins" class="button button-primary button-large" value="Import pins"><br><br><i style="font-weight: normal;">No data will be written to the widget until you confirm it in step #2.<br>Importing can take up to 2 seconds per pin as the addresses have to be GEO coded. Please be patient.</i></th>';
      echo '</tr>';
    } // step zero

    echo '</table>';
    echo '</div>'; // import pins tab

    echo '</form>';
    echo '</div>'; // wrap
  } // settings_screen


  // send user's name & email and get trial license key
  static function get_trial_ajax() {
    check_ajax_referer('gmw_get_trial');

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    if (defined('WPLANG')) {
      $lang = strtolower(substr(WPLANG, 0, 2));
    } else {
      $lang = 'en';
    }

    $request_params = array('sslverify' => false, 'timeout' => 15, 'redirection' => 2);
    $request_args = array('action' => 'get_trial',
						  'name' => $name,
						  'email' => $email,
						  'lang' => $lang,
              'codebase' => 'pro',
						  'version' => GMWP::$version,
						  'ip' => $_SERVER['REMOTE_ADDR'],
						  'site' => get_home_url());

    $url = add_query_arg($request_args, GMWP::$licensing_servers[0]);
    $response = wp_remote_get(esc_url_raw($url), $request_params);

    if (is_wp_error($response) || !wp_remote_retrieve_body($response)) {
      $url = add_query_arg($request_args, GMWP::$licensing_servers[1]);
      $response = wp_remote_get(esc_url_raw($url), $request_params);
    }

    if (!is_wp_error($response) && wp_remote_retrieve_body($response)) {
      $result = wp_remote_retrieve_body($response);
      $result = json_decode($result, true);
      if (!empty($result['success']) && $result['success'] === true && is_array($result['data']) && sizeof($result['data']) == 3) {
        $result['data']['license_active'] = true;
        GMWP::set_options($result['data']);
        wp_send_json_success();
      } elseif (isset($result['success']) && $result['success'] === false && !empty($result['data'])) {
        wp_send_json_error($result['data']);
      } else {
        wp_send_json_error('Invalid response from licensing server. Please try again later.');
      }
    } else {
      wp_send_json_error('Unable to contact licensing server. Please try again in a few moments.');
    }
  } // get_trial_ajax


  // check activation code and save if valid
  static function activate_license_key_ajax() {
    check_ajax_referer('gmw_activate_license_key');

    $code = str_replace(' ', '', $_POST['code']);

    if (strlen($code) < 6 || strlen($code) > 50) {
      wp_send_json_error(__('Please double-check the license key. The format is not valid.', 'google-maps-widget'));
    }

    $tmp = GMWP::validate_activation_code($code);
    if ($tmp['success']) {
      GMWP::set_options(array('activation_code' => $code, 'license_active' => $tmp['license_active'], 'license_type' => $tmp['license_type'], 'license_expires' => $tmp['license_expires']));
    }
    if ($tmp['license_active'] && $tmp['success']) {
      wp_send_json_success();
    } else {
      wp_send_json_error($tmp['error']);
    }
  } // activate_license_key_ajax


  // get info on new plugin version if one exists
  static function update_filter($current) {
    if (!GMWP::is_activated()) {
      return $current;
    }

    static $response = false;
    $options = GMWP::get_options();
    $plugin = plugin_basename(__FILE__);

    if(empty($response) || is_wp_error($response)) {
      $request_params = array('sslverify' => false, 'timeout' => 15, 'redirection' => 2);
      $request_args = array('action' => 'update_info',
                            'timestamp' => time(),
							              'codebase' => 'pro',
                            'version' => GMWP::$version,
                            'code' => $options['activation_code'],
                            'site' => get_home_url());

      $url = add_query_arg($request_args, GMWP::$licensing_servers[0]);
      $response = wp_remote_get(esc_url_raw($url), $request_params);

      if (is_wp_error($response)) {
        $url = add_query_arg($request_args, GMWP::$licensing_servers[1]);
        $response = wp_remote_get(esc_url_raw($url), $request_params);
      }
    } // if !$response

    if (!is_wp_error($response) && wp_remote_retrieve_body($response)) {
      $data = json_decode(wp_remote_retrieve_body($response));
      if (empty($current)) {
        $current = new stdClass();
      }
      if (empty($current->response)) {
        $current->response = array();
      }
      if (!empty($data) && is_object($data)) {
        $current->response[$plugin] = $data;
      }
    }

    return $current;
  } // update_filter


  // get plugin info for lightbox
  static function update_details($result, $action, $args) {
    static $response = false;
    $options = self::get_options();
    $plugin = 'google-maps-widget';

    if ($action != 'plugin_information' || empty($args->slug) || ($args->slug != $plugin)) {
      return $result;
    }

    if(empty($response) || is_wp_error($response)) {
      $request_params = array('sslverify' => false, 'timeout' => 15, 'redirection' => 2);
      $request_args = array('action' => 'plugin_information',
                            'request_details' => serialize($args),
                            'timestamp' => time(),
							              'codebase' => 'pro',
                            'version' => GMWP::$version,
                            'code' => $options['activation_code'],
                            'site' => get_home_url());

      $url = add_query_arg($request_args, GMWP::$licensing_servers[0]);
      $response = wp_remote_get(esc_url_raw($url), $request_params);

      if (is_wp_error($response) || !wp_remote_retrieve_body($response)) {
        $url = add_query_arg($request_args, GMWP::$licensing_servers[1]);
        $response = wp_remote_get(esc_url_raw($url), $request_params);
      }
    } // if !$response

    if (is_wp_error($response) || !wp_remote_retrieve_body($response)) {
      $res = new WP_Error('plugins_api_failed', __('An unexpected HTTP error occurred during the API request.', 'google-maps-widget'));
    } else {
      $res = json_decode(wp_remote_retrieve_body($response), false);

      if (!is_object($res)) {
        $res = new WP_Error('plugins_api_failed', __('Invalid API respone.', 'google-maps-widget'), wp_remote_retrieve_body($response));
      } else {
        $res->sections = (array) $res->sections;
        $res->banners = (array) $res->banners;
        $res->icons = (array) $res->icons;
      }
    }

    return $res;
  } // update_details


  // check if activation code is valid
  static function validate_activation_code($code) {
    $request_params = array('sslverify' => false, 'timeout' => 25, 'redirection' => 2);
    $request_args = array('action' => 'validate_license',
	                      'code' => $code,
						  'version' => GMWP::$version,
						  'site' => get_home_url());

    $out = array('success' => false, 'license_active' => false, 'activation_code' => $code, 'error' => '', 'license_type' => '', 'license_expires' => '1900-01-01');

    $url = add_query_arg($request_args, GMWP::$licensing_servers[0]);
    $response = wp_remote_get(esc_url_raw($url), $request_params);

    if (is_wp_error($response) || !wp_remote_retrieve_body($response)) {
      $url = add_query_arg($request_args, GMWP::$licensing_servers[1]);
      $response = wp_remote_get(esc_url_raw($url), $request_params);
    }

    if (!is_wp_error($response) && wp_remote_retrieve_body($response)) {
      $result = wp_remote_retrieve_body($response);
      $result = json_decode($result, true);
      if (is_array($result['data']) && sizeof($result['data']) == 4) {
        $out['success'] = true;
        $out = array_merge($out, $result['data']);
      } else {
        $out['error'] = 'Invalid response from licensing server. Please try again later.';
      }
    } else {
      $out['error'] = 'Unable to contact licensing server. Please try again in a few moments.';
    }

    return $out;
  } // validate_activation_code


  // name to id converter for multidimensional names
  static function field_name_to_id($name, $increment = true) {
    global $field_name_cnt;
    if (empty($field_name_cnt)) {
      $field_name_cnt = 0;
    }

    if ($increment) {
      $field_name_cnt++;
    }

    $name = str_replace(array('][', '[', ']'), array('_', '_', $field_name_cnt), $name);

    return $name;
  } // field_name_to_id


  // helper function for creating dropdowns
  static function create_select_options($options, $selected = null, $output = true) {
    $out = "\n";

    if(!is_array($selected)) {
      $selected = array($selected);
    }

    foreach ($options as $tmp) {
      $data = '';

      if (isset($tmp['disabled'])) {
        $data .= ' disabled="disabled" ';
      }
      if ($tmp['val'] == '-1') {
        $data .= ' class="gmw_promo" ';
      }
      if (in_array($tmp['val'], $selected)) {
        $out .= "<option selected=\"selected\" value=\"{$tmp['val']}\"{$data}>{$tmp['label']}&nbsp;</option>\n";
      } else {
        $out .= "<option value=\"{$tmp['val']}\"{$data}>{$tmp['label']}&nbsp;</option>\n";
      }
    } // foreach

    if ($output) {
      echo $out;
    } else {
      return $out;
    }
  } // create_select_options


  // sanitizes color code string, leaves # intact
  static function sanitize_hex_color( $color ) {
    if (empty($color)) {
      return '#ff0000';
    }

    // 3 or 6 hex digits
    if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
      return $color;
    }
  } // sanitize_hex_color


  // converts color from human readable to hex
  static function convert_color($color) {
    $color_codes = array('black'  => '#000000', 'white'  => '#ffffff',
                         'brown'  => '#a52a2a', 'green'  => '#00ff00',
                         'purple' => '#800080', 'yellow' => '#ffff00',
                         'blue'   => '#0000ff', 'gray'   => '#808080',
                         'orange' => '#ffa500', 'red'    => '#ff0000');

    $color = strtolower(trim($color));

    if (empty($color) || !isset($color_codes[$color])) {
      return '#ff0000';
    } else {
      return $color_codes[$color];
    }
  } // convert_color


  // count active GMWP widgets in sidebars
  static function count_active_widgets() {
    $count = 0;

    $sidebars = get_option('sidebars_widgets', array());
    foreach ($sidebars as $sidebar_name => $widgets) {
      if (strpos($sidebar_name, 'inactive') !== false || strpos($sidebar_name, 'orphaned') !== false) {
        continue;
      }
      if (is_array($widgets)) {
        foreach ($widgets as $widget_name) {
          if (strpos($widget_name, 'googlemapswidget') !== false) {
            $count++;
          }
        }
      }
    } // foreach sidebar

    return $count;
  } // count_active_widgets


  // helper function for checkbox handling
  static function check_var_isset($values, $variables) {
    foreach ($variables as $key => $value) {
      if (!isset($values[$key])) {
        $values[$key] = $value;
      }
    }

    return $values;
  } // check_var_isset


  // shortcode support for any GMW instance
  static function do_shortcode($atts, $content = null) {
    global $wp_widget_factory;
    $out = '';
    $atts = shortcode_atts(array('id' => 0, 'thumb_width' => 0, 'thumb_height' => 0), $atts);
    $id = (int) $atts['id'];
    $widgets = get_option('widget_googlemapswidget');

    if (!$id || !isset($widgets[$id]) || empty($widgets[$id])) {
      $out .= '<span class="gmw-error">Google Maps Widget shortcode error - please double-check the widget ID.</span>';
    } else {
      $widget_args = $widgets[$id];
      $widget_instance['widget_id'] = 'googlemapswidget-' . $id;
      $widget_instance['widget_name'] = 'Google Maps Widget';

      if (!empty($atts['thumb_width']) && !empty($atts['thumb_height'])) {
        $widget_args['thumb_width'] = min(640, max(50, (int) $atts['thumb_width']));
        $widget_args['thumb_height'] = min(640, max(50, (int) $atts['thumb_height']));
      }

      $out .= '<div class="gmw-shortcode-widget">';
      ob_start();
      the_widget('GoogleMapsWidget', $widget_args, $widget_instance);
      $out .= ob_get_contents();
      ob_end_clean();
      $out .= '</div>';
    }

    return $out;
  } // do_shortcode


  // activate doesn't get fired on upgrades so we have to compensate
  public static function maybe_upgrade() {
    $options = GMWP::get_options();

    if (!isset($options['first_version']) || !isset($options['first_install'])) {
      $update = array();
      $update['first_version'] = GMWP::$version;
      $update['first_install'] = current_time('timestamp');
      GMWP::set_options($update);
    }
  } // maybe_upgrade


  // write down a few things on plugin activation
  static function activate() {
  } // activate


  // clean up on deactivation
  static function deactivate() {
  } // deactivate


  // clean up on uninstall / delete
  static function uninstall() {
	  delete_option(GMWP::$options);
  } // uninstall
} // class GMWP

endif; // if GMWP class exists


// hook everything up
register_activation_hook(__FILE__, array('GMWP', 'activate'));
register_deactivation_hook(__FILE__, array('GMWP', 'deactivate'));
register_uninstall_hook(__FILE__, array('GMWP', 'uninstall'));
add_action('init', array('GMWP', 'init'));
add_action('plugins_loaded', array('GMWP', 'plugins_loaded'));
add_action('widgets_init', array('GMWP', 'widgets_init'));
