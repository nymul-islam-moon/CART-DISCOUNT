<?php
/**
 * CART-DISCOUNT
 *
 * @package           CART-DISCOUNT
 * @author            Nymul Islam Moon
 * @copyright         2023 Nymul Islam
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       CART-DISCOUNT
 * Plugin URI:        https://github.com/nymul-islam-moon/CART-DISCOUNT
 * Description:       This plugin will add duplicate cart item from parent item when the parent item have quantity is >= 5 and remove the duplicate item when the parent item have quantity < 5;
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nymul Islam Moon
 * Author URI:        https://github.com/nymul-islam-moon
 * Text Domain:       wp-rest-plugin
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://github.com/nymul-islam-moon/CART-DISCOUNT
 */

/**
 * Copyright (c) 2023 Nymul Islam ( email: towkir1997islam@gmail.com ). All rights reserved.
 *
 * Released under the GPL license
 * htp://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpres.org/
 *
 * *********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope hat it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Ring Road, Mohammadpur, Dhaka, Bangladesh.
 * ********************************************************************
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Add free item to cart when parent item quantity is >= 5
 *
 * @param $cart_updated
 *
 * @return mixed
 *
 * @throws Exception
 */

function action_on_cart_updated( $cart_updated ) {
    $cart = WC()->cart;

    if ( ! $cart->is_empty() ) {
        foreach ( $cart->get_cart() as $item_key => $item ) {
            if ( 5 <= $item['quantity'] && ( ! isset( $item['discount_added'] ) ||  $item['discount_added'] == 'false' ) ) {

                $item_data = [ 'unique_key' => md5(microtime().rand()), 'parent_cart_item_key' => $item_key  ];

                try {
                    /**
                     * Add a counter on parent product to check if the discount is added or not
                     */
                    WC()->cart->cart_contents[$item_key]['discount_added'] = 'true';

                    /**
                     * Add seperated product ( FREE )
                     */
                    $insert = $cart->add_to_cart( $item['product_id'], 1, $item['variation_id'], $item['variation'], $item_data );

                    if ( ! $insert ) {
                        throw new Exception('Failed to add to cart!');
                    }

                } catch (Exception $e) {
                    error_log( 'Get Message ' . $e->getMessage() );
//                    error_log( 'Get File ' . $e->getFile() );
//                    error_log( 'Get Line ' . $e->getLine() );
//                    error_log( 'Get Code ' . $e->getCode() );
//                    error_log( 'Get TraceAsString ' . $e->getTraceAsString() );
//                    error_log( 'Previous ' . $e->getPrevious() );
//                    error_log( 'Get Trace ' . $e->getTrace() );
                }

            }

            /**
             * Remove free cart item if parent cart have less than 5 quantity
             */
            if ( isset( $item['parent_cart_item_key'] ) ) {
                /**
                 * Check parent cart item quantity
                 */
                $cart_item_key = $item['parent_cart_item_key'];

                $cart_item = WC()->cart->get_cart_item($cart_item_key);

                if ( $cart_item['quantity'] < 5 ){

                    WC()->cart->cart_contents[$cart_item_key]['discount_added'] = 'false';

                    $cart->remove_cart_item($item_key);
                }

            }

        }
    }
    return $cart_updated;
}
add_filter('woocommerce_update_cart_action_cart_updated', 'action_on_cart_updated');
add_action('woocommerce_add_to_cart', 'action_on_cart_updated');


/**
 * Set free cart item price 0
 *
 * @param $cart
 *
 * @return void
 */
function action_before_calculate_totals( $cart ) {

    foreach ( $cart->get_cart() as $item_key => $item ) {

        if ( isset( $item['test'] ) && $item['test'] == 456 ) {
            error_log( $item_key );
        }

        if ( isset($item['parent_cart_item_key']) ) {
            $item['data']->set_price(0);
        }
    }
}
// Set cart item price
add_filter('woocommerce_before_calculate_totals', 'action_before_calculate_totals');


/**
 * Display FREE tag to the subtotal and cart item price section
 *
 * @param $price_html
 * @param $cart_item
 *
 * @return mixed|string
 */
function filter_cart_item_displayed_price($price_html, $cart_item){
    if (isset($cart_item['parent_cart_item_key'])) {
        return 'FREE';
    }

    return $price_html;
}

// Display "Free" instead of "0" price
add_action('woocommerce_cart_item_subtotal', 'filter_cart_item_displayed_price', 10, 2);
add_action('woocommerce_cart_item_price', 'filter_cart_item_displayed_price', 10, 2);


/**
 * Remove Delete button from the free product on cart page
 *
 * @param $button_link
 * @param $cart_item_key
 *
 * @return mixed|string
 */
function customized_cart_item_remove_link( $button_link, $cart_item_key ){

    $cart_item = WC()->cart->get_cart()[$cart_item_key];

    if ( isset( $cart_item['parent_cart_item_key'] ) ) {
        $button_link = '';
    }

    return $button_link;
}
add_filter('woocommerce_cart_item_remove_link', 'customized_cart_item_remove_link', 20, 2 );


/**
 * Set discount item quantity to 1
 *
 * @param $product_quantity
 * @param $cart_item_key
 *
 * @return mixed|string
 */
function set_item_quantity($product_quantity, $cart_item_key) {

    // Get the cart object
    $cart_item = WC()->cart->get_cart()[$cart_item_key];

    if ( isset( $cart_item['parent_cart_item_key'] ) ) {
        $product_quantity = '1';
    }

    return $product_quantity;
}
add_filter('woocommerce_cart_item_quantity', 'set_item_quantity', 10, 2);


/**
 * Remove discount item from cart page when the parent item is deleted
 *
 * @param $cart_item_key
 * @param $cart
 *
 * @return void
 */
function remove_discount_item( $cart_item_key, $cart ) {

    foreach ( $cart->get_cart() as $item_key => $item ) {

        if( isset( $item['parent_cart_item_key'] ) && $item['parent_cart_item_key'] == $cart_item_key ) {
            $cart->remove_cart_item( $item_key );
        }
    }

}
add_action('woocommerce_cart_item_removed', 'remove_discount_item', 10, 2);
// a b c d e f g h i j k l m n o p q r s t u v w x y z