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
}