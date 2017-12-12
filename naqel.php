<?php
/*
Plugin Name: Naqel express
Description: Plugin for integrate with delivery servise NAQEL EXPRESS
Version: 1.0
Author: Averyanau Yauheni
Author URI: https://github.com/eaveryanau
Plugin URI: https://github.com/eaveryanau/naqel
*/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
// Include methods for any entity.
    require_once('app/bootstrap.php');

    add_action('woocommerce_order_status_processing', 'custom_processing');

    new WC_Naqel_Shipping_Method();
}