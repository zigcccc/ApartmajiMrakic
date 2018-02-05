<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function pys_get_woo_product_price_to_display( $product_id ) {
    
    if ( ! $product = wc_get_product( $product_id ) ) {
        return 0;
    }
    
    if ( pys_is_wc_version_gte( '2.7' ) ) {
        
        return wc_get_price_to_display( $product );
        
    } else {
        
        return 'incl' === get_option( 'woocommerce_tax_display_shop' )
            ? $product->get_price_including_tax()
            : $product->get_price_excluding_tax();
        
    }
    
}

if ( ! function_exists( 'pys_get_woo_ajax_addtocart_params' ) ) {
	
	function pys_get_woo_ajax_addtocart_params( $product_id ) {
		
	    $content_id = pys_get_product_content_id( $product_id );
	    
		$params                 = array();
		$params['content_type'] = 'product';
		$params['content_ids']  = json_encode( $content_id );
		
		// currency, value
		if ( pys_get_option( 'woo', 'enable_add_to_cart_value' ) ) {

			$option = pys_get_option( 'woo', 'add_to_cart_value_option' );
			switch ( $option ) {
				case 'global':
					$value = pys_get_option( 'woo', 'add_to_cart_global_value' );
					break;

				case 'price':
					$value = pys_get_product_price( $product_id );
					break;

				default:
					$value = null;
			}

			$params['value']    = $value;
			$params['currency'] = get_woocommerce_currency();

		}
        
        // contents
        $params['contents'] = json_encode( array(
            array(
                'id'         => (string) reset( $content_id ),
                'quantity'   => 1,
                'item_price' => pys_get_woo_product_price_to_display( $product_id ),
            )
        ) );
		
		return $params;
		
	}
	
}

if ( ! function_exists( 'pys_get_post_tags' ) ) {
	
	/**
	 * Return array of product tags.
	 * PRO only.
	 */
	function pys_get_post_tags( $post_id ) {
		
		return array(); // PRO feature
		
	}
	
}

