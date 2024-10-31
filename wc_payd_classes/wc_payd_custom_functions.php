<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class wc_payd_custom_functions
{
	function __construct() {
          add_action( 'woocommerce_admin_order_data_after_billing_address',[ $this, 'payd_display_order_meta' ]);
          add_action( 'woocommerce_api_paydpaymentcomplete', [ $this, 'payd_webhook' ]);
     }

     
	function payd_display_order_meta($order){
		  
		  $referencenumber = get_post_meta($order->id,'referencenumber',true);
		    if($referencenumber)
		    {
		    echo '<p><strong>'.__('Transaction id').':</strong> <br/>'.$referencenumber.'</p>';
		    }
	}
	

	
	  function payd_webhook() 
	  {
	      $order_id   = sanitize_text_field($_GET['orderid']);
	      $reference  = sanitize_text_field($_GET['reference']);
	      $order      = wc_get_order( $order_id);
	      $payment_gateway_id = 'payd';
	      $payment_gateways   = WC_Payment_Gateways::instance();
	      $payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];
	      $key  = $payment_gateway->settings['publishable_key'];
	      $rurl = $payment_gateway->settings['successpage'];
	    //$mode = $payment_gateway->settings['mode'];
	      $mode = 2;
	      $wc_payd_api_connection = new wc_payd_api_connection;
	      $response = $wc_payd_api_connection->verifyTransactionStatus($key,$reference,$mode);
	      $responsearray = json_decode($response,true); 
	      $statusid = $responsearray['status'];
	      $statusmessage = $responsearray['data']['payment_status'];
	      if($statusid==1 && $statusmessage=="completed")
	      {
	              if (!empty($order)) {
	            $order->update_status( 'completed' );
	          $note="Transaction id $reference";
	          $order->add_order_note( $note );
	          
	        }
	      }
	      wp_redirect( esc_url($rurl)."?orderid=$order_id&reference=$reference&status=$statusid&message=$statusmessage");
	  }
	
}

$wc_payd_custom_functions = new wc_payd_custom_functions;