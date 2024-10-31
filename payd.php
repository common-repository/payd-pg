<?php
/*
 * Plugin Name: Payd Gateway for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/PAYD-PG
 * Description: PAYD payment gateway. Take online payments on your store with 3ds security
 * Author: Muhammed Shaheen
 * Author URI: https://hashinclu.de/
 * Version: 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;
require_once("wc_payd_classes/wc_payd_api_connection.php");
require_once("wc_payd_classes/wc_payd_custom_details.php");
require_once("wc_payd_classes/wc_payd_custom_functions.php");

 add_filter( 'woocommerce_payment_gateways', 'payd_add_gateway_class' );
function payd_add_gateway_class( $gateways ) {
  $gateways[] = 'WC_Payd_Gateway'; // your class name is here
  return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */

add_action( 'plugins_loaded', 'payd_init_gateway_class' );


if (!function_exists('payd_init_gateway_class')) 
{
  function payd_init_gateway_class() 
  {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
    class WC_Payd_Gateway extends WC_Payment_Gateway 
    {

      public function __construct() 
      {
   
        $this->id = 'payd'; // payment gateway plugin ID
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'Payd';
        $this->method_description = 'Payd'; 
   
    
        $this->supports = array(
          'products','subscriptions'
        );
   
    
        $this->init_form_fields();
   
    
        $this->init_settings();
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->testmode = 'yes' === $this->get_option( 'testmode' );
        $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
        $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
   
    
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
   
    
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

        
   
      }

     

      public function init_form_fields()
      {
   
          $this->form_fields = array(
          'enabled' => array(
            'title'       => 'Enable/Disable',
            'label'       => 'Enable Payd',
            'type'        => 'checkbox',
            'description' => '',
            'default'     => 'no'
          ),
          'title' => array(
            'title'       => 'Title',
            'type'        => 'text',
            'description' => 'Payd',
            'default'     => 'Payd',
            'desc_tip'    => true
          ),
          'publishable_key' => array(
            'title'       => 'Merchant Key',
            'type'        => 'text'
          ),
          'successpage' => array(
            'title'       => 'Redirect Url',
            'type'        => 'text'
          ),
          'mode' => array(
            'title'       => 'Mode',
            'type'        => 'select',
            'options'     => array(
                      '0' => 'Sandbox',
                      '1'  => 'Live')   
          )
          );
      }

      public function payment_scripts() 
      {
      }

      public function payment_fields() 
      {
      
        echo 'Pay with payd paymentgateway';
      }

      public function process_payment( $order_id ) 
      {
     
        global $woocommerce;
     
        $order = wc_get_order( $order_id )  ;
        $total = $order->get_total();
        $payment_gateway_id = 'payd';
        $payment_gateways   = WC_Payment_Gateways::instance();
        $payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];
        $key  = $payment_gateway->settings['publishable_key'];
        $rurl = $payment_gateway->settings['successpage'];
        $rurl = home_url().'?wc-api=paydpaymentcomplete';
        $wc_payd_custom_details = new wc_payd_custom_details;
        $product = $wc_payd_custom_details->getOrderDetailsByObject($order);
        $params = Array (
                'name'=>$order->billing_first_name,
                'amount'=>$total,
                'remarks'=>$order_id,
                'phone'=>$order->billing_phone,
                'email'=>$order->billing_email,
                'redirecturl'=>$rurl.'&orderid='.$order_id,
                'product'=>json_encode($product)
              );
        $header = array(
          'click2paysecret'=>$key
        );
        $wc_payd_api_connection = new wc_payd_api_connection;
        $response = $wc_payd_api_connection->initiateTransaction($params,$key);
          
       if( !is_wp_error( $response ) ) 
       {
     
         $body = json_decode( $response, true );
        
         if ( $body['data'] != '' ) 
         {
          
            $parts = parse_url($body['data']);
            parse_str($parts['query'], $query);
            $reference =  $query['uniqueid'];
            add_post_meta($order_id, 'referencenumber', $reference, true);
            $woocommerce->cart->empty_cart();
     
          // Redirect to the thank you page
            return array(
              'result' => 'success',
              'redirect' => $body['data']
            );
     
         } 
         else 
         {
            wc_add_notice(  'Please try again.', 'error' );
            return;
         }
     
        } 
        else 
        {
          wc_add_notice(  'Connection error.', 'error' );
          return;
        }
     
      }



    }


  }
}



