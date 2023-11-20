<?php

namespace Cart\Discount;
use \Cart\Discount\Frontend\Frontend;
use \Cart\Discount\Admin\Admin;

class CartDiscount
{
    public function __construct()
    {
        new Frontend();
        new Admin();
    }
}