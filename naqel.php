<?php
/*
Plugin Name: Naqel express
Description: Plugin for integrate with delivery servise NAQEL EXPRESS
Version: 1.0
Author: Averyanau Yauheni
Author URI: https://github.com/eaveryanau
Plugin URI: https://github.com/eaveryanau/naqel
*/

// Include methods for any entity.
require_once('app/bootstrap.php');

add_action('woocommerce_order_status_processing', 'custom_processing');

class WC_Naqel_Shipping_Method
{

    function __construct() {
        add_filter( 'woocommerce_shipping_methods',array($this,'add_wc_naqel_shipping_method'));
        add_action( 'woocommerce_shipping_init',array($this,'wc_naqel_shipping_method_init') );


    }

    function add_wc_naqel_shipping_method( $methods ) {
        $methods[] = 'WC_Naqel_Product_Shipping_Method';
        return $methods;
    }

    function wc_naqel_shipping_method_init(){
        require_once 'app/class-shipping.php';
    }

}

new WC_Naqel_Shipping_Method();
