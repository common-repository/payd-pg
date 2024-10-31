<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class wc_payd_custom_details
{
	public $result;
	public $res;
	protected $product;
	protected $product_name;
	protected $regular_price;
	protected $item_quantity;
	protected $item_total;
	protected $order;
	protected $item_id;
	protected $item;
	function __construct()
	{
		$this->result = array();
		$this->res 	  = array();
	}

	public function getOrderDetailsByObject($order)
	{
		$this->order = $order;
		foreach ($this->order->get_items() as $this->item_id => $this->item ) 
		{
    
			$this->product        	 = 	$this->item->get_product();
    		$this->product_name   	 = 	$this->item->get_name(); // Get the item name (product name)
			$this->regular_price  	 = 	$this->product->get_price();
    		$this->item_quantity  	 = 	$this->item->get_quantity(); // Get the item quantity
    		$this->item_total     	 = 	$this->item->get_total(); // Get the item line total discounted
			$this->result['title'] 	 = 	$this->product_name;
			$this->result['details'] = 	$this->product_name.'(quantity-'.$this->item_quantity.')';
			$this->result['amount']  = 	$this->item_total;
			$this->result['vat'] 	 = 	0;
			$this->result['total'] 	 = 	$this->item_total;
			$this->res[] = $this->result;
    
		}
		return $this->res;
	}
}