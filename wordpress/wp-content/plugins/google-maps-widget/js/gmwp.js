/*
 * Google Maps Widget PRO
 * (c) Web factory Ltd, 2012 - 2018
 */


jQuery(function($) {
  if (typeof gmw_data != 'object') {
    return;
  }

  if (gmw_data.colorbox_css) {
    $('head').append('<link id="gmw-colorbox" rel="stylesheet" href="' + gmw_data.colorbox_css + '" type="text/css" media="all">');
  }

  // click map to open lightbox
  $('a.gmw-thumbnail-map.gmw-lightbox-enabled').click(function(e) {
    e.preventDefault();

    widget_id = $(this).data('widget-id');
    widget_data = gmw_data[widget_id];

    // adjust map size if screen is too small
    if (widget_data.lightbox_width !== '100%' && widget_data.lightbox_height !== '100%') {
      screen_width = $(window).width() - 75;
      if (screen_width < widget_data.lightbox_width) {
        widget_data.lightbox_width = screen_width;
        widget_data.lightbox_height *= screen_width / widget_data.lightbox_width;
      }
      screen_height = $(window).height() - 75;
      if (screen_height < widget_data.lightbox_height) {
        widget_data.lightbox_height = screen_height;
        widget_data.lightbox_width *= screen_height / widget_data.lightbox_height;
      }

      widget_data.lightbox_height += 'px';
      widget_data.lightbox_width += 'px';
    } // if !fullscreen

    original_map_title = widget_data.map_title;
    if (!widget_data.show_title) {
      widget_data.map_title = '';
    }

    content = $($($(this).attr('href')).html());
    content.filter('.gmw-map').data('widget-id', widget_id);
    if (!widget_data.multiple_pins) {
      content.filter('.gmw-map').html('<iframe width="100%" height="100%" src="' + widget_data.map_url + '" allowfullscreen></iframe>');
    }

    $.colorbox({ html: content,
                 title: widget_data.map_title,
                 width: widget_data.lightbox_width,
                 height: widget_data.lightbox_height,
                 scrolling: false,
                 preloading: false,
                 arrowKey: false,
                 className: 'gmw-' + widget_data.lightbox_skin,
                 closeButton: widget_data.close_button,
                 overlayClose: widget_data.close_overlay,
                 escKey: widget_data.close_esc });

    // if GA tracking is enabled - track
    if (gmw_data.track_ga === '1') {
      if (typeof _gaq !== 'undefined') {
        _gaq.push(['_trackEvent', 'Open GMW interactive map in lightbox', original_map_title || 'Map']);
      }

      if (typeof ga !== 'undefined') {
        ga('send', 'event', 'Open GMW interactive map in lightbox', original_map_title || 'Map');
      }

	  if (typeof __gaTracker !== 'undefined') {
        __gaTracker('send', 'event', 'Open GMW interactive map in lightbox', original_map_title || 'Map');
      }
    } // if track GA

    return false;
  }); // click map to open lightbox


  // click map to replace img with interactive map
  $('a.gmw-thumbnail-map.gmw-replace-enabled').click(function(e) {
    e.preventDefault();

    widget_id = $(this).data('widget-id');
    widget_data = gmw_data[widget_id];

    // adjust map size if screen is too small
    if (widget_data.lightbox_width !== '100%' && widget_data.lightbox_height !== '100%') {
      screen_width = $(window).width() - 75;
      if (screen_width < widget_data.lightbox_width) {
        widget_data.lightbox_width = screen_width;
        widget_data.lightbox_height *= screen_width / widget_data.lightbox_width;
      }
      screen_height = $(window).height() - 75;
      if (screen_height < widget_data.lightbox_height) {
        widget_data.lightbox_height = screen_height;
        widget_data.lightbox_width *= screen_height / widget_data.lightbox_height;
      }

      widget_data.lightbox_height += 'px';
      widget_data.lightbox_width += 'px';
    } // if !fullscreen

    parent_p = $(this).parent('p');
    parent_p.prev('p').hide();
    parent_p.next('p, span.gmw-powered-by').hide();
    content = $($($(this).attr('href')).html());
    content.filter('.gmw-map').data('widget-id', widget_id);
    if (!widget_data.multiple_pins) {
      content.filter('.gmw-map').html('<iframe width="' + widget_data.lightbox_width + '" height="' + widget_data.lightbox_height + '" src="' + widget_data.map_url + '" allowfullscreen></iframe>');
      parent_p.replaceWith(content);
    } else {
      container = $(this).parents('.google-maps-widget');
      content.filter('.gmw-map').width(widget_data.lightbox_width).height(widget_data.lightbox_height);
      parent_p.replaceWith(content);
      gmw_load_map(widget_id, $(container).find('.gmw-map'));
    } // multiple pins

    // if GA tracking is enabled - track
    if (gmw_data.track_ga === '1') {
      if (typeof _gaq !== 'undefined') {
        _gaq.push(['_trackEvent', 'Replace GMW thumbnail with interactive map', widget_data.map_title]);
      }

      if (typeof ga !== 'undefined') {
        ga('send', 'event', 'Replace GMW thumbnail with interactive map', widget_data.map_title);
      }
    } // if track GA

    return false;
  }); // click map to replace img with interactive map


  // auto replace thumb with interactive map
  $('a.gmw-thumbnail-map.gmw-replace-enabled.gmw-auto-replace').trigger('click');


  // fix lightbox height when header/footer are used
  $(document).bind('cbox_complete', function(e){
    // test if this is a GMW colorbox
    colorbox = e.currentTarget.activeElement;
    if ($('div.gmw-map', colorbox).length === 0) {
      return;
    }

    // get widget/map details
    widget_id = $('div.gmw-map', colorbox).data('widget-id');
    if (!widget_id) {
      return;
    }
    widget_data = gmw_data[widget_id];

    if ($('#cboxTitle', colorbox).html() === '') {
      $('#cboxTitle').hide();
      title_height = 0;
    } else {
      title_height = widget_data.measure_title * parseInt(0 + $('#cboxTitle', colorbox).outerHeight(true), 10);
    }

    if (widget_data.multiple_pins) {
      // adjust map container size
      container = parseInt(0 + $('#cboxLoadedContent').height(), 10);
      header = parseInt(0 + $('#cboxLoadedContent div.gmw-header').outerHeight(true), 10);
      footer = parseInt(0 + $('#cboxLoadedContent div.gmw-header').outerHeight(true), 10);
      $('.gmw-map', colorbox).height((container - header - footer - title_height) + 'px');
      $('.gmw-map', colorbox).width('100%');
      gmw_load_map(widget_id, $('div.gmw-map', colorbox));
    } else {
      // adjust iframe size
      container = parseInt(0 + $('#cboxLoadedContent').height(), 10);
      header = parseInt(0 + $('#cboxLoadedContent div.gmw-header').outerHeight(true), 10);
      footer = parseInt(0 + $('#cboxLoadedContent div.gmw-header').outerHeight(true), 10);
      $('.gmw-map iframe', colorbox).height((container - header - footer - title_height) + 'px');
    }
  }); // cbox_complete


  // filter pins when checkbox changes
  $('.google-maps-widget, #colorbox').on('change', '.gmw-group-wrap input', function(e) {
    group = $(this).data('group-name');
    map = $(this).parents('.gmw-map');

    if ($(this).is(':checked')) {
      visible = true;
    } else {
      visible = false;
    }

    map.gmap3({ get: {
        name: 'marker',
        tag: [group],
        all: true,
        callback: function(objs){
          $.each(objs, function(i, obj){
            obj.setVisible(visible);
          });
        }
    }});
  }); // filter pins click
}); // onload


