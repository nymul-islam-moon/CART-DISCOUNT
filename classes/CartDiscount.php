<?php

namespace Cart\Discount;
use \Cart\Discount\Frontend\Frontend;
use \Cart\Discount\Admin\Admin;
use \Cart\Discount\Version\Version;

class CartDiscount
{
    public function __construct()
    {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init()
    {
        new Version();
        new Frontend();
        new Admin();
    }
}