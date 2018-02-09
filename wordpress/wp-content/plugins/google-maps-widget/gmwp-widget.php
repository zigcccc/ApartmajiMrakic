<?php
/*
 * Google Maps Widget PRO
 * Widget definition, admin GUI and widget rendering functions
 * (c) Web factory Ltd, 2012 - 2018
 */


// this is an include only WP file
if (!defined('ABSPATH')) {
  die;
}


// main widget class, extends WP widget interface/class
if (!class_exists('GMW') && !class_exists('GoogleMapsWidget')):
class GoogleMapsWidget extends WP_Widget {
  static $widgets = array();
  static $defaults = array('title' => 'Map',
                           'address' => 'New York City, NY, USA',
                           'thumb_pin_color' => '#ff0000',
                           'thumb_pin_type' => 'predefined',
                           'thumb_pin_size' => 'default',
                           'thumb_pin_label' => 'A',
                           'thumb_pin_img' => '',
                           'thumb_pin_img_library' => 'default/00_default.png',
                           'thumb_width' => '250',
                           'thumb_height' => '250',
                           'thumb_type' => 'roadmap',
                           'thumb_zoom' => '13',
                           'thumb_header' => '',
                           'thumb_footer' => '',
                           'thumb_color_scheme' => 'new',
                           'thumb_color_scheme_custom' => '',
                           'thumb_link_type' => 'lightbox',
                           'thumb_link' => '',
                           'thumb_format' => 'png',
                           'thumb_lang' => 'en',
                           'lightbox_width' => '550',
                           'lightbox_height' => '550',
                           'lightbox_fullscreen' => '0',
                           'lightbox_mode' => 'place',
                           'lightbox_origin' => 'Washington DC, USA',
                           'lightbox_search' => '',
                           'lightbox_unit' => 'auto',
                           'lightbox_heading' => '0',
                           'lightbox_pitch' => '0',
                           'lightbox_map_type' => 'roadmap',
                           'lightbox_map_type_multiple' => 'roadmap',
                           'lightbox_zoom' => '13',
                           'lightbox_clustering' => '0',
                           'lightbox_filtering' => '0',
                           'lightbox_feature' => array('overlay_close'),
                           'lightbox_skin' => 'light',
                           'lightbox_lang' => 'en',
                           'lightbox_header' => '',
                           'lightbox_footer' => '',
                           'multiple_pins' => '0',
                           'lightbox_layer' => array(),
                           'lightbox_lock' => array(),
                           'lightbox_color_scheme' => 'default',
                           'lightbox_color_scheme_custom' => '',
                           'pins' => array(array('address' => 'New York City, NY, USA', 'thumb_pin_color' => '#FF0000', 'show_on' => '3', 'interactive_pin_img' => 'default/00_default.png', 'interactive_pin_click' => '1', 'description' => '', 'group' => ''), array('address' => 'Jersey City, NJ, USA', 'thumb_pin_color' => '#0000FF', 'show_on' => '3', 'description' => '', 'interactive_pin_click' => '1', 'interactive_pin_img' => 'default/00_default.png', 'group' => '')));


  // constructor - define the widget
  function __construct() {
    $title = 'Google Maps Widget <b>PRO</b>';
    $widget_ops = array('classname' => 'google-maps-widget', 'description' => __('Displays a map image thumbnail with a larger map available in a lightbox or as an inline replacement.', 'google-maps-widget'), 'customize_selective_refresh' => true);
    $control_ops = array('width' => 450, 'height' => 350);
    parent::__construct('GoogleMapsWidget', $title, $widget_ops, $control_ops);

    self::$defaults['title'] = __('Map', 'google-maps-widget');
  } // GoogleMapsWidget