if ( ! function_exists( 'pys_get_woo_code' ) ) {
	
	/**
	 * Build WooCommerce related events code.
	 * Function adds evaluated event params to global array.
	 */
	function pys_get_woo_code() {
		global $post, $posts, $woocommerce;
		
		// set defaults params
		$params                 = array();
		$params['content_type'] = 'product';
		
		// ViewContent Event
		if ( pys_get_option( 'woo', 'on_view_content' ) && is_product() ) {

            $product = wc_get_product( $post->ID );

            $content_id = pys_get_product_content_id( $post->ID );
            $params['content_ids'] = json_encode( $content_id );

            if ( $product->get_type() == 'variable' && pys_get_option( 'woo', 'variation_id' ) != 'main' ) {
                $params['content_type'] = 'product_group';
            } else {
                $params['content_type'] = 'product';
            }

            // @since 5.0.6
            $params['content_type'] = apply_filters( 'pys_fb_pixel_woo_product_content_type', $params['content_type'],
                $product->get_type(), $product, pys_get_option( 'woo', 'content_id_format' ) );

            // currency, value
			if ( pys_get_option( 'woo', 'enable_view_content_value' ) ) {
				
				$option = pys_get_option( 'woo', 'view_content_value_option' );
				switch ( $option ) {
					case 'global':
						$value = pys_get_option( 'woo', 'view_content_global_value' );
						break;
					
					case 'price':
						$value = pys_get_product_price( $post );
						break;
					
					default:
						$value = null;
				}
				
				$params['value']    = $value;
				$params['currency'] = get_woocommerce_currency();
				
			}
            
            // contents
            $params['contents'] = json_encode( array(
                array(
                    'id'         => (string) reset( $content_id ),
                    'quantity'   => 1,
                    'item_price' => pys_get_woo_product_price_to_display( $post->ID ),
                )
            ) );
			
			pys_add_event( 'ViewContent', $params );
			
			return;
			
		}
        
        // ViewCategory
        if ( pys_get_option( 'woo', 'on_view_category', true ) && is_tax( 'product_cat' ) ) {
            
            $term                   = get_term_by( 'slug', get_query_var( 'term' ), 'product_cat' );
            $params['content_name'] = $term->name;
            
            $parent_ids                 = get_ancestors( $term->term_id, 'product_cat', 'taxonomy' );
            $params['content_category'] = array();
            
            foreach ( $parent_ids as $term_id ) {
                $term                         = get_term_by( 'id', $term_id, 'product_cat' );
                $params['content_category'][] = $term->name;
            }
            
            $params['content_category'] = implode( ',', $params['content_category'] );
            
            $content_ids = array();
            $limit       = min( count( $posts ), 5 );
            
            for ( $i = 0; $i < $limit; $i ++ ) {
                $content_ids = array_merge( pys_get_product_content_id( $posts[ $i ]->ID ), $content_ids );
            }
            
            $params['content_ids']  = json_encode( $content_ids );
    
            pys_add_event( 'ViewCategory', $params );
            
            return;
            
        }
        
        // AddToCart Cart Page Event
        if ( pys_get_option( 'woo', 'on_add_to_cart_page' ) && is_cart() ) {
            
            $ids = array();  // cart items ids or sku
            $contents = array();
            
            foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $item ) {
                
                $product_id = pys_get_woo_cart_item_product_id( $item );
                $content_id = pys_get_product_content_id( $product_id );
                $ids        = array_merge( $ids, $content_id );
    
                // raw product id
                $_product_id = empty( $item['variation_id'] ) ? $item['product_id']
                    : $item['variation_id'];
                
                // contents
                $contents[] = array(
                    'id'         => (string) reset( $content_id ),
                    'quantity'   => $item['quantity'],
                    'item_price' => pys_get_woo_product_price_to_display( $_product_id ),
                );
                
            }
            
            $params['content_ids'] = json_encode( $ids );
            $params['contents'] = json_encode( $contents );
            
            // currency, value
            if ( pys_get_option( 'woo', 'enable_add_to_cart_value' ) ) {
                
                $option = pys_get_option( 'woo', 'add_to_cart_value_option' );
                switch ( $option ) {
                    case 'global':
                        $value = pys_get_option( 'woo', 'add_to_cart_global_value' );
                        break;
                    
                    case 'price':
                        $value = pys_get_cart_total();
                        break;
                    
                    default:
                        $value = null;
                }
                
                $params['value']    = $value;
                $params['currency'] = get_woocommerce_currency();
                
            }
            
            pys_add_event( 'AddToCart', $params );
            
            return;
            
        }
        
        // AddToCart on Checkout page
        if ( pys_get_option( 'woo', 'on_add_to_cart_checkout' ) && is_checkout() && ! is_wc_endpoint_url() ) {
            
            $ids = array();  // cart items ids or sku
            $contents = array();
            
            foreach ( $woocommerce->cart->cart_contents as $cart_item_key => $item ) {
                
                $product_id = pys_get_woo_cart_item_product_id( $item );
                $content_id = pys_get_product_content_id( $product_id );
                $ids        = array_merge( $ids, $content_id );
    
                // raw product id
                $_product_id = empty( $item['variation_id'] ) ? $item['product_id']
                    : $item['variation_id'];
                
                // contents
                $contents[] = array(
                    'id'         => (string) reset( $content_id ),
                    'quantity'   => $item['quantity'],
                    'item_price' => pys_get_woo_product_price_to_display( $_product_id ),
                );
                
            }
            
            $params['content_ids'] = json_encode( $ids );
            $params['contents'] = json_encode( $contents );
            
            // currency, value
            if ( pys_get_option( 'woo', 'enable_add_to_cart_value' ) ) {
                
                $option = pys_get_option( 'woo', 'add_to_cart_value_option' );
                switch ( $option ) {
                    case 'global':
                        $value = pys_get_option( 'woo', 'add_to_cart_global_value' );
                        break;
                    
                    case 'price':
                        $value = pys_get_cart_total();
                        break;
                    
                    default:
                        $value = null;
                }
                
                $params['value']    = $value;
                $params['currency'] = get_woocommerce_currency();
                
            }
            
            pys_add_event( 'AddToCart', $params );

        }
		
		// Checkout Page Event
		if ( pys_get_option( 'woo', 'on_checkout_page' ) && is_checkout() && ! is_wc_endpoint_url() ) {
			
			$params = pys_get_woo_checkout_params( false );
			
			// currency, value
			if ( pys_get_option( 'woo', 'enable_checkout_value' ) ) {
				
				$option = pys_get_option( 'woo', 'checkout_value_option' );
				switch ( $option ) {
					case 'global':
						$value = pys_get_option( 'woo', 'checkout_global_value' );
						break;
					
					case 'price':
						$value = pys_get_cart_total();
						break;
					
					default:
						$value = null;
				}
				
				$params['value']    = $value;
				$params['currency'] = get_woocommerce_currency();
				
			}
			
			pys_add_event( 'InitiateCheckout', $params );
			
			return;
			
		}
		
		// Purchase Event
		if ( pys_get_option( 'woo', 'on_thank_you_page' ) && is_wc_endpoint_url( 'order-received' ) ) {
			
			$order_id = wc_get_order_id_by_order_key( $_REQUEST['key'] );
			$order    = new WC_Order( $order_id );
			$items    = $order->get_items( 'line_item' );
			
			$ids = array();     // order items ids or sku
            $contents = array();
			
			foreach ( $items as $item ) {
                
                $product_id = pys_get_woo_cart_item_product_id( $item );
                $content_id = pys_get_product_content_id( $product_id );
                $ids        = array_merge( $ids, $content_id );
                
                // raw product id
                $_product_id = empty( $item['variation_id'] ) ? $item['product_id']
                    : $item['variation_id'];
                
                // contents
                $contents[] = array(
                    'id'         => (string) reset( $content_id ),
                    'quantity'   => $item['quantity'],
                    'item_price' => pys_get_woo_product_price_to_display( $_product_id ),
                );
                
			}
			
			$params['content_ids'] = json_encode( $ids );
            $params['contents'] = json_encode( $contents );
			
			// currency, value
			if ( pys_get_option( 'woo', 'enable_purchase_value' ) ) {
				
				$option = pys_get_option( 'woo', 'purchase_value_option' );
				switch ( $option ) {
					case 'global':
						$value = pys_get_option( 'woo', 'purchase_global_value' );
						break;
					
					case 'total':
						$value = pys_get_order_total( $order );
						break;
					
					default:
						$value = null;
				}
				
				$params['value']    = $value;
				$params['currency'] = get_woocommerce_currency();
				
			}
			
			pys_add_event( 'Purchase', $params );
			
			return;
			
		}
		
	}
	
}

