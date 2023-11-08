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
 * Plugin URI:        https://github.com/nymul-islam-moon/WOO-Sing-Prod-hook
 * Description:       This plugin will add duplicate cart item from parent item when the parent item have quantity is >= 5 and remove the duplicate item when the parent item have quantity < 5;
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nymul Islam Moon
 * Author URI:        https://github.com/nymul-islam-moon
 * Text Domain:       wp-rest-plugin
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://github.com/nymul-islam-moon/CART-DISCOUNT-PLUGIN
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
 * CART-DISCOUNT
 *
 * @return $cart_updated
 */
add_filter('woocommerce_update_cart_action_cart_updated', 'action_on_cart_updated');
function action_on_cart_updated( $cart_updated ) {
    $cart = WC()->cart;

    if ( ! $cart->is_empty() ) {
        foreach ( $cart->get_cart() as $item_key => $item ) {
            if ( $item && 5 <= $item['quantity'] ) {

                $item_data = ['unique_key' => md5(microtime().rand()), 'free_item' => 'yes', 'parent_cart_item' => $item['product_id']];
                // Add a separated product (free )

//                if ( isset( $item['parent_cart_item'] ) ) {
//
//                }

                $cart->add_to_cart($item['product_id'], 1, $item['variation_id'], $item['variation'], $item_data);
            }




            if ( isset( $item['parent_cart_item'] ) ) {
                // check parent cart item quantity
                $parent_id = $item['parent_cart_item'];
                $parent_cart_item = WC()->cart->get_cart()[$parent_id];

                if ( $parent_cart_item['quantity'] < 5 ){
                    $cart->remove_cart_item($item_key);
                }

            }

        }
    }
    return $cart_updated;
}

// Set cart item price
add_filter('woocommerce_before_calculate_totals', 'action_before_calculate_totals');
function action_before_calculate_totals( $cart ) {
    if ((is_admin() && !defined('DOING_AJAX')))
        return;

    foreach ( $cart->get_cart() as $item_key => $item ) {
        if ( isset($item['free_item']) ) {
            $item['data']->set_price(0);
        }
    }
}

// Display "Free" instead of "0" price
add_action('woocommerce_cart_item_subtotal', 'filter_cart_item_displayed_price', 10, 2);
add_action('woocommerce_cart_item_price', 'filter_cart_item_displayed_price', 10, 2);
function filter_cart_item_displayed_price($price_html, $cart_item){
    if (isset($cart_item['free_item'])) {
                return 'FREE';
    }
    return $price_html;
}


/**
 * Remove delete button from duplicate cart item
 */

add_filter('woocommerce_cart_item_remove_link', 'customized_cart_item_remove_link', 20, 2 );
function customized_cart_item_remove_link( $button_link, $cart_item_key ){

    $cart_item = WC()->cart->get_cart()[$cart_item_key];

    if ( isset( $cart_item['free_item'] ) ) {
        $button_link = '';
    }

    return $button_link;
}


/**
 * Hide duplicated item quantity handler
 */

add_filter('woocommerce_cart_item_quantity', 'hide_quantity_handler', 10, 2);

function hide_quantity_handler($product_quantity, $cart_item_key) {

    // Get the cart object
    $cart_item = WC()->cart->get_cart()[$cart_item_key];

    if ( isset( $cart_item['free_item'] ) ) {
        $product_quantity = '1';
    }

    return $product_quantity;
}