  // widget edit form HTML
  function form($instance) {
    $options = GMWP::get_options();
    $instance = $this->upgrade_wiget_instance($instance);
    extract($instance, EXTR_SKIP);

    $thumb_map_types = array(array('val' => 'hybrid', 'label' => __('Hybrid', 'google-maps-widget')),
                             array('val' => 'roadmap', 'label' => __('Road (default)', 'google-maps-widget')),
                             array('val' => 'satellite', 'label' => __('Satellite', 'google-maps-widget')),
                             array('val' => 'terrain', 'label' => __('Terrain', 'google-maps-widget')));

    $lightbox_map_types = array(array('val' => 'roadmap', 'label' => __('Road (default)', 'google-maps-widget')),
                                array('val' => 'satellite', 'label' => __('Satellite', 'google-maps-widget')));

    $lightbox_modes = array(array('val' => 'directions', 'label' => __('Directions', 'google-maps-widget')),
                            array('val' => 'place', 'label' => __('Place (default)', 'google-maps-widget')),
                            array('val' => 'search', 'label' => __('Search', 'google-maps-widget')),
                            array('val' => 'streetview', 'label' => __('Street View', 'google-maps-widget')),
                            array('val' => 'view', 'label' => __('View (clean map, no markers)', 'google-maps-widget')));

    $thumb_pin_sizes = array(array('val' => 'tiny', 'label' => __('Tiny', 'google-maps-widget')),
                       array('val' => 'small', 'label' => __('Small', 'google-maps-widget')),
                       array('val' => 'mid', 'label' => __('Medium', 'google-maps-widget')),
                       array('val' => 'default', 'label' => __('Large (default)', 'google-maps-widget')));

    $pin_labels = array(array('val' => 'x', 'label' => __('Dot', 'google-maps-widget')));
    for ($tmp = 'A'; $tmp <= 'Z'; $tmp = chr(ord($tmp)+1)) {
      $pin_labels[] = array('val' => $tmp, 'label' => $tmp);
    }
    for ($tmp = 1; $tmp <= 9; $tmp++) {
      $pin_labels[] = array('val' => $tmp, 'label' => $tmp);
    }

    $zoom_levels_thumb = array(array('val' => 'auto', 'label' => 'Auto-adjust'));
    $zoom_levels_thumb[] = array('val' => '0', 'label' => __('0 - entire world', 'google-maps-widget'));
    for ($tmp = 1; $tmp <= 21; $tmp++) {
      if ($tmp == 13) {
        $zoom_levels_thumb[] = array('val' => $tmp, 'label' => $tmp . ' (default)');
      } else {
        $zoom_levels_thumb[] = array('val' => $tmp, 'label' => $tmp);
      }
    }
    $zoom_levels_lightbox = $zoom_levels_thumb;

    $lightbox_sizes = array(array('val' => '0', 'label' => __('Custom size (default)', 'google-maps-widget')),
                            array('val' => '1', 'label' => __('Fullscreen', 'google-maps-widget')));

    $lightbox_clustering_options = array(array('val' => '0', 'label' => __('Disabled (default)', 'google-maps-widget')),
                                         array('val' => '20', 'label' => __('Small cluster radius', 'google-maps-widget')),
                                         array('val' => '100', 'label' => __('Medium cluster radius', 'google-maps-widget')),
                                         array('val' => '220', 'label' => __('Large cluster radius', 'google-maps-widget')));

    $lightbox_filtering_options = array(array('val' => '0', 'label' => __('Disabled (default)', 'google-maps-widget')),
                                        array('val' => '1', 'label' => __('Enabled', 'google-maps-widget')));

    $lightbox_skins = array(array('val' => 'noimage-blue', 'label' => __('Blue', 'google-maps-widget')),
                            array('val' => 'sketchtoon', 'label' => __('Cartoonish', 'google-maps-widget')),
                            array('val' => 'dark', 'label' => __('Dark', 'google-maps-widget')),
                            array('val' => 'darkrimmed', 'label' => __('Dark rim', 'google-maps-widget')),
                            array('val' => 'fancyoverlay', 'label' => __('Fancy', 'google-maps-widget')),
                            array('val' => 'gears', 'label' => __('Gears', 'google-maps-widget')),
                            array('val' => 'gray-square', 'label' => __('Gray squared', 'google-maps-widget')),
                            array('val' => 'light', 'label' => __('Light (default)', 'google-maps-widget')),
                            array('val' => 'minimal', 'label' => __('Minimalistic', 'google-maps-widget')),
                            array('val' => 'minimal-circles', 'label' => __('Minimalistic #2', 'google-maps-widget')),
                            array('val' => 'painting', 'label' => __('Painting', 'google-maps-widget')),
                            array('val' => 'noimage-polaroid', 'label' => __('Polaroid', 'google-maps-widget')),
                            array('val' => 'noimage-rounded', 'label' => __('Rounded', 'google-maps-widget')),
                            array('val' => 'rounded-white', 'label' => __('Rounded white', 'google-maps-widget')),
                            array('val' => 'shadow', 'label' => __('Shadow', 'google-maps-widget')),
                            array('val' => 'noimage', 'label' => __('Simple', 'google-maps-widget')),
                            array('val' => 'square-black', 'label' => __('Squared black', 'google-maps-widget')),
                            array('val' => 'square-white', 'label' => __('Squared white', 'google-maps-widget')),
                            array('val' => 'tablet', 'label' => __('Tablet', 'google-maps-widget')),
                            array('val' => 'vintage', 'label' => __('Vintage', 'google-maps-widget')),
                            array('val' => 'wood', 'label' => __('Wood', 'google-maps-widget')));

    $thumb_pin_types = array(array('val' => 'predefined', 'label' => __('Predefined by Google (default)', 'google-maps-widget')),
                             array('val' => 'custom-library', 'label' => __('Pins library', 'google-maps-widget')),
                             array('val' => 'custom', 'label' => __('Custom image', 'google-maps-widget')));

    $thumb_link_types = array(array('val' => 'lightbox', 'label' => __('Interactive map in lightbox (default)', 'google-maps-widget')),
                              array('val' => 'replace', 'label' => __('Replace thumb map with an interactive map', 'google-maps-widget')),
                              array('val' => 'custom', 'label' => __('Custom URL', 'google-maps-widget')),
                              array('val' => 'custom_blank', 'label' => __('Custom URL in a new window', 'google-maps-widget')),
                              array('val' => 'interactive', 'label' => __('Skip thumbnail map, immediately show interactive one', 'google-maps-widget')),
                              array('val' => 'nolink', 'label' => __('Disable link', 'google-maps-widget')));

    $thumb_color_schemes = array(array('val' => 'apple', 'label' => __('Apple', 'google-maps-widget')),
                                 array('val' => 'blue', 'label' => __('Blue', 'google-maps-widget')),
                                 array('val' => 'bright', 'label' => __('Bright', 'google-maps-widget')),
                                 array('val' => 'default', 'label' => __('Default', 'google-maps-widget')),
                                 array('val' => 'gowalla', 'label' => __('Gowalla', 'google-maps-widget')),
                                 array('val' => 'gray', 'label' => __('Gray', 'google-maps-widget')),
                                 array('val' => 'gray2', 'label' => __('Gray #2', 'google-maps-widget')),
                                 array('val' => 'light', 'label' => __('Light', 'google-maps-widget')),
                                 array('val' => 'mapbox', 'label' => __('Mapbox', 'google-maps-widget')),
                                 array('val' => 'midnight', 'label' => __('Midnight', 'google-maps-widget')),
                                 array('val' => 'pale', 'label' => __('Pale', 'google-maps-widget')),
                                 array('val' => 'paper', 'label' => __('Paper', 'google-maps-widget')),
                                 array('val' => 'new', 'label' => __('Refreshed by Google', 'google-maps-widget')),
                                 array('val' => 'ultra_light', 'label' => __('Ultra Light', 'google-maps-widget')),
                                 array('val' => 'custom', 'label' => __('Custom - import you own scheme', 'google-maps-widget')));

    $lightbox_color_schemes = array(array('val' => 'apple', 'label' => __('Apple', 'google-maps-widget')),
                                    array('val' => 'blue', 'label' => __('Blue', 'google-maps-widget')),
                                    array('val' => 'bright', 'label' => __('Bright', 'google-maps-widget')),
                                    array('val' => 'default', 'label' => __('Default', 'google-maps-widget')),
                                    array('val' => 'gowalla', 'label' => __('Gowalla', 'google-maps-widget')),
                                    array('val' => 'gray', 'label' => __('Gray', 'google-maps-widget')),
                                    array('val' => 'gray2', 'label' => __('Gray #2', 'google-maps-widget')),
                                    array('val' => 'light', 'label' => __('Light', 'google-maps-widget')),
                                    array('val' => 'mapbox', 'label' => __('Mapbox', 'google-maps-widget')),
                                    array('val' => 'midnight', 'label' => __('Midnight', 'google-maps-widget')),
                                    array('val' => 'pale', 'label' => __('Pale', 'google-maps-widget')),
                                    array('val' => 'paper', 'label' => __('Paper', 'google-maps-widget')),
                                    array('val' => 'ultra_light', 'label' => __('Ultra Light', 'google-maps-widget')),
                                    array('val' => 'custom', 'label' => __('Custom - import you own scheme', 'google-maps-widget')));

    $thumb_formats = array(array('val' => 'png', 'label' => __('PNG 8-bit (default)', 'google-maps-widget')),
                           array('val' => 'png32', 'label' => __('PNG 32-bit', 'google-maps-widget')),
                           array('val' => 'gif', 'label' => __('GIF', 'google-maps-widget')),
                           array('val' => 'jpg', 'label' => __('JPEG', 'google-maps-widget')),
                           array('val' => 'jpg-baseline', 'label' => __('Non-progressive JPEG', 'google-maps-widget')));

    $thumb_langs = array(array('val' => 'auto', 'label' => __('Automatic (based on user\'s browser settings)', 'google-maps-widget')),
                         array('val' => 'ar', 'label' => __('Arabic', 'google-maps-widget')),
                         array('val' => 'eu', 'label' => __('Basque', 'google-maps-widget')),
                         array('val' => 'bn', 'label' => __('Bengali', 'google-maps-widget')),
                         array('val' => 'bg', 'label' => __('Bulgarian', 'google-maps-widget')),
                         array('val' => 'ca', 'label' => __('Catalan', 'google-maps-widget')),
                         array('val' => 'zh-CN', 'label' => __('Chinese (Simplified)', 'google-maps-widget')),
                         array('val' => 'zh-TW', 'label' => __('Chinese (Traditional)', 'google-maps-widget')),
                         array('val' => 'hr', 'label' => __('Croatian', 'google-maps-widget')),
                         array('val' => 'cs', 'label' => __('Czech', 'google-maps-widget')),
                         array('val' => 'da', 'label' => __('Danish', 'google-maps-widget')),
                         array('val' => 'nl', 'label' => __('Dutch', 'google-maps-widget')),
                         array('val' => 'en', 'label' => __('English', 'google-maps-widget')),
                         array('val' => 'en-AU', 'label' => __('English (Australian)', 'google-maps-widget')),
                         array('val' => 'en-GB', 'label' => __('English (Great Britain)', 'google-maps-widget')),
                         array('val' => 'fa', 'label' => __('Farsi', 'google-maps-widget')),
                         array('val' => 'fil', 'label' => __('Filipino', 'google-maps-widget')),
                         array('val' => 'fi', 'label' => __('Finnish', 'google-maps-widget')),
                         array('val' => 'fr', 'label' => __('French', 'google-maps-widget')),
                         array('val' => 'gl', 'label' => __('Galician', 'google-maps-widget')),
                         array('val' => 'de', 'label' => __('German', 'google-maps-widget')),
                         array('val' => 'el', 'label' => __('Greek', 'google-maps-widget')),
                         array('val' => 'gu', 'label' => __('Gujarati', 'google-maps-widget')),
                         array('val' => 'iw', 'label' => __('Hebrew', 'google-maps-widget')),
                         array('val' => 'hi', 'label' => __('Hindi', 'google-maps-widget')),
                         array('val' => 'hu', 'label' => __('Hungarian', 'google-maps-widget')),
                         array('val' => 'id', 'label' => __('Indonesian', 'google-maps-widget')),
                         array('val' => 'it', 'label' => __('Italian', 'google-maps-widget')),
                         array('val' => 'ja', 'label' => __('Japanese', 'google-maps-widget')),
                         array('val' => 'kn', 'label' => __('Kannada', 'google-maps-widget')),
                         array('val' => 'ko', 'label' => __('Korean', 'google-maps-widget')),
                         array('val' => 'lv', 'label' => __('Latvian', 'google-maps-widget')),
                         array('val' => 'lt', 'label' => __('Lithuanian', 'google-maps-widget')),
                         array('val' => 'ml', 'label' => __('Malayalam', 'google-maps-widget')),
                         array('val' => 'mr', 'label' => __('Marathi', 'google-maps-widget')),
                         array('val' => 'no', 'label' => __('Norwegian', 'google-maps-widget')),
                         array('val' => 'pl', 'label' => __('Polish', 'google-maps-widget')),
                         array('val' => 'pt', 'label' => __('Portuguese', 'google-maps-widget')),
                         array('val' => 'pt-BR', 'label' => __('Portuguese (Brazil)', 'google-maps-widget')),
                         array('val' => 'pt-PT', 'label' => __('Portuguese (Portugal)', 'google-maps-widget')),
                         array('val' => 'ro', 'label' => __('Romanian', 'google-maps-widget')),
                         array('val' => 'ru', 'label' => __('Russian', 'google-maps-widget')),
                         array('val' => 'sr', 'label' => __('Serbian', 'google-maps-widget')),
                         array('val' => 'sk', 'label' => __('Slovak', 'google-maps-widget')),
                         array('val' => 'sl', 'label' => __('Slovenian', 'google-maps-widget')),
                         array('val' => 'es', 'label' => __('Spanish', 'google-maps-widget')),
                         array('val' => 'sv', 'label' => __('Swedish', 'google-maps-widget')),
                         array('val' => 'tl', 'label' => __('Tagalog', 'google-maps-widget')),
                         array('val' => 'ta', 'label' => __('Tamil', 'google-maps-widget')),
                         array('val' => 'te', 'label' => __('Telugu', 'google-maps-widget')),
                         array('val' => 'th', 'label' => __('Thai', 'google-maps-widget')),
                         array('val' => 'tr', 'label' => __('Turkish', 'google-maps-widget')),
                         array('val' => 'uk', 'label' => __('Ukrainian', 'google-maps-widget')),
                         array('val' => 'vi', 'label' => __('Vietnamese', 'google-maps-widget')));

    $lightbox_langs = $thumb_langs;

    $lightbox_features = array(array('val' => 'overlay_close', 'label' => __('Close on overlay click', 'google-maps-widget')),
                               array('val' => 'esc_close', 'label' => __('Close on Esc key', 'google-maps-widget')),
                               array('val' => 'title', 'label' => __('Show map title', 'google-maps-widget')),
                               array('val' => 'close_button', 'label' => __('Show close button', 'google-maps-widget')));

    $lightbox_units = array(array('val' => 'auto', 'label' => __('Detect automatically', 'google-maps-widget')),
                            array('val' => 'imperial', 'label' => __('Imperial', 'google-maps-widget')),
                            array('val' => 'metric', 'label' => __('Metric', 'google-maps-widget')));

    $show_on_options = array(array('val' => '3', 'label' => __('Thumbnail and interactive map', 'google-maps-widget')),
                             array('val' => '1', 'label' => __('Thumbnail map only', 'google-maps-widget')),
                             array('val' => '2', 'label' => __('Interactive map only', 'google-maps-widget')));

    $interactive_pin_click_options = array(array('val' => '1', 'label' => __('Show description bubble (default)', 'google-maps-widget')),
                                           array('val' => '2', 'label' => __('Open URL', 'google-maps-widget')),
                                           array('val' => '3', 'label' => __('Open URL in a new tab', 'google-maps-widget')),
                                           array('val' => '0', 'label' => __('No action', 'google-maps-widget')));

    $lightbox_locks = array(array('val' => 'noscroll', 'label' => __('Disable mouse scrollwheel', 'google-maps-widget')),
                            array('val' => 'nostreet', 'label' => __('Disable Street View', 'google-maps-widget')),
                            array('val' => 'nodrag', 'label' => __('Disable dragging', 'google-maps-widget')),
                            array('val' => 'nodoubleclick', 'label' => __('Disable double click zooming', 'google-maps-widget')),
                            array('val' => 'nokeyboard', 'label' => __('Disable keyboard shortcuts', 'google-maps-widget')),
                            array('val' => 'nozoom', 'label' => __('Hide zoom UI', 'google-maps-widget')),
                            array('val' => 'notype', 'label' => __('Hide map type UI', 'google-maps-widget')),
                            array('val' => 'nopan', 'label' => __('Hide panning UI', 'google-maps-widget')));

    $lightbox_layers = array(array('val' => 'traffic', 'label' => __('Traffic', 'google-maps-widget')),
                             array('val' => 'transit', 'label' => __('Transit', 'google-maps-widget')),
                             array('val' => 'bicycle', 'label' => __('Bicycle tracks', 'google-maps-widget')));

    // license check
    if (!GMWP::is_activated()) {
      echo '<p>Please enter your license key below and click Save. The key can be found in the email you received after purchasing. If you lost it please contact <a href="mailto:gmw@webfactoryltd.com?subject=Lost%20license%20key">support</a>. If you don\'t have a license - <a href="#" data-target-screen="gmw_dialog_intro" class="open_promo_dialog">purchase one now</a>.</p>';
      echo '<p><label for="' . $this->get_field_id('activation_code') . '">' . __('License Key', 'google-maps-widget') . ':</label>';
      echo '<input data-tooltip="Your license key will be in the following format:
      _12345678-12345678-12345678-12345678 or 12345678_ if you have a trial key." class="widefat activation_code" id="' . $this->get_field_id('activation_code') . '" name="' . $this->get_field_name('activation_code') . '" placeholder="12345678-12345678-12345678-12345678" type="text" value="' . esc_attr($options['activation_code']) . '"><br>
      <input type="button" class="button button-primary gmw-widget-save-license" value="Save &amp; Validate License Key" style="margin-top: 1em;">';
      echo '</p>';

      echo '<p>If you are having any problems with the license please do not hasitate to contact <a href="mailto:gmw@webfactoryltd.com?subject=License%20key%20problem">support</a>.</p>';

      return;
    } // license check

    // trial notice
    if (GMWP::is_activated() && $options['license_type'] == 'trial') {
      $days = time() - strtotime($options['license_expires'] . date(' G:i:m'));
      $days = round($days / DAY_IN_SECONDS);
      echo '<p class="gmw-trial-notice"><b>Thank you</b> for trying out Google Maps Widget PRO! If you have any questions do not hesitate to contact our <a href="mailto:gmw@webfactoryltd.com?subject=Trial%20support">support</a>.</p>';
    }

    // warn if API key is not set
    if (!GMWP::get_api_key('test')) {
      echo '<p class="gmw-api-key-error"><b>Important!</b> ';
      echo 'Go to <a href="' . admin_url('options-general.php?page=gmw_options') . '" title="Google Maps Widget PRO settings">settings</a> and follow instructions on how to obtain your <b>free maps API key</b>. Without a key the maps will stop working. This requirement is enforced by Google.</p>';
    }

    // widget options markup
    // title & address
    echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title', 'google-maps-widget') . ':</label>';
    echo '<input data-tooltip="Widget title styled as defined in the active theme. HTML tags and shortcodes are not supported. Title is optional." class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" placeholder="' . __('Map title', 'google-maps-widget') . '" type="text" value="' . esc_attr($title) . '">';
    echo '</p>';
    echo '<label class="gmw_single_pin_feature" for="' . $this->get_field_id('address') . '">' . __('Address', 'google-maps-widget') . ':</label>';
    echo '<div class="input-address-group gmw_single_pin_feature">';
    echo '<input name="' . $this->get_field_name('address') . '" type="text" value="' . esc_attr($address) . '" required="required" class="widefat" id="' . $this->get_field_id('address') . '" placeholder="' . __('Address / location to show', 'google-maps-widget') . '" data-tooltip="' . htmlspecialchars('Address or location shown on both maps. Coordinates can be used as well, but please write them in a numerical fashion, not in degrees, ie: 40.70823, -74.01052
          If interactive map mode is set to directions this address is used as the destination address. If the mode is set to search the address will be used as the map and search center.') . '">';
    echo '<a data-target="' . $this->get_field_id('address') . '" href="#" class="button-secondary gmw-pick-address"><span class="dashicons dashicons-location"></span></a>';
    echo '</div>';
    // end - title & address

    echo '<div class="gmw-tabs" id="tab-' . $this->id . '"><ul>';
    echo '<li><a href="#gmw-thumb">' . __('Thumbnail Map', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-lightbox">' . __('Interactive Map', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-pins">' . __('Pins', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-shortcode">' . __('Tools', 'google-maps-widget') . '</a></li>';
    echo '<li><a href="#gmw-info">' . __('Info &amp; Support', 'google-maps-widget') . '</a></li>';
    echo '</ul>';

    // thumbnail tab
    echo '<div id="gmw-thumb">';
    echo '<p><label style="margin-top: -5px;" class="gmw-label" for="' . $this->get_field_id('thumb_width') . '">' . __('Map Size', 'google-maps-widget') . ':</label>';
    echo '<input data-title="Map Width" data-tooltip="Map width in pixels; from 50 to 640. The size limit is imposed by Google. Image may be resized by the theme if the sidebar is narrower." min="50" max="640" step="1" class="small-text" id="' . $this->get_field_id('thumb_width') . '" name="' . $this->get_field_name('thumb_width') . '" type="number" value="' . esc_attr($thumb_width) . '" required="required"> x ';
    echo '<input data-title="Map Height" data-tooltip="Map height in pixels; from 50 to 640. The size limit is imposed by Google." min="50" max="640" step="1" class="small-text" id="' . $this->get_field_id('thumb_height') . '" name="' . $this->get_field_name('thumb_height') . '" type="number" value="' . esc_attr($thumb_height) . '" required="required">';
    echo ' px</p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_type') . '">' . __('Map Type', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Controls the map layers shown. Roadmap is the most popular, hybrid combines road and satellite while terrain shows physical relief map image, displaying terrain and vegetation." id="' . $this->get_field_id('thumb_type') . '" name="' . $this->get_field_name('thumb_type') . '">';
    GMWP::create_select_options($thumb_map_types, $thumb_type);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_color_scheme') . '">' . __('Color Scheme', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Changes the overall appearance of the map. Please note that most visitors are acustomed to the Refreshed color scheme." class="gmw_thumb_color_scheme" id="' . $this->get_field_id('thumb_color_scheme') . '" name="' . $this->get_field_name('thumb_color_scheme') . '">';
    GMWP::create_select_options($thumb_color_schemes, $thumb_color_scheme);
    echo '</select></p>';

    echo '<p class="thumb_color_scheme_custom"><label style="margin-top: -40px;" class="gmw-label" for="' . $this->get_field_id('thumb_color_scheme_custom') . '">' . __('Custom Color Scheme', 'google-maps-widget') . ':</label>';
    echo '<textarea data-tooltip="Only use JSON formatted (JS style) custom style.<br><a target=\'blank\' href=\'https://snazzymaps.com/\'>Snazzy Maps</a> offers over 15,000 custom map styles you can customize. <a target=\'blank\' href=\'http://www.mapstylr.com/map-style-editor/\'>Mapstylr</a> and <a target=\'blank\' href=\'https://mapstyle.withgoogle.com/\'>Mapstyle</a> offer style builders too. Refer to <a target=\'blank\' href=\'http://www.gmapswidget.com/documentation/\'>documentation</a> for more details.<br>*Important*: due to URL length limitations imposed by browsers some custom styles may not be applied. If that happens try using a simpler style with less customizations." class="" id="' . $this->get_field_id('thumb_color_scheme_custom') . '" rows="2" cols="20" placeholder="JSON formatted custom map style" name="' . $this->get_field_name('thumb_color_scheme_custom') . '">';
    echo esc_textarea($thumb_color_scheme_custom);
    echo '</textarea></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_zoom') . '">' . __('Zoom Level', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Zoom varies from the lowest level, in which the entire world can be seen, to highest, which shows streets and individual buildings. Building outlines, where available, appear on the map around zoom level 17. This value differs from area to area." class="gmw_thumb_zoom" id="' . $this->get_field_id('thumb_zoom') . '" name="' . $this->get_field_name('thumb_zoom') . '">';
    GMWP::create_select_options($zoom_levels_thumb, $thumb_zoom);
    echo '</select></p>';

    echo '<p class="gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_type') . '">' . __('Pin Type', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Predefined pin can be adjusted in terms of color, size and one letter label.
          Custom pin can be any custom image stored on a publically available server.
          If you are using a pin from the library please note that it will *not work* if your site is on a localhost or a secure only (https) server." class="gmw_thumb_pin_type" id="' . $this->get_field_id('thumb_pin_type') . '" name="' . $this->get_field_name('thumb_pin_type') . '">';
    GMWP::create_select_options($thumb_pin_types, $thumb_pin_type);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_custom_library gmw_single_pin_feature"><span class="gmw-label">' . __('Pin Image', 'google-maps-widget') . ':</span>';
    echo '<input class="thumb_pin_img_library" type="hidden" id="' . $this->get_field_id('thumb_pin_img_library') . '" name="' . $this->get_field_name('thumb_pin_img_library') . '" value="' . esc_attr($thumb_pin_img_library) . '">';
    echo '<a data-target="' . $this->get_field_id('thumb_pin_img_library') . '" class="button button-secondary open_pins_library" href="#">Open pins library</a> <img class="thumb_pin_img_library_preview" src="' . plugins_url('/images/pins/' . $thumb_pin_img_library, __FILE__) . '">';
    echo '</p>';

    echo '<p class="gmw_thumb_pin_type_predefined colorpicker_section gmw_single_pin_feature"><span class="gmw-label">' . __('Pin Color', 'google-maps-widget') . ':</span>';
    echo '<input data-tooltip="Use the colorpicker to choose a custom color for the pin." class="gmw-colorpicker" data-specialtype="colorpicker" id="' . $this->get_field_id('thumb_pin_color') . '" name="' . $this->get_field_name('thumb_pin_color') . '" type="text" value="' . esc_attr($thumb_pin_color) . '">';
    echo '</p>';

    echo '<p class="gmw_thumb_pin_type_predefined gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_size') . '">' . __('Pin Size', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Location pin size. All sizes besides the large one are quite small." id="' . $this->get_field_id('thumb_pin_size') . '" name="' . $this->get_field_name('thumb_pin_size') . '">';
    GMWP::create_select_options($thumb_pin_sizes, $thumb_pin_size);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_predefined gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_label') . '">' . __('Pin Label', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Due to the pin\'s size only single-letter labels are available." id="' . $this->get_field_id('thumb_pin_label') . '" name="' . $this->get_field_name('thumb_pin_label') . '">';
    GMWP::create_select_options($pin_labels, $thumb_pin_label);
    echo '</select></p>';

    echo '<p class="gmw_thumb_pin_type_custom gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('thumb_pin_img') . '">' . __('Pin Image URL', 'google-maps-widget') . ':</label>';
    echo '<input data-tooltip="Enter the full URL to the image, starting with http://. Image has to be publicly accessible and with size up to 64x64px. Https and localhost are *not* supported." placeholder="http://" type="text" class="regular-text" id="' . $this->get_field_id('thumb_pin_img') . '" name="' . $this->get_field_name('thumb_pin_img') . '" value="' . esc_attr($thumb_pin_img) . '">';
    echo '</p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_link_type') . '">' . __('Link To', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Choose what happens when the map is clicked. Clicks are tracked in Google Analytics if that option is set in settings. Please configure interactive map\'s settings in its tab." class="gmw_thumb_link_type" id="' . $this->get_field_id('thumb_link_type') . '" name="' . $this->get_field_name('thumb_link_type') . '">';
    GMWP::create_select_options($thumb_link_types, $thumb_link_type);
    echo '</select></p>';

    echo '<p class="gmw_thumb_link_section"><label class="gmw-label" for="' . $this->get_field_id('thumb_link') . '">' . __('Custom URL', 'google-maps-widget') . ':</label>';
    echo '<input data-tooltip="Make sure the URL starts with http:// if it leads to a different site." placeholder="http://" class="regular-text" id="' . $this->get_field_id('thumb_link') . '" name="' . $this->get_field_name('thumb_link') . '" type="url" value="' . esc_attr($thumb_link) . '">';
    echo '</p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_format') . '">' . __('Image Format', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Jpg and jpg-baseline typically provide the smallest image size, though they do so through _lossy_ compression which may degrade the image. Gif, PNG8 and PNG32 provide lossless compression." id="' . $this->get_field_id('thumb_format') . '" name="' . $this->get_field_name('thumb_format') . '">';
    GMWP::create_select_options($thumb_formats, $thumb_format);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('thumb_lang') . '">' . __('Map Language', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Not all map labels and texts have translations. Everything is controlled by Google at their discretion. If you choose the auto-detect mode language will be detected from the users browser settings." id="' . $this->get_field_id('thumb_lang') . '" name="' . $this->get_field_name('thumb_lang') . '">';
    GMWP::create_select_options($thumb_langs, $thumb_lang);
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('thumb_header') . '">' . __('Text Above Map', 'google-maps-widget') . ':</label>';
    echo '<textarea placeholder="Header text" data-tooltip="Text that appears above the map. HTML tags and shortcodes are fully supported.
          If you choose to have the thumb replaced by an interactive map this text will be replaced by the interactive header text.
          Use the _{address}_ variable to display the map\'s address." class="widefat" rows="1" cols="20" id="' . $this->get_field_id('thumb_header') . '" name="' . $this->get_field_name('thumb_header') . '">'. esc_textarea($thumb_header) . '</textarea></p>';
    echo '<p><label for="' . $this->get_field_id('thumb_footer') . '">' . __('Text Below Map', 'google-maps-widget') . ':</label>';
    echo '<textarea placeholder="Footer text" data-tooltip="Text that appears below the map. HTML tags and shortcodes are fully supported.
          If you choose to have the thumb replaced by an interactive map this text will be replaced by the interactive header text.
          Use the _{address}_ variable to display the map\'s address." class="widefat" rows="1" cols="20" id="' . $this->get_field_id('thumb_footer') . '" name="' . $this->get_field_name('thumb_footer') . '">'. esc_textarea($thumb_footer) . '</textarea></p>';
    echo '</div>';
    // end - thumbnail tab

    // lightbox tab
    echo '<div id="gmw-lightbox">';
    echo '<p class="gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_mode') . '">' . __('Map Mode', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Place mode displays a map pin at a defined place or address.
          Directions mode displays the path between a start address defined below, and destination defined in the map\'s address.
          Search mode displays results for a search across the area around the map\'s address.
          View mode returns a map with no markers or directions; it produces a very clean map.
          Street View provides panoramic views on the designated location. Please note that it\'s not available on all locations." class="gmw_lightbox_mode" id="' . $this->get_field_id('lightbox_mode') . '" name="' . $this->get_field_name('lightbox_mode') . '">';
    GMWP::create_select_options($lightbox_modes, $lightbox_mode);
    echo '</select></p>';

    echo '<p class="gmw_lightbox_mode_search gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_search') . '">' . __('Search Query', 'google-maps-widget') . ':</label>';
    echo '<input data-tooltip="The search term, ie: pizza. It can include a geographic restriction, such as \'In New York\'. Location should be the same as the main map address." placeholder="' . __('Pizza in New York', 'google-maps-widget') . '" id="' . $this->get_field_id('lightbox_search') . '" name="' . $this->get_field_name('lightbox_search') . '" type="text" value="' . esc_attr($lightbox_search) . '">';
    echo '</p>';

    echo '<p class="gmw_lightbox_mode_directions gmw_single_pin_feature">';
    echo '<label class="gmw-label" for="' . $this->get_field_id('lightbox_origin') . '">' . __('Start Address', 'google-maps-widget') . ':</label>';
    echo '<span class="input-address-group">';
    echo '<input data-tooltip="Start address for directions. Destination is defined in the map\'s address." id="' . $this->get_field_id('lightbox_origin') . '" name="' . $this->get_field_name('lightbox_origin') . '" type="text" value="' . esc_attr($lightbox_origin) . '">';
    echo '<a href="#" data-target="' . $this->get_field_id('lightbox_origin') . '" class="button-secondary gmw-pick-address"><span class="dashicons dashicons-location"></span></a>';
    echo '</span>';
    echo '</p>';

    echo '<p class="gmw_lightbox_mode_directions gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_unit') . '">' . __('Distance Units', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Units in which the distance is measured." id="' . $this->get_field_id('lightbox_unit') . '" name="' . $this->get_field_name('lightbox_unit') . '">';
    GMWP::create_select_options($lightbox_units, $lightbox_unit);
    echo '</select></p>';

    echo '<p class="gmw_lightbox_mode_streetview gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_heading') . '">' . __('Streetview', 'google-maps-widget') . ':</label>';
    echo 'Heading: <input data-title="Streetview Camera Heading" data-tooltip="Indicates the compass heading of the camera in degrees clockwise from North. Accepted values are from -180° to 360°." class="small-text" min="-180" max="360" step="1" id="' . $this->get_field_id('lightbox_heading') . '" type="number" name="' . $this->get_field_name('lightbox_heading') . '" value="' . esc_attr($lightbox_heading) . '"> ';
    echo 'Pitch: <input data-title="Streetview Camera Pitch" data-tooltip="Specifies the angle, up or down, of the camera. The pitch is specified in degrees from -90° to 90°. Positive values will angle the camera up, while negative values will angle the camera down." class="small-text" id="' . $this->get_field_id('lightbox_pitch') . '" name="' . $this->get_field_name('lightbox_pitch') . '" type="number" step="1" min="-90" max="90" value="' . esc_attr($lightbox_pitch) . '"> degrees';
    echo '</p>';

    echo '<p class="gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_map_type') . '">' . __('Map Type', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Controls the map type." id="' . $this->get_field_id('lightbox_map_type') . '" name="' . $this->get_field_name('lightbox_map_type') . '">';
    GMWP::create_select_options($lightbox_map_types, $lightbox_map_type);
    echo '</select></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_map_type_multiple') . '">' . __('Map Type', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Controls the map type." id="' . $this->get_field_id('lightbox_map_type_multiple') . '" name="' . $this->get_field_name('lightbox_map_type_multiple') . '">';
    GMWP::create_select_options($thumb_map_types, $lightbox_map_type_multiple);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_zoom') . '">' . __('Zoom Level', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Zoom varies from the lowest level, in which the entire world can be seen, to highest, which shows streets and individual buildings. Building outlines, where available, appear on the map around zoom level 17. This value differs from area to area." id="' . $this->get_field_id('lightbox_zoom') . '" name="' . $this->get_field_name('lightbox_zoom') . '">';
    GMWP::create_select_options($zoom_levels_lightbox, $lightbox_zoom);
    echo '</select></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_clustering') . '">' . __('Pins Clustering', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Clustering enables pins grouping depending on their distances on the current view. It declutters the map when a lot of pins are displayed." id="' . $this->get_field_id('lightbox_clustering') . '" name="' . $this->get_field_name('lightbox_clustering') . '">';
    GMWP::create_select_options($lightbox_clustering_options, $lightbox_clustering);
    echo '</select></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_filtering') . '">' . __('Pins Filtering', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Filtering gives users the ability to show/hide pins based on group name(s) set for each pin. Additional GUI is displayed in the upper right corner of the map." id="' . $this->get_field_id('lightbox_filtering') . '" name="' . $this->get_field_name('lightbox_filtering') . '">';
    GMWP::create_select_options($lightbox_filtering_options, $lightbox_filtering);
    echo '</select></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_color_scheme') . '">' . __('Color Scheme', 'google-maps-widget') . ':</label>';
    echo '<select class="gmw_lightbox_color_scheme" data-tooltip="Changes the overall appearance of the map. Please note that most visitors are acustomed to the default color scheme.<br>Color schemes work only on default (road) map type." id="' . $this->get_field_id('lightbox_color_scheme') . '" name="' . $this->get_field_name('lightbox_color_scheme') . '">';
    GMWP::create_select_options($lightbox_color_schemes, $lightbox_color_scheme);
    echo '</select></p>';

    echo '<p class="lightbox_color_scheme_custom gmw_multiple_pins_feature"><label style="margin-top: -40px;" class="gmw-label" for="' . $this->get_field_id('lightbox_color_scheme_custom') . '">' . __('Custom Color Scheme', 'google-maps-widget') . ':</label>';
    echo '<textarea data-tooltip="Only use JSON formatted (JS style) custom style.<br><a target=\'blank\' href=\'https://snazzymaps.com/\'>Snazzy Maps</a> offers over 15,000 custom map styles you can customize. <a target=\'blank\' href=\'http://www.mapstylr.com/map-style-editor/\'>Mapstylr</a> and <a target=\'blank\' href=\'https://mapstyle.withgoogle.com/\'>Mapstyle</a> offer style builders too. Refer to <a target=\'blank\' href=\'http://www.gmapswidget.com/documentation/\'>documentation</a> for more details." class="" id="' . $this->get_field_id('lightbox_color_scheme_custom') . '" rows="2" cols="20" placeholder="JSON formatted custom map style" name="' . $this->get_field_name('lightbox_color_scheme_custom') . '">';
    echo esc_textarea($lightbox_color_scheme_custom);
    echo '</textarea></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_layer') . '">' . __('Map Layers', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Traffic - displays traffic conditions on the map
Transit - displays the public transport network of your city on the map
Bicycling - renders a layer of bike paths and/or bicycle-specific overlays" class="gmw-select2" data-placeholder="' . __('Click to choose layers', 'google-maps-widget') . '" multiple="multiple" id="' . $this->get_field_id('lightbox_layer') . '" name="' . $this->get_field_name('lightbox_layer') . '[]">';
    GMWP::create_select_options($lightbox_layers, $lightbox_layer);
    echo '</select></p>';

    echo '<p class="gmw_multiple_pins_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_lock') . '">' . __('Map Features', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="By disabling various map features, you can make the map more user-friendly by removing distracting elements users don\'t need" multiple="multiple" class="gmw-select2" data-placeholder="' . __('Click to disable interactive map features', 'google-maps-widget') . '" id="' . $this->get_field_id('lightbox_lock') . '" name="' . $this->get_field_name('lightbox_lock') . '[]">';
    GMWP::create_select_options($lightbox_locks, $lightbox_lock);
    echo '</select></p>';

    echo '<p class="gmw_single_pin_feature"><label class="gmw-label" for="' . $this->get_field_id('lightbox_lang') . '">' . __('Map Language', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Not all map labels and texts have translations. Everything is controlled by Google at their discretion. If you choose the auto-detect mode language will be detected from the users browser settings." id="' . $this->get_field_id('lightbox_lang') . '" name="' . $this->get_field_name('lightbox_lang') . '">';
    GMWP::create_select_options($lightbox_langs, $lightbox_lang);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_fullscreen') . '">' . __('Lightbox Size', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Choose from a custom size or a fullscreen, border-to-border option for the lightbox map." class="gmw_lightbox_fullscreen" id="' . $this->get_field_id('lightbox_fullscreen') . '" name="' . $this->get_field_name('lightbox_fullscreen') . '">';
    GMWP::create_select_options($lightbox_sizes, $lightbox_fullscreen);
    echo '</select>';
    echo '<span class="gmw_lightbox_fullscreen_custom_section"><span class="gmw-label label-holder">&nbsp;</span>';
    echo '<input data-title="Map Width" data-tooltip="Interactive map width in pixels; from 50 to 2000. If needed, map will be resized to accomodate for smaller screens." class="small-text fullscreen_fix" min="50" max="2000" step="1" id="' . $this->get_field_id('lightbox_width') . '" type="number" name="' . $this->get_field_name('lightbox_width') . '" value="' . esc_attr($lightbox_width) . '" required="required"> x ';
    echo '<input data-title="Map Height" data-tooltip="Interactive map height in pixels; from 50 to 2000. If needed, map will be resized to accomodate for smaller screens." class="small-text" id="' . $this->get_field_id('lightbox_height') . '" name="' . $this->get_field_name('lightbox_height') . '" type="number" step="1" min="50" max="2000" value="' . esc_attr($lightbox_height) . '" required="required"> px</span></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_skin') . '">' . __('Lightbox Skin', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Controls the overall appearance of the lightbox, not the map itself. Adjust according to your site\'s design." class="gmw_lightbox_skin" id="' . $this->get_field_id('lightbox_skin') . '" name="' . $this->get_field_name('lightbox_skin') . '">';
    GMWP::create_select_options($lightbox_skins, $lightbox_skin);
    echo '</select></p>';

    echo '<p><label class="gmw-label" for="' . $this->get_field_id('lightbox_feature') . '">' . __('Lightbox Features', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Title is taken from the widget title field. Not all skins have a title, and the ones that do have it in different places, so please test your maps.
          Other 3 options control the way users close the lightbox. Enable at least one of them." class="gmw-select2" data-placeholder="' . __('Click to choose features', 'google-maps-widget') . '" multiple="multiple" id="' . $this->get_field_id('lightbox_feature') . '" name="' . $this->get_field_name('lightbox_feature') . '[]">';
    GMWP::create_select_options($lightbox_features, $lightbox_feature);
    echo '</select></p>';

    echo '<p><label for="' . $this->get_field_id('lightbox_header') . '">' . __('Header Text', 'google-maps-widget') . ':</label>';
    echo '<textarea placeholder="Header text" data-tooltip="Text that appears above the interactive map. HTML tags and shortcodes are fully supported.
         Use the _{address}_ variable to display the map\'s address." class="widefat" rows="1" cols="20" id="' . $this->get_field_id('lightbox_header') . '" name="' . $this->get_field_name('lightbox_header') . '">'. esc_textarea($lightbox_header) . '</textarea></p>';

    echo '<p><label for="' . $this->get_field_id('lightbox_footer') . '">' . __('Footer Text', 'google-maps-widget') . ':</label>';
    echo '<textarea placeholder="Footer text" data-tooltip="Text that appears below the interactive map. HTML tags and shortcodes are fully supported.
         Use the _{address}_ variable to display the map\'s address." class="widefat" rows="1" cols="20" id="' . $this->get_field_id('lightbox_footer') . '" name="' . $this->get_field_name('lightbox_footer') . '">'. esc_textarea($lightbox_footer) . '</textarea></p>';

    echo '</div>';
    // end - lightbox tab

    // pins tab
    echo '<div id="gmw-pins">';
    echo '<input class="multiple_pins" id="' . $this->get_field_id('multiple_pins') . '" type="hidden" name="' . $this->get_field_name('multiple_pins') . '" value="' . esc_attr($multiple_pins) . '">';

    // blank pin
    echo '<div class="gmw-blank-pin" style="display: none;">';
    echo '<div class="gmw-pin-group">';
    echo '<h4>Pin ## <a class="dashicons dashicons-trash gmw-del-pin" href="#" title="Delete pin"></a></h4>';
    echo '<div class="gmw_pin_container">';
    echo '<div class="input-address-group">';
    echo '<input disabled="disabled" name="' . $this->get_field_name('pins') . '[address][]" type="text" value="" class="widefat" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[address]-##') . '" placeholder="' . __('Address / pin location', 'google-maps-widget') . '" data-tooltip="' . htmlspecialchars('Address or location used for both maps. Coordinates can be used as well. Write them in a numerical fashion, not in degrees, ie: 40.70823, -74.01052.') . '">';
    echo '<a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[address]-##', false) . '" href="#" class="button-secondary gmw-pick-address"><span class="dashicons dashicons-location"></span></a>';
    echo '</div>';

    echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[show_on]-##') . '">' . __('Show on', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="A pin can be shown only on the thumbnail map, only on the interactive map, or by default on both maps." disabled="disabled" name="' . $this->get_field_name('pins') . '[show_on][]" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[show_on]-##', false) . '">';
    GMWP::create_select_options($show_on_options, 0);
    echo '</select></p>';

    echo '<p class="colorpicker_section"><span class="gmw-label">' . __('Thumbnail Pin Color', 'google-maps-widget') . ':</span>';
    echo '<input disabled="disabled" data-tooltip="Use the colorpicker to choose a custom color for the thumbnail pin." class="gmw-colorpicker gmw-skip-colorpicker-init" data-specialtype="colorpicker" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[thumb_pin_color]-##') . '" name="' . $this->get_field_name('pins') . '[thumb_pin_color][]' . '" type="text" value="' . self::$defaults['pins'][0]['thumb_pin_color'] . '">';
    echo '</p>';

