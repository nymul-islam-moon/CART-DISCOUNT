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
 * Check
 */
if (!defined('ABSPATH')) {
    exit;
}

class CartDiscountPlugin
{

    /**
     * CartDiscountPlugin constructor.
     */
    public function __construct()
    {
        add_filter('woocommerce_update_cart_action_cart_updated', array($this, 'onCartUpdated'));
        add_action('woocommerce_add_to_cart', array($this, 'onCartUpdated'));
        add_filter('woocommerce_before_calculate_totals', array($this, 'beforeCalculateTotals'));
        add_action('woocommerce_cart_item_subtotal', array($this, 'filterCartItemDisplayedPrice'), 10, 2);
        add_action('woocommerce_cart_item_price', array($this, 'filterCartItemDisplayedPrice'), 10, 2);
        add_filter('woocommerce_cart_item_remove_link', array($this, 'customizedCartItemRemoveLink'), 20, 2);
        add_filter('woocommerce_cart_item_quantity', array($this, 'setItemQuantity'), 10, 2);
        add_action('woocommerce_cart_item_removed', array($this, 'removeDiscountItem'), 10, 2);
    }

    /**
     * @param $cartUpdated
     * @return mixed
     */
    public function onCartUpdated($cartUpdated)
    {
        $cart = WC()->cart;

        if (!$cart->is_empty()) {
            foreach ($cart->get_cart() as $item_key => $item) {
                if (5 <= $item['quantity'] && (!isset($item['discount_added']) || $item['discount_added'] === 'false')) {
                    try {
                        WC()->cart->cart_contents[$item_key]['discount_added'] = 'true';
                        $insert = $cart->add_to_cart(
                            $item['product_id'],
                            1,
                            $item['variation_id'],
                            $item['variation'],
                            ['unique_key' => md5(microtime() . rand()), 'parent_cart_item_key' => $item_key]
                        );

                        if (!$insert) {
                            throw new Exception('Failed to add to cart!');
                        }
                    } catch (Exception $e) {
                        $this->handleException($e);
                    }
                }

                if (isset($item['parent_cart_item_key'])) {
                    $parent_cart_item_key = $item['parent_cart_item_key'];

                    if ($cart->get_cart_item($parent_cart_item_key)['quantity'] < 5) {
                        WC()->cart->cart_contents[$parent_cart_item_key]['discount_added'] = 'false';
                        $cart->remove_cart_item($item_key);
                    }
                }
            }
        }

        return $cartUpdated;
    }

    /**
     * @param $cart
     * @return void
     */
    public function beforeCalculateTotals($cart)
    {
        foreach ($cart->get_cart() as $item_key => $item) {
            if (isset($item['parent_cart_item_key'])) {
                $item['data']->set_price(0);
            }
        }
    }

    /**
     * @param $priceHtml
     * @param $cartItem
     * @return mixed|string
     */
    public function filterCartItemDisplayedPrice($priceHtml, $cartItem)
    {
        if (isset($cartItem['parent_cart_item_key'])) {
            return 'FREE';
        }

        return $priceHtml;
    }

    /**
     * @param $buttonLink
     * @param $cartItemKey
     * @return mixed|string
     */
    public function customizedCartItemRemoveLink($buttonLink, $cartItemKey)
    {
        $cartItem = WC()->cart->get_cart()[$cartItemKey];

        if (isset($cartItem['parent_cart_item_key'])) {
            $buttonLink = '';
        }

        return $buttonLink;
    }

    /**
     * @param $productQuantity
     * @param $cartItemKey
     * @return mixed|string
     */
    public function setItemQuantity($productQuantity, $cartItemKey)
    {
        $cartItem = WC()->cart->get_cart()[$cartItemKey];

        if (isset($cartItem['parent_cart_item_key'])) {
            $productQuantity = '1';
        }

        return $productQuantity;
    }

     /**
     * @param $cartItemKey
     * @param $cart
     * @return void
     */
    public function removeDiscountItem($cartItemKey, $cart)
    {
        foreach ($cart->get_cart() as $itemKey => $item) {
            if (isset($item['parent_cart_item_key']) && $item['parent_cart_item_key'] == $cartItemKey) {
                $cart->remove_cart_item($itemKey);
            }
        }
    }

    /**
     * @param Exception $e
     * @return void
     */
    private function handleException(Exception $e)
    {
        error_log('Exception Message: ' . $e->getMessage());
        // Add more error logging or custom handling if needed
    }
}

new CartDiscountPlugin();