if ( ! function_exists( 'pys_add_code_to_woo_cart_link' ) ) {
	
	/**
	 * Adds data-pixelcode attribute to "add to cart" buttons in the WooCommerce loop.
	 *
	 * @param string     $tag
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	function pys_add_code_to_woo_cart_link( $tag, $product ) {
		global $pys_woo_ajax_events;
		
		// do not add code if AJAX is disabled. event will be processed by another function
		if ( 'yes' !== get_option( 'woocommerce_enable_ajax_add_to_cart' ) ) {
			return $tag;
		}
  
		if ( false == pys_woo_product_is_type( $product, 'simple' ) ) {
			return $tag;
		}
		
		/**
		 * @since 5.0.1
		 */
		if ( pys_is_wc_version_gte( '2.6' ) ) {
			$product_id = $product->get_id();
		} else {
			$product_id = $product->post->ID;
		}
		
		$content_id = pys_get_product_content_id( $product_id );
		
		// common params
		$params                 = array();
		$params['content_type'] = 'product';
		$params['content_ids']  = json_encode( $content_id );
		
		// currency, value
		if ( pys_get_option( 'woo', 'enable_add_to_cart_value' ) ) {
			
			$option = pys_get_option( 'woo', 'view_content_value_option' );
			switch ( $option ) {
				case 'global':
					$value = pys_get_option( 'woo', 'view_content_global_value' );
					break;
				
				case 'price':
					$value = pys_get_product_price( $product_id );
					break;
				
				default:
					$value = null;
				
			}
			
			$params['value']    = $value;
			$params['currency'] = get_woocommerce_currency();
			
		}
        
        // contents
        $params['contents'] = json_encode( array(
            array(
                'id'         => (string) reset( $content_id ),
                'quantity'   => 1,
                'item_price' => pys_get_woo_product_price_to_display( $product_id ),
            )
        ) );
        
        $params = apply_filters( 'pys_event_params', $params, 'AddToCart' );
		
        $event_id = 'pys_' . md5( serialize( $params ) );
		$tag = pys_insert_attribute( 'data-pys-event-id', $event_id, $tag, true, 'any' );
		
		$pys_woo_ajax_events[ $event_id ] = array(
			'name'   => 'AddToCart',
			'params' => $params
		);
		
		return $tag;
		
	}
	
}

if ( ! function_exists( 'pys_get_additional_matching_code' ) ) {
	
	/**
	 * Adds extra params to pixel init code. On Free always returns empty string.
	 * PRO only.
	 *
	 * @see: https://www.facebook.com/help/ipad-app/606443329504150
	 * @see: https://developers.facebook.com/ads/blog/post/2016/05/31/advanced-matching-pixel/
	 * @see: https://github.com/woothemes/woocommerce/blob/master/includes/abstracts/abstract-wc-order.php
	 *
	 * @return string
	 */
	function pys_get_additional_matching_code() {
		
		return ''; // PRO feature
		
	}
	
}

if ( ! function_exists( 'pys_get_additional_woo_params' ) ) {
	
	/**
	 * Adds additional post parameters like `content_name` and `category_name`.
	 * PRO only.
	 *
	 * @param $post   WP_Post|int
	 * @param $params array reference to $params array
	 */
	function pys_get_additional_woo_params( $post, &$params ) {
		
		// PRO only
		
	}
	
}

if ( ! function_exists( 'pys_general_woo_event' ) ) {
	
	/**
	 * Add General event on Woo Product page. PRO only.
	 *
	 * @param $post       WP_Post|int
	 * @param $track_tags bool
	 * @param $delay      int
	 * @param $event_name string
	 */
	function pys_general_woo_event( $post, $track_tags, $delay, $event_name ) {
		// PRO feature		
	}
	
}

