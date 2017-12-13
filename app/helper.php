<?php
/**
 * Created by PhpStorm.
 * User: eaveryanau
 * Date: 12/9/17
 * Time: 1:22 PM
 */

function waybills_list(){
    $out = "
    <h1>Waybills list</h1>
    ";
    return $out;
}

function custom_processing($order_id){
    if(WC() && WC()->session){
        $chosen_methods = WC()->session->get('chosen_shipping_methods');
    }
 if (get_option('woocommerce_wc_naqel_shipping_method_settings')['enabled'] === 'yes'
     && $chosen_methods
     && is_array($chosen_methods)
     && in_array('wc_naqel_shipping_method', $chosen_methods)){
        Naqel::createWaybill($order_id);
    }
}