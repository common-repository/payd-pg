<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class wc_payd_api_connection
{
	public $secretkey;
	public $params;
	public $apiurl;
  public $reference;
  public $mode;
	public function initiateTransaction($params,$key)
	{
		$this->secretkey = $key;
		$this->apiurl = "https://www.payd.ae/pg/public/api/generateTransactionId";
		$this->params = $params;

          $args = array(
            'body'        => $this->params,
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('secretkey'=>$this->secretkey),
            'cookies'     => array(),);
          $response       = wp_remote_post( $this->apiurl, $args );
          return $response['body'];
	}

     public function verifyTransactionStatus($key,$reference,$mode)
     {
          $this->secretkey = $key;
          $this->reference = $reference;
          $this->mode      = $mode;
          $this->apiurl = "https://www.payd.ae/pg/public/api/paymentdetails/".$this->reference."/".$this->mode;
          $args = array(
            'body'        => null,
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array('secretkey'=>$this->secretkey),
            'cookies'     => array(),);
          $response       = wp_remote_get( $this->apiurl, $args );
          return $response['body'];
     }
}