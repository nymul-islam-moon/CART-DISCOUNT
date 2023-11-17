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

/**
 * Exit if accessed directly.
 *
 * Prevents the plugin file from being accessed directly, ensuring it is only loaded within the context of WordPress.
 * This helps enhance security and ensures proper integration with the WordPress environment.
 */
if (!defined('ABSPATH')) {
    wp_die('Direct access not allowed.', 'Access Denied', array('response' => 403));
}

/**
 * Class CartDiscountPlugin
 *
 * This class is a WooCommerce plugin designed to handle cart updates, item price adjustments, and removal of discount items
 * based on specific conditions. It provides functionality to customize the cart behavior and prices in accordance with
 * defined rules.
 *
 * @package CART-DISCOUNT
 */
class CartDiscountPlugin
{
    /**
     * Product discount quantity
     */
    private $disProdQty;

    /**
     * Constructor for the CartDiscountPlugin class, initializes actions and filters for WooCommerce.
     */
    public function __construct()
    {
        // set product quantity
        $this->disProdQty = 5;
        // Add filter and action hooks for cart updates
        add_filter( 'woocommerce_update_cart_action_cart_updated', array( $this, 'onCartUpdated' ) );
        add_action( 'woocommerce_add_to_cart', array( $this, 'onCartUpdated' ) );

        // Add filter and action hooks for cart item price adjustments
        add_filter( 'woocommerce_before_calculate_totals', array( $this, 'beforeCalculateTotals' ) );
        add_action( 'woocommerce_cart_item_subtotal', array( $this, 'filterCartItemDisplayedPrice' ), 10, 2 );
        add_action( 'woocommerce_cart_item_price', array( $this, 'filterCartItemDisplayedPrice' ), 10, 2 );

        // Add filter and action hooks for cart item removal and quantity adjustments
        add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'customizedCartItemRemoveLink' ), 20, 2 );
        add_filter( 'woocommerce_cart_item_quantity', array( $this, 'setItemQuantity' ), 10, 2 );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'removeDiscountItem' ), 10, 2 );
    }


    /**
     * Handle actions when the cart is updated, such as adding or removing items based on specific conditions.
     *
     * @param mixed $cartUpdated The result of the cart update action.
     *
     * @return mixed The updated result of the cart update action.
     */
    public function onCartUpdated( $cartUpdated )
    {
        // Retrieve the WooCommerce cart object
        $cart = WC()->cart;

        // Check if the cart is not empty
        if ( ! $cart->is_empty() ) {
            // Iterate through each item in the cart
            foreach ( $cart->get_cart() as $item_key => $item ) {
                // Check if the item quantity is greater than or equal to 5 and the discount has not been added
                if ( $this->disProdQty <= $item[ 'quantity' ] && ( ! isset( $item[ 'discount_added' ] ) || $item[ 'discount_added' ] === 'false' )) {
                    try {
                        // Set the discount added flag to true for the current item
                        WC()->cart->cart_contents[ $item_key ][ 'discount_added' ] = 'true';

                        // Add a separated product (FREE) to the cart
                        $insert = $cart->add_to_cart(
                            $item[ 'product_id' ],
                            1,
                            $item[ 'variation_id' ],
                            $item[ 'variation' ],
                            [ 'unique_key' => md5( microtime() . rand()), 'parent_cart_item_key' => $item_key ]
                        );

                        // Throw an exception if the product addition to the cart fails
                        if ( ! $insert ) {
                            throw new Exception( 'Failed to add to cart!' );
                        }
                    } catch ( Exception $e ) {
                        // Handle the exception, logging the error message
                        $this->handleException($e);
                    }
                }

                // Check if the item is associated with a parent cart item
                if ( isset( $item[ 'parent_cart_item_key' ] ) ) {
                    // Retrieve the parent cart item key
                    $parent_cart_item_key = $item[ 'parent_cart_item_key' ];

                    // Check if the quantity of the parent cart item is less than 5
                    if ( $cart->get_cart_item( $parent_cart_item_key )[ 'quantity' ] < 5 ) {
                        // Set the discount added flag to false for the parent cart item
                        WC()->cart->cart_contents[ $parent_cart_item_key ][ 'discount_added' ] = 'false';

                        // Remove the discount item from the cart
                        $cart->remove_cart_item( $item_key );
                    }
                }
            }
        }

        // Return the updated result of the cart update action
        return $cartUpdated;
    }


    /**
     * Perform actions before calculating cart totals, such as adjusting prices for specific cart items.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     *
     * @return void
     */
    public function beforeCalculateTotals( $cart )
    {
        try {
            // Iterate through each item in the cart
            foreach ( $cart->get_cart() as $item_key => $item ) {
                // Check if the cart item is associated with a parent cart item
                if ( isset( $item[ 'parent_cart_item_key' ] ) ) {
                    // Set the price of the cart item to 0 for discount items associated with a parent cart item
                    $item[ 'data' ]->set_price( 0 );
                }
            }
        } catch ( Exception $e ) {
            // Handle the exception, logging the error message
            $this->handleException( $e );
        }
    }



    /**
     * Filter and customize the displayed price HTML for a cart item based on certain conditions.
     *
     * @param mixed|string $priceHtml The HTML string representing the displayed price of the cart item.
     * @param array $cartItem The data of the cart item.
     *
     * @return mixed|string The updated price HTML.
     */
    public function filterCartItemDisplayedPrice( $priceHtml, $cartItem )
    {
        try {
            // Check if the cart item is associated with a parent cart item
            if ( isset( $cartItem[ 'parent_cart_item_key' ] ) ) {
                // Customize the displayed price for discount items associated with a parent cart item
                return 'FREE';
            }

            // Return the original price HTML for non-discount items
            return $priceHtml;
        } catch ( Exception $e ) {
            // Handle the exception, logging the error message
            $this->handleException( $e );
        }
    }



    /**
     * Customize the remove item link for a cart item based on certain conditions.
     *
     * @param mixed|string $buttonLink The HTML link for removing the cart item.
     * @param string $cartItemKey The key of the cart item.
     *
     * @return mixed|string The updated remove item link HTML.
     */
    public function customizedCartItemRemoveLink( $buttonLink, $cartItemKey )
    {
        try {
            // Retrieve the cart item based on the provided cart item key
            $cartItem = WC()->cart->get_cart()[ $cartItemKey ];

            // Check if the cart item is associated with a parent cart item
            if ( isset( $cartItem[ 'parent_cart_item_key' ] ) ) {
                // Customize the remove item link for discount items associated with a parent cart item
                $buttonLink = '';
            }

            // Return the updated remove item link HTML
            return $buttonLink;
        } catch (Exception $e) {
            // Handle the exception, logging the error message
            $this->handleException( $e );
        }
    }


    /**
     * Set the quantity of the specified cart item based on certain conditions.
     *
     * @param mixed $productQuantity The quantity of the cart item.
     * @param string $cartItemKey The key of the cart item.
     *
     * @return mixed The updated quantity of the cart item.
     */
    public function setItemQuantity( $productQuantity, $cartItemKey )
    {
        // Retrieve the cart item based on the provided cart item key
        $cartItem = WC()->cart->get_cart()[ $cartItemKey ];

        // Check if the cart item is associated with a parent cart item
        if ( isset( $cartItem[ 'parent_cart_item_key' ] ) ) {
            // Set the quantity to '1' for discount items associated with a parent cart item
            $productQuantity = '1';
        }

        // Return the updated quantity of the cart item
        return $productQuantity;
    }


    /**
     * Remove the discount item associated with the specified cart item key when the parent item is deleted.
     *
     * @param string $cartItemKey The key of the cart item being removed.
     * @param WC_Cart $cart The WooCommerce cart object.
     *
     * @return void
     */
    public function removeDiscountItem( $cartItemKey, $cart )
    {
        foreach ( $cart->get_cart() as $itemKey => $item ) {
            // Check if the current item is a discount item associated with the specified parent cart item key
            if ( isset( $item[ 'parent_cart_item_key' ] ) && $item[ 'parent_cart_item_key' ] == $cartItemKey ) {
                // Remove the discount item from the cart
                $cart->remove_cart_item( $itemKey );
            }
        }
    }


    /**
     * Handle an exception by logging the error message to the system error log.
     *
     * @param Exception $e The exception to be handled.
     *
     * @return void
     */
    private function handleException( Exception $e ) {
        try {
            // Log the exception message to the system error log
            error_log( 'Exception Message: ' . $e->getMessage() );

            // Additional error logging or custom handling can be added here if needed
            // For example, you might want to send an email notification to the admin

            // Send an email to the admin with the exception details
            $admin_email = get_option( 'admin_email' );
            $subject = 'Exception occurred on your website';
            $message = 'An exception occurred on your website. Details: ' . PHP_EOL . PHP_EOL;
            $message .= 'Exception Message: ' . $e->getMessage() . PHP_EOL;
            $message .= 'Exception Code: ' . $e->getCode() . PHP_EOL;
            $message .= 'File: ' . $e->getFile() . PHP_EOL;
            $message .= 'Line: ' . $e->getLine() . PHP_EOL;

            wp_mail( $admin_email, $subject, $message );

        } catch ( Exception $e ) {
            // If there is an error handling the exception, log it to the system error log
            error_log( 'Error handling exception: ' . $e->getMessage() );
            // You may want to log to a specific error log or take additional actions as needed
        }
    }

}

new CartDiscountPlugin();