    echo '<p><span class="gmw-label">' . __('Interactive Pin', 'google-maps-widget') . ':</span>';
    echo '<input disabled="disabled" class="thumb_pin_img_library" type="hidden" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img]-##') . '" name="' . $this->get_field_name('pins') . '[interactive_pin_img][]" value="' . esc_attr(self::$defaults['pins'][0]['interactive_pin_img']) . '">';
    echo '<a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img]-##', false) . '" class="button button-secondary open_pins_library" href="#">Open pins library</a> <a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img]-##', false) . '" href="#" class="button button-secondary gmw_set_custom_pin">Set custom pin</a> <img class="thumb_pin_img_library_preview" src="' . GMW_PLUGIN_URL . 'images/pins/' . self::$defaults['pins'][0]['interactive_pin_img'] . '">';
    echo '</p>';

    echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_click]-##') . '">' . __('On Interactive Pin Click', 'google-maps-widget') . ':</label>';
    echo '<select data-tooltip="Clicking on pins placed on interactive maps can: open/close description bubbles with arbitrary content, open a user-defined link, or do nothing." class="interactive_pin_click" disabled="disabled" name="' . $this->get_field_name('pins') . '[interactive_pin_click][]" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_click]-##', false) . '">';
    GMWP::create_select_options($interactive_pin_click_options, 1);
    echo '</select></p>';

    echo '<p class="pin-click-url"><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_url]-##') . '">' . __('Pin Click URL', 'google-maps-widget') . ':</label>';
    echo '<input type="text" data-tooltip="All links leading to external sites have to start with _https://_ or _http://_" disabled="disabled" name="' . $this->get_field_name('pins') . '[interactive_pin_url][]" value="" placeholder="Interactive pin click target URL" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_url]-##', false) . '">';
    echo '</p>';

    echo '<p class="pin-description"><label for="' . GMWP::field_name_to_id($this->get_field_name('pins')) . '[description]-##">' . __('Description', 'google-maps-widget') . ':</label>';
    echo '<textarea placeholder="Pin description shown on click" disabled="disabled" data-tooltip="Pin description shown when user clicks on it. Supports HTML and shortcodes. Use CSS on _div.gmw_infowindow_ to style it." class="widefat" rows="1" cols="20" id="' . GMWP::field_name_to_id($this->get_field_name('pins'), false) . '[description]-##" name="' . $this->get_field_name('pins') . '[description][]">'. esc_textarea(self::$defaults['pins'][0]['description']) . '</textarea></p>';

    echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[group]-##') . '">' . __('Group Name', 'google-maps-widget') . ':</label>';
    echo '<input type="text" data-tooltip="Filtering (when enabled in interactive map options) is based on group names. Names are completely arbitrary and case-sensitive - make sure they are the same between multiple pins. Separate multiple names by a comma." disabled="disabled" name="' . $this->get_field_name('pins') . '[group][]" value="" placeholder="Pin group #1, Pin group #2" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[group]-##', false) . '">';
    echo '</p>';

    echo '</div>'; // pin container
    echo '</div>'; // pin group
    echo '</div>'; // blank pin

    // pins active
    echo '<div class="gmw_multiple_pins_feature gmw_accordion_wrapper">';
    echo '<div class="gmw-accordion">';
    $i = 0;
    foreach ($pins as $pin) {
      $i++;
      echo '<div class="gmw-pin-group">';
      echo '<h4>' . (empty($pin['address'])? 'Pin #' . $i: $pin['address']) . ' <a class="dashicons dashicons-trash gmw-del-pin" href="#" title="Delete pin"></a></h4>';
      echo '<div class="gmw_pin_container">';
      echo '<div class="input-address-group">';
      echo '<input name="' . $this->get_field_name('pins') . '[address][]" type="text" value="' . esc_attr($pin['address']) . '" class="widefat" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[address][]') . '" placeholder="' . __('Address / pin location', 'google-maps-widget') . '" data-tooltip="' . htmlspecialchars('Address or location used for both maps. Coordinates can be used as well. Write them in a numerical fashion, not in degrees, ie: 40.70823, -74.01052.') . '">';
      echo '<a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[address][]', false) . '" href="#" class="button-secondary gmw-pick-address"><span class="dashicons dashicons-location"></span></a>';
      echo '</div>';

      echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[show_on][]', true) . '">' . __('Show on', 'google-maps-widget') . ':</label>';
      echo '<select data-tooltip="A pin can be shown only on the thumbnail map, only on the interactive map, or by default on both maps." name="' . $this->get_field_name('pins') . '[show_on][]" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[show_on][]', false) . '">';
      GMWP::create_select_options($show_on_options, $pin['show_on']);
      echo '</select></p>';

      echo '<p class="colorpicker_section"><span class="gmw-label">' . __('Thumbnail Pin Color', 'google-maps-widget') . ':</span>';
      echo '<input data-tooltip="Use the colorpicker to choose a custom color for the thumbnail pin." class="gmw-colorpicker" data-specialtype="colorpicker" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[thumb_pin_color][]', false) . '" name="' . $this->get_field_name('pins') . '[thumb_pin_color][]' . '" type="text" value="' . esc_attr($pin['thumb_pin_color']) . '">';
      echo '</p>';

      echo '<p><span class="gmw-label">' . __('Interactive Pin', 'google-maps-widget') . ':</span>';
      echo '<input class="thumb_pin_img_library" type="hidden" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img][]') . '" name="' . $this->get_field_name('pins') . '[interactive_pin_img][]" value="' . esc_attr($pin['interactive_pin_img']) . '">';
      echo '<a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img][]', false) . '" class="button button-secondary open_pins_library" href="#">Open pins library</a> <a data-target="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_img][]', false) . '" href="#" class="button button-secondary gmw_set_custom_pin">Set custom pin</a>';
      if (strpos($pin['interactive_pin_img'], '//') !== false) {
        echo '<img class="thumb_pin_img_library_preview" src="' . $pin['interactive_pin_img'] . '">';
      } else {
        echo '<img class="thumb_pin_img_library_preview" src="' . GMW_PLUGIN_URL . 'images/pins/' . $pin['interactive_pin_img'] . '">';
      }
      echo '</p>';

      echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_click][]') . '">' . __('On Interactive Pin Click', 'google-maps-widget') . ':</label>';
      echo '<select class="interactive_pin_click" data-tooltip="Clicking on pins placed on interactive maps can: open/close description bubbles with arbitrary content, open a user-defined link, or do nothing." name="' . $this->get_field_name('pins') . '[interactive_pin_click][]" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_click][]', false) . '">';
      GMWP::create_select_options($interactive_pin_click_options, $pin['interactive_pin_click']);
      echo '</select></p>';

      echo '<p class="pin-click-url"><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_url][]') . '">' . __('Pin Click URL', 'google-maps-widget') . ':</label>';
      echo '<input type="text" data-tooltip="All links leading to external sites have to start with _https://_ or _http://_" name="' . $this->get_field_name('pins') . '[interactive_pin_url][]" value="' . esc_attr($pin['interactive_pin_url']) . '" placeholder="Interactive pin click target URL" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[interactive_pin_url][]', false) . '">';
      echo '</p>';

      echo '<p class="pin-description"><label for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[description][]') . '">' . __('Description', 'google-maps-widget') . ':</label>';
      echo '<textarea data-tooltip="Pin description shown when user clicks on it. Supports HTML and shortcodes. Use CSS on _div.gmw_infowindow_ to style it." placeholder="Pin description shown on click" class="widefat" rows="1" cols="20" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[description][]', false) . '" name="' . $this->get_field_name('pins') . '[description][]">'. esc_textarea($pin['description']) . '</textarea></p>';

      echo '<p><label class="gmw-label" for="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[group][]') . '">' . __('Group Name', 'google-maps-widget') . ':</label>';
      echo '<input type="text" data-tooltip="Filtering (when enabled in interactive map options) is based on group names. Names are completely arbitrary and case-sensitive - make sure they are the same between multiple pins. Separate multiple names by a comma." name="' . $this->get_field_name('pins') . '[group][]" value="' . esc_attr($pin['group']) . '" placeholder="Pin group #1, Pin group #2" id="' . GMWP::field_name_to_id($this->get_field_name('pins') . '[group][]', false) . '">';
      echo '</p>';

      echo '</div>'; // pin container
      echo '</div>'; // pin group
    } // foreach pins
    echo '</div>'; // accordion

    echo '<p class="gmw_new_pin_container" title="Add new pin"><span class="ui-state-default ui-icon ui-icon-plus"></span><span>Add new pin</span></p>';

    echo '<p><br><a href="#" class="gmw_disable_multiple_pins">Disable multiple pins</a> (no settings will be lost)</p>'; echo '</div>';

    // pins disabled
    echo '<div class="gmw_single_pin_feature">';
    echo '<p>To simplify editing, by default, multiple pins feature is disabled. Enabling it will not reset the widget or alter any settings.</p>';
    echo '<p><br><a href="#" class="button button-secondary gmw_enable_multiple_pins">Enable multiple pins</a></p>';
    echo '</div>';

    echo '</div>';
    // end - pins tab

    // shortcode tab -> tools
    echo '<div id="gmw-shortcode">';
    $id = str_replace('googlemapswidget-', '', $this->id);

    echo '<h4>' . __('Shortcode', 'google-maps-widget') . '</h4>';
    if (empty($id) || !is_numeric($id)) {
      echo '<p>' . __('Please save the widget so that the shortcode can be generated.', 'google-maps-widget') . '</p>';
    } else {
      echo '<p><code>[' . $options['sc_map'] . ' thumb_width="' . $thumb_width . '" thumb_height="' . $thumb_width . '" id="' . $id . '"]</code><br></p>';
      echo '<p>' . __('Use the above shortcode to display this Google Maps Widget instance in any page or post. <br>Please note that your theme might style the widget in the post as if it is placed in a sidebar. In that case use the <code>div.gmw-shortcode-widget</code> class to target the shortcode and make  necessary changes via CSS.', 'google-maps-widget') . '</p>';
    }

    echo '<h4>' . __('Import pins', 'google-maps-widget') . '</h4>';
    echo '<p>If you need to add more than 5-10 pins importing them is probably a better &amp; faster idea. Sample CSV file and detailed instructions are available in <a href="' . admin_url('options-general.php?page=gmw_options&widget_id=' . $id . '&gmw-settings-tabs=1') .'">GMW options - Import Pins</a>.</p>';

    echo '</div>';
    // end - shortcode tab

    // info tab
    echo '<div id="gmw-info">';
    echo '<h4>' . __('Support', 'google-maps-widget') . '</h4>';
    echo '<p>If you have any problems, questions or would like a new feature added, please contact our support <a href="mailto:gmw@webfactoryltd.com?subject=GMW%20support">via email</a>. As a paying customer you have access to premium, prioritised support.';
    echo '</p>';

    echo '<h4>' . __('License', 'google-maps-widget') . '</h4>';
    echo '<p>Your license is active and valid. Additional info is available in <a href="' . admin_url('options-general.php?page=gmw_options') . '" title="Settings">settings</a>.</p>';

    echo '<h4>' . __('Rate the plugin &amp; spread the word', 'google-maps-widget') . '</h4>';
    echo '<p>It won\'t take you more than a minute, but it will help us immensely. So please - <a href="https://wordpress.org/support/view/plugin-reviews/google-maps-widget" target="_blank">rate the plugin</a>. Or spread the word by <a href="https://twitter.com/intent/tweet?via=WebFactoryLtd&amp;text=' . urlencode('I\'m using the #free Google Maps Widget for #wordpress. You can grab it too at http://goo.gl/2qcbbf') . '" target="_blank">tweeting about it</a>. Thank you!</p>';
    echo '</div>';
    // end - info tab
    echo '</div><p></p>'; // tabs

    echo '<p class="widget_footer_info">' . sprintf(__('Additional options are available in <a href="%s" title="Settings">settings</a>. ', 'google-maps-widget'), admin_url('options-general.php?page=gmw_options')) . __('All options are explained in details in the <a href="https://www.gmapswidget.com/documentation/" target="_blank">documentation</a>. If you need further help, please contact <a href="mailto:gmw@webfactoryltd.com?subject=GMW%20support">support</a>.', 'google-maps-widget') . '</p>';
  } // form


  // update/save widget options
  function update($new_instance, $old_instance) {
    $instance = array();
    if (!is_array($old_instance)) {
      $old_instance = array();
    }

    if (sizeof($new_instance) < 10) {
      return $old_instance;
    }

    $instance['title'] = $new_instance['title'];
    $instance['address'] = strip_tags(trim($new_instance['address']));

    $instance['thumb_pin_type'] = $new_instance['thumb_pin_type'];
    $instance['thumb_pin_color'] = GMWP::sanitize_hex_color($new_instance['thumb_pin_color']);
    $instance['thumb_pin_size'] = $new_instance['thumb_pin_size'];
    $instance['thumb_pin_label'] = $new_instance['thumb_pin_label'];
    $instance['thumb_pin_img'] = trim($new_instance['thumb_pin_img']);
    $instance['thumb_pin_img_library'] = trim($new_instance['thumb_pin_img_library']);
    $instance['thumb_width'] = min(640, max(50, (int) $new_instance['thumb_width']));
    $instance['thumb_height'] = min(640, max(50, (int) $new_instance['thumb_height']));
    $instance['thumb_zoom'] = $new_instance['thumb_zoom'];
    $instance['thumb_type'] = $new_instance['thumb_type'];
    $instance['thumb_link_type'] = $new_instance['thumb_link_type'];
    $instance['thumb_link'] = trim($new_instance['thumb_link']);
    $instance['thumb_header'] = trim($new_instance['thumb_header']);
    $instance['thumb_footer'] = trim($new_instance['thumb_footer']);
    $instance['thumb_color_scheme'] = $new_instance['thumb_color_scheme'];
    $instance['thumb_color_scheme_custom'] = trim($new_instance['thumb_color_scheme_custom']);
    $instance['thumb_format'] = $new_instance['thumb_format'];
    $instance['thumb_lang'] = $new_instance['thumb_lang'];

    $instance['lightbox_fullscreen'] = (int) $new_instance['lightbox_fullscreen'];
    $instance['lightbox_width'] = min(2000, max(50, (int) $new_instance['lightbox_width']));
    $instance['lightbox_height'] = min(2000, max(50, (int) $new_instance['lightbox_height']));
    $instance['lightbox_mode'] = $new_instance['lightbox_mode'];
    $instance['lightbox_origin'] = trim($new_instance['lightbox_origin']);
    $instance['lightbox_search'] = trim($new_instance['lightbox_search']);
    $instance['lightbox_unit'] = $new_instance['lightbox_unit'];
    $instance['lightbox_heading'] = min(360, max(-180, (int) $new_instance['lightbox_heading']));
    $instance['lightbox_pitch'] = min(90, max(-90, (int) $new_instance['lightbox_pitch']));
    $instance['lightbox_map_type'] = $new_instance['lightbox_map_type'];
    $instance['lightbox_map_type_multiple'] = $new_instance['lightbox_map_type_multiple'];
    $instance['lightbox_zoom'] = $new_instance['lightbox_zoom'];
    $instance['lightbox_clustering'] = $new_instance['lightbox_clustering'];
    $instance['lightbox_filtering'] = $new_instance['lightbox_filtering'];
    $instance['lightbox_feature'] = (array) $new_instance['lightbox_feature'];
    $instance['lightbox_header'] = trim($new_instance['lightbox_header']);
    $instance['lightbox_footer'] = trim($new_instance['lightbox_footer']);
    $instance['lightbox_skin'] = $new_instance['lightbox_skin'];
    $instance['lightbox_lang'] = $new_instance['lightbox_lang'];

    $instance['lightbox_lock'] = (array) $new_instance['lightbox_lock'];
    $instance['lightbox_layer'] = (array) $new_instance['lightbox_layer'];
    $instance['lightbox_color_scheme'] = $new_instance['lightbox_color_scheme'];
    $instance['lightbox_color_scheme_custom'] = trim($new_instance['lightbox_color_scheme_custom']);
    $instance['multiple_pins'] = (int) $new_instance['multiple_pins'];
    $tmp = sizeof($new_instance['pins']['show_on']);
    $instance['pins'] = array();

	  for ($i = 0; $i < $tmp; $i++) {
      $pin = array('address' => trim($new_instance['pins']['address'][$i]),
                   'thumb_pin_color' => GMWP::sanitize_hex_color($new_instance['pins']['thumb_pin_color'][$i]),
                   'description' => trim($new_instance['pins']['description'][$i]),
                   'interactive_pin_click' => trim($new_instance['pins']['interactive_pin_click'][$i]),
                   'interactive_pin_img' => trim($new_instance['pins']['interactive_pin_img'][$i]),
                   'interactive_pin_url' => trim($new_instance['pins']['interactive_pin_url'][$i]),
                   'show_on' => $new_instance['pins']['show_on'][$i],
                   'group' => trim(str_replace('  ', ' ', $new_instance['pins']['group'][$i])));

      if (preg_match('|^([-+]?\d{1,2}([.]\d+)?),\s*([-+]?\d{1,3}([.]\d+)?)$|', $new_instance['pins']['address'][$i])) {
        $pin['latlng'] = true;
        $tmp2 = explode(',', $new_instance['pins']['address'][$i]);
        $pin['lat'] = trim($tmp2[0]);
        $pin['lng'] = trim($tmp2[1]);
		    $pin['from_cache'] = false;
		    $pin['cache_address'] = false;
      } else {
        $cache = GMWP::get_coordinates($new_instance['pins']['address'][$i], false);
        if ($cache) {
          $pin['lat'] = $cache['lat'];
          $pin['lng'] = $cache['lng'];
          $pin['cache_address'] = $cache['address'];
          $pin['from_cache'] = true;
          $pin['latlng'] = true;
        } else {
          $pin['from_cache'] = false;
          $pin['latlng'] = false;
        }
      }

      $instance['pins'][] = $pin;
    } // for pins

    $instance['core_ver'] = GMWP::$version;

    return $instance;
  } // update


  // output widget
  function widget($widget, $instance) {
    $out = $widget_content = $style = $markers = '';
    $map_params = array();
    $marker_label = 65;

    $options = GMWP::get_options();
    $instance = $this->upgrade_wiget_instance($instance);

    // check license
    if (!GMWP::is_activated()) {
      return false;
    }

    $map_src = '//maps.googleapis.com/maps/api/staticmap';

    // make sure all params are defined
    $instance = wp_parse_args((array) $instance, self::$defaults);
    $instance['id'] = $widget['widget_id'];

    // build thumbnail map parameters
    if (GMWP::get_api_key('static')) {
      $map_params['key'] = GMWP::get_api_key('static');
    }
    $map_params['scale'] = 1;
    $map_params['format'] = $instance['thumb_format'];
    $map_params['size'] = $instance['thumb_width'] . 'x' . $instance['thumb_height'];
    if ($instance['thumb_lang'] != 'auto') {
      $map_params['language'] = $instance['thumb_lang'];
    }
    $map_params['maptype'] = $instance['thumb_type'];

    $center = '';
    if ($instance['multiple_pins']) { // multiple pins
      foreach ($instance['pins'] as $pin) {
        if (empty($pin['address']) || $pin['show_on'] == '2') {
          continue;
        }
        if (!$center) {
          $center = $pin['address'];
        }
        $markers .= 'markers=color%3A' . str_replace('#', '0x', $pin['thumb_pin_color']) . '%7Clabel%3A' . chr($marker_label++) . '%7Csize%3Adefault%7C' . urlencode($pin['address']) . '&amp;';
      }
      if ($markers) {
        $markers = '&amp;' . substr($markers, 0, -5);
      }
      if ($instance['thumb_zoom'] != 'auto') {
        $map_params['zoom'] = $instance['thumb_zoom'];
        $map_params['center'] = $center;
      }
    } else { // single pin
      if ($instance['thumb_pin_type'] == 'custom') {
        $map_params['markers'] = 'icon:' . $instance['thumb_pin_img'];
      } elseif ($instance['thumb_pin_type'] == 'custom-library') {
        $map_params['markers'] = 'icon:' . plugins_url('/images/pins/' . $instance['thumb_pin_img_library'], __FILE__);
      } else {
        $map_params['markers'] = 'size:' . $instance['thumb_pin_size'] . '|color:' . str_replace('#', '0x', $instance['thumb_pin_color']) . '|label:' . $instance['thumb_pin_label'];
      }
      $map_params['markers'] .= '|' . $instance['address'];
      $map_params['center'] = $instance['address'];
      if ($instance['thumb_zoom'] != 'auto') {
        $map_params['zoom'] = $instance['thumb_zoom'];
      }
    } // single pin

    if ($instance['thumb_color_scheme'] == 'new') {
      $map_params['visual_refresh'] = 'true';
    } elseif ($instance['thumb_color_scheme'] == 'custom') {
      $map_params['visual_refresh'] = 'false';
      $style = '&amp;style=' . str_replace('&', '&amp;', GMWP_styles::convert_json2url($instance['thumb_color_scheme_custom']));
    } elseif ($instance['thumb_color_scheme'] != 'default') {
      $map_params['visual_refresh'] = 'false';
      $style = '&amp;' . str_replace('&', '&amp;', GMWP_styles::$php_styles[$instance['thumb_color_scheme']]);
    }

    // start building widget markup
    $out .= $widget['before_widget'];

    // add widget title; respect sidebar markup
    $title = empty($instance['title'])? '' : apply_filters('widget_title', $instance['title']);
    if (!empty($title)) {
      $out .= $widget['before_title'] . $title . $widget['after_title'];
    }

    // if not empty, add header text
    if (!empty($instance['thumb_header'])) {
      $tmp = str_ireplace(array('{address}'), array($instance['address']), $instance['thumb_header']);
      $widget_content .= wpautop(do_shortcode($tmp));
    }
    $widget_content .= '<p>';

    if ($instance['thumb_link_type'] == 'lightbox') {
      self::$widgets[$widget['widget_id']] = $instance;
      $map_alt = __('Click to open a larger map', 'google-maps-widget');
      $widget_content .= '<a data-widget-id="' . $widget['widget_id'] . '" class="gmw-thumbnail-map gmw-lightbox-enabled" href="#gmw-dialog-' . $widget['widget_id'] . '" title="' . __('Click to open a larger map', 'google-maps-widget') . '">';
    } elseif ($instance['thumb_link_type'] == 'interactive') {
      self::$widgets[$widget['widget_id']] = $instance;
      $map_alt = __('Interactive map will load automatically in a few moments', 'google-maps-widget');
      $widget_content .= '<a data-widget-id="' . $widget['widget_id'] . '" class="gmw-thumbnail-map gmw-replace-enabled gmw-auto-replace" href="#gmw-dialog-' . $widget['widget_id'] . '" title="' . __('Click to open an interactive map', 'google-maps-widget') . '">';
    } elseif ($instance['thumb_link_type'] == 'replace') {
      self::$widgets[$widget['widget_id']] = $instance;
      $map_alt = __('Click to open the interactive map', 'google-maps-widget');
      $widget_content .= '<a data-widget-id="' . $widget['widget_id'] . '" class="gmw-thumbnail-map gmw-replace-enabled" href="#gmw-dialog-' . $widget['widget_id'] . '" title="' . __('Click to open an interactive map', 'google-maps-widget') . '">';
    } elseif ($instance['thumb_link_type'] == 'custom') {
      $map_alt = esc_attr($instance['address']);
      $widget_content .= '<a class="gmw-thumbnail-map" title="' . esc_attr($instance['address']) . '" href="' . $instance['thumb_link'] . '">';
    } elseif ($instance['thumb_link_type'] == 'custom_blank') {
      $map_alt = esc_attr($instance['address']);
      $widget_content .= '<a class="gmw-thumbnail-map" title="' . esc_attr($instance['address']) . '" target="_blank" href="' . $instance['thumb_link'] . '">';
    } elseif ($instance['thumb_link_type'] == 'map_blank') {
      $map_alt = __('Click to open the interactive map in a new window', 'google-maps-widget');
      $map_url = GMWP::build_lightbox_url($instance);
      $widget_content .= '<a class="gmw-thumbnail-map" title="' . esc_attr($instance['address']) . '" target="_blank" href="' . $map_url . '">';
    } elseif ($instance['thumb_link_type'] == 'nolink') {
      $map_alt = esc_attr($instance['address']);
    }

    // build map image source
    $map_src .= '?' . http_build_query($map_params, null, '&amp;') . $markers . $style;
    $map_src = apply_filters('gmw_thumb_map_src', $map_src, $instance);
    $widget_content .= '<img width="' . $instance['thumb_width'] . 'px" height="' . $instance['thumb_height'] . 'px" alt="' . $map_alt . '" title="' . $map_alt . '" src="' . $map_src . '">';

    if ($instance['thumb_link_type'] == 'lightbox' ||
        $instance['thumb_link_type'] == 'replace' ||
        $instance['thumb_link_type'] == 'interactive' ||
        $instance['thumb_link_type'] == 'custom' ||
        $instance['thumb_link_type'] == 'custom_blank' ||
        $instance['thumb_link_type'] == 'map_blank') {
      $widget_content .= '</a>';
    }
    $widget_content .= '</p>';

    // if not empty, add footer text
    if (!empty($instance['thumb_footer'])) {
      $tmp = str_ireplace(array('{address}'), array($instance['address']), $instance['thumb_footer']);
      $widget_content .= wpautop(do_shortcode($tmp));
    }

    $out .= apply_filters('gmw_widget_content', $widget_content, $instance);
    $out .= $widget['after_widget'];

    echo $out;
  } // widget


  // compatibility fixes for widgets prior to v3.0
  function upgrade_wiget_instance($instance) {
    $instance = wp_parse_args((array) $instance, self::$defaults);

    if (isset($instance['core_ver']) && version_compare($instance['core_ver'], '3.0',  '>=')) {
      return $instance;
    }

    // pin color is now in hex
    if ($instance['thumb_pin_color'][0] != '#') {
      $instance['thumb_pin_color'] = GMWP::convert_color($instance['thumb_pin_color']);
    } elseif (empty($instance['thumb_pin_color'])) {
      $instance['thumb_pin_color'] = '#ff0000';
    }

    // if we had title before, we need it now too
    if (!empty($instance['lightbox_title']) && !in_array('title', $instance['lightbox_feature'])) {
      $instance['lightbox_feature'][] = 'title';
    }

    // map type values changed
    if (isset($instance['lightbox_map_type']) && $instance['lightbox_map_type'] == 'k') {
      $instance['lightbox_map_type'] = 'satellite';
    } elseif ($instance['lightbox_map_type'] != 'satellite') {
      $instance['lightbox_map_type'] = 'roadmap';
    }

    return $instance;
  } // upgrade_widget_instance
} // class GoogleMapsWidget
endif;
