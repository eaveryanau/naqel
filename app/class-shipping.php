<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12/10/17
 * Time: 6:40 PM
 */
class WC_Naqel_Product_Shipping_Method extends WC_Shipping_Method{

    public function __construct(){
        $this->id = 'wc_naqel_shipping_method';
        $this->method_title = __( 'Naqel  Shipping', 'woocommerce' );

        // Load the settings.
        $this->init_form_fields();    // For creating necessary fields for shipping setting page
        $this->init_settings();


        // Define user set variables
        $this->enabled   = $this->get_option( 'enabled' );
        $this->title         = $this->get_option( 'naqel_title' );


        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) ); // Updating Shipping setting fields values

        add_filter( 'woocommerce_package_rates', array($this,'wc_hide_shipping_when_is_available'), 100 );  // this will hide other shipping methods if our shipping method is available
    }


    /**
     * Hide shipping rates when free shipping is available.
     * Updated to support WooCommerce 2.6 Shipping Zones.
     *
     * @param array $rates Array of rates found for the package.
     * @return array
     */
    function wc_hide_shipping_when_is_available( $rates ) {
        $new_product = array();
//        foreach ( $rates as $rate_id => $rate ) {
//            if ( 'wc_naqel_shipping_method' === $rate->method_id ) {
//                $new_product[ $rate_id ] = $rate;
//                break;
//            }
//
//        }
        return ! empty( $new_product ) ? $new_product : $rates;
    }

    public function init_form_fields(){
        $this->form_fields = array(
            'enabled' => array(
                'title'       => __( 'Enable/Disable', 'woocommerce' ),
                'type'            => 'checkbox',
                'label'       => __( 'Enable Naqel Shipping for WooCommerce products', 'woocommerce' ),
                'default'         => 'yes'
            ),
            'naqel_title' => array(
                'title'       => __( 'WooCommerce Naqel Shipping', 'woocommerce' ),
                'type'            => 'text',
                'description'     => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                'default'     => __( 'Naqel Shipping', 'woocommerce' ),

            )
        );
    }


    public function calculate_shipping($package=array()){

        // This is where you'll add your rates

        $this->add_rate( array(
            'id'  => $this->id,
            'label' => $this->title,
            'cost'    => '100'
        ));
        // This will add custom cost to shipping method
    }

}