// load GMWP map
function gmw_load_map(widget_id, map_obj) {
  var $ = jQuery.noConflict();
  var globalCluster;

  map = gmw_data[widget_id];
  if (!map) {
    return;
  }

  // place pins
  tmp_pins = [];
  for(i = 0, len = map.pins.length; i < len; i++) {
    pin = map.pins[i];
    if (pin.show_on == 1) {
      continue;
    }

    pin.tooltip = pin.address;
    if (pin.interactive_pin_img && pin.interactive_pin_img.indexOf('//') == -1) {
      pin.icon = gmw_data.plugin_url + 'images/pins/' + pin.interactive_pin_img;
    } else {
      pin.icon = pin.interactive_pin_img;
    }

    if (pin.latlng) {
      tmp_pins.push({ tag: pin.group.replace(', ', ',').split(','), latLng:[pin.lat, pin.lng], options:{title: pin.tooltip, icon: pin.icon, animation: 2, data: pin.description, click_action: pin.interactive_pin_click, click_action_url: pin.interactive_pin_url }});
    } else {
      tmp_pins.push({ tag: pin.group.replace(', ', ',').split(','), address: pin.address, options:{title: pin.tooltip, icon: pin.icon, animation: 2, data: pin.description, click_action: pin.interactive_pin_click, click_action_url: pin.interactive_pin_url}});
    } // no latlng
  } // for pins

  if (map.lightbox_clustering) {
    map_obj.gmap3({
          marker: {
            values: tmp_pins,
            events:{ click: function(marker, event, context){ gmw_pin_click(marker, event, marker.data, map_obj); } },
            cluster:{
              radius: map.lightbox_clustering,
              0: {
                content: '<div class="gmw-cluster gmw-cluster-1">CLUSTER_COUNT</div>',
                width: 30,
                height: 48,
                anchor: [-18, 0],
                iconAnchor: [15, 48]
              }
            }
          }
    });
    if (map.pins[0].latlng) {
      map_obj.gmap3({ map:{ options:{ center: [ map.pins[0].lat, map.pins[0].lng ] } } });
    }
  } else {
    map_obj.gmap3({
          marker: {
            values: tmp_pins,
            events:{ click: function(marker, event, context){ gmw_pin_click(marker, event, marker.data, map_obj); } },
          }
    });
  }

  // predefined skin
  if (map.lightbox_color_scheme) {
    map_obj.gmap3({ map:{ options: { styles: eval(map.lightbox_color_scheme) } } }) ;
  }

  // autofit map based on pins
  if (map.lightbox_zoom == 'auto') {
    map_obj.gmap3({ autofit:{} });
  } else {
    map_obj.gmap3({ map:{ options:{ zoom: parseInt(map.lightbox_zoom, 10), cache: true } } });
  }

  // set map type
  if (map.lightbox_map_type == 'roadmap') {
    map_obj.gmap3({ map:{ options:{ mapTypeId: google.maps.MapTypeId.ROADMAP } } });
  } else if (map.lightbox_map_type == 'satellite') {
    map_obj.gmap3({ map:{ options:{ mapTypeId: google.maps.MapTypeId.SATELLITE } } });
  } else if (map.lightbox_map_type == 'hybrid') {
    map_obj.gmap3({ map:{ options:{ mapTypeId: google.maps.MapTypeId.HYBRID } } });
  } else if (map.lightbox_map_type == 'terrain') {
    map_obj.gmap3({ map:{ options:{ mapTypeId: google.maps.MapTypeId.TERRAIN } } });
  }

  // disable map features / UI
  map_obj.gmap3({ map:{ options:{ scrollwheel: (typeof map.lightbox_lock.noscroll != 'undefined')? false: true,
                                  zoomControl: (typeof map.lightbox_lock.nozoom != 'undefined')? false: true,
                                  panControl: (typeof map.lightbox_lock.nopan != 'undefined')? false: true,
                                  draggable: (typeof map.lightbox_lock.nodrag != 'undefined')? false: true,
                                  mapTypeControl: (typeof map.lightbox_lock.notype != 'undefined')? false: true,
                                  disableDoubleClickZoom: (typeof map.lightbox_lock.nodoubleclick != 'undefined')? true: false,
                                  keyboardShortcuts: (typeof map.lightbox_lock.nokeyboard != 'undefined')? false: true,
                                  streetViewControl: (typeof map.lightbox_lock.nostreet != 'undefined')? false: true
  } } });

  // set various layers
  if (typeof map.lightbox_layer.traffic != 'undefined') {
    map_obj.gmap3({ trafficlayer:{} });
  }
  if (typeof map.lightbox_layer.transit != 'undefined') {
    transitLayer = new google.maps.TransitLayer();
    transitLayer.setMap(map_obj.gmap3('get'));
  }
  if (typeof map.lightbox_layer.bicycle != 'undefined') {
    map_obj.gmap3({ bicyclinglayer:{} });
  }

  // rebuild on resize
  jQuery(window).resize(function() {
    if (!map_obj.gmap3('get')) {
      return;
    }

    tmp = map_obj.gmap3('get').getCenter();
    map_obj.gmap3({trigger: 'resize'});
    if (tmp !== null) {
      map_obj.gmap3({map: { options: {center: [tmp.k, tmp.A] }}});
    }
  });

  if (map.lightbox_filtering) {
    $(map_obj).append(map.groups_html);
  }

  return map_obj;
} // gmw_load_map


// handle pin click - description show/hide
function gmw_pin_click(marker, event, data, map_obj) {
  var $ = jQuery.noConflict();
  allow_multiple = false;

  if (marker.click_action == '2') {
    document.location.href = marker.click_action_url;
    return;
  } else if (marker.click_action == '3') {
    redirectWindow = window.open(marker.click_action_url, '_blank');
    redirectWindow.location;
    return;
  } else if (marker.click_action == '0') {
    // no action on pin click
    return;
  } else if (marker.click_action == '1' || marker.click_action == '' || typeof marker.click_action == 'undefined') {
    if (!data) {
       return;
    }

    if (allow_multiple) {
      tag_name = 'info-' + encodeURIComponent(marker.title);
    } else {
      tag_name = 'info';
    }

    infowindow = map_obj.gmap3({get:{ name: 'infowindow', tag: tag_name }});
    data = '<div class="gmw_infowindow" style="max-width: 450px;">' + data + '</div>';

    if (infowindow){
      map_obj = $(map_obj).gmap3('get');
      infowindow.open(map_obj, marker);
      infowindow.setContent(data);
    } else {
      map_obj.gmap3({ infowindow:{tag: tag_name, anchor: marker, options:{ content: data }}});
    }

    return true;
  }
} // gmw_pin_click