if ( ! function_exists( 'pys_general_edd_event' ) ) {
	
	/**
	 * Add General event on EDD Download page. PRO only.
	 *
	 * @param $post       WP_Post|int
	 * @param $track_tags bool
	 * @param $delay      int
	 * @param $event_name string
	 */
	function pys_general_edd_event( $post, $track_tags, $delay, $event_name ) {
		// PRO feature
	}
	
}

if ( ! function_exists( 'pys_get_product_price' ) ) {
	
	/**
	 * Return product price depends on plugin, product and WooCommerce settings.
	 *
	 * @param $product_id
	 *
	 * @return null|int Product price
	 */
	function pys_get_product_price( $product_id ) {
		
		$product = wc_get_product( $product_id );

		if ( false == $product instanceof WC_Product ) {
			return 0;
		}
		
		if ( $product->is_taxable() ) {

			if ( pys_is_wc_version_gte( '3.0' ) ) {
				$value = wc_get_price_including_tax( $product, array( 'price' => $product->get_price() ) );
			} else {
				$value = $product->get_price_including_tax( 1, $product->get_price() );
			}
			
		} else {
   
			if ( pys_is_wc_version_gte( '3.0' ) ) {
				$value = wc_get_price_excluding_tax( $product, array( 'price' => $product->get_price() ) );
			} else {
				$value = $product->get_price_excluding_tax( 1, $product->get_price() );
			}
			
		}
		
		return $value;
		
	}
	
}

if ( ! function_exists( 'pys_get_cart_total' ) ) {
	
	function pys_get_cart_total() {
		global $woocommerce;
		
		return $woocommerce->cart->subtotal;
		
	}
	
}

if ( ! function_exists( 'pys_get_order_total' ) ) {
	
	/**
	 * Calculates order 'value' param depends on WooCommerce and PYS settings
	 */
	function pys_get_order_total( $order ) {
		
		//wc_get_price_thousand_separator is ignored
		return number_format( $total = $order->get_total(), wc_get_price_decimals(), '.', '' );
		
	}
	
}

function pys_pixel_options() {
    global $post;
    
    $options = array(
        'woo' => array(),
    );
    
    if ( pys_get_option( 'woo', 'enabled' ) && pys_is_woocommerce_active() ) {

        $options['woo']['is_shop'] = is_shop();
        $options['woo']['is_cat'] = is_product_category();
        $options['woo']['is_product'] = is_product();
        $options['woo']['add_to_cart_enabled'] = (bool) pys_get_option( 'woo', 'on_add_to_cart_btn' );
    
        if ( is_product() ) {
    
            $product = wc_get_product( $post );
    
            if ( pys_woo_product_is_type( $product, 'simple' ) ) {
                $options['woo']['single_product']['type']               = 'simple';
                $options['woo']['single_product']['add_to_cart_params'] = pys_get_woo_ajax_addtocart_params(
                    $post->ID );
            } elseif ( pys_woo_product_is_type( $product, 'variable' ) ) {
                $options['woo']['single_product']['type']               = 'variable';
                $options['woo']['single_product']['add_to_cart_params'] = pys_woo_product_variations_add_to_cart_params( $product );
            }
            
        }
        
    }

    $options['ajax_url'] = admin_url( 'admin-ajax.php' );
    
    return $options;
    
}

function pys_woo_product_variations_add_to_cart_params( $product ) {

    $params  = array();
    $product = wc_get_product( $product );

    if ( false == $product || false == pys_woo_product_is_type( $product, 'variable' ) ) {
        return array();
    }

    foreach ( $product->get_available_variations() as $variation ) {
        $params[ $variation['variation_id'] ] = pys_get_woo_ajax_addtocart_params( $variation['variation_id'] );
    }


    return $params;

}

function pys_woo_product_is_type( $product, $type ) {
    
    if ( pys_is_wc_version_gte( '2.7' ) ) {
        return $type == $product->is_type( $type );
    } else {
        return $product->product_type == $type;
    }
    
}

// front-end ajax handler
add_action( 'wp_ajax_pys_fb_ajax', 'pys_ajax_handler' );
add_action( 'wp_ajax_nopriv_pys_fb_ajax', 'pys_ajax_handler' );

function pys_ajax_handler() {

    if ( empty( $_GET['sub_action'] ) ) {
        wp_die();
    }

    switch ( $_GET['sub_action'] ) {
        case 'get_woo_product_addtocart_params':
            pys_get_woo_ajax_addtocart_params( absint( $_GET['product_id'] ) );
            break;

        default:
            wp_die( 'Unknown sub action.' );
    }

}
