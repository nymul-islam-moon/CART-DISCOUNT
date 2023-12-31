<?php
/**
 * CART-DISCOUNT
 *
 * @package           CART-DISCOUNT
 * @author            Nymul Islam Moon
 * @copyright         2023 Nymul Islam Moon
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       CART-DISCOUNT
 * Plugin URI:        https://github.com/nymul-islam-moon/CART-DISCOUNT
 * Description:       This plugin will enhance your WordPress e-commerce experience by automatically adding a complimentary product to the cart when a specified quantity of a particular product is added. For example, you can configure the plugin to offer a free T-shirt for every four T-shirts added to the cart, providing your customers with an enticing incentive to make larger purchases.
 duplicate item when the parent item have quantity < 5;
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

/**
 * Exit if accessed directly.
 *
 * Prevents the plugin file from being accessed directly, ensuring it is only loaded within the context of WordPress.
 * This helps enhance security and ensures proper integration with the WordPress environment.
 */
if (!defined('ABSPATH')) {
    wp_die('Direct access not allowed.', 'Access Denied', array( 'response' => 403 ) );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class CartDiscountPlugin
 *
 * This class is a WooCommerce plugin designed to handle cart updates, item price adjustments, and removal of discount items
 * based on specific conditions. It provides functionality to customize the cart behavior and prices in accordance with
 * defined rules.
 *
 * @package CART-DISCOUNT
 */
final class CartDiscount
{
    private function __construct() {
        add_action( 'plugin_loaded', [ $this, 'init_plugin' ] );
    }

    public function init_plugin() {
        new \Cart\Discount\CartDiscount();
    }

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }
    }
}

function cart_discount() {
    return CartDiscount::init();
}

cart_discount();