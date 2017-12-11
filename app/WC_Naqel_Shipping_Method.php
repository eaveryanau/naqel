<?php
/**
 * Created by PhpStorm.
 * User: eaveryanau
 * Date: 12/11/17
 * Time: 7:46 AM
 */

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
		require_once 'class-shipping.php';
	}

}