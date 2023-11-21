<?php

namespace Cart\Discount\Admin;

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
//        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    public function admin_menu() {
        add_menu_page(
            'Cart Discount',
            'Cart Discount',
            'manage_options',
            'cart-discount',
            [$this, 'admin_page'],
            'dashicons-cart',
            20
        );
    }

    public function admin_page() {
        if ( ! get_option('cart_discount_product_qty') ) {
            add_option('cart_discount_product_qty', '3');
        } else {
            update_option('cart_discount_product_qty', '3');
        }
        echo 'Hello World';
    }
}