<?php

/*
Based on Webforce Cart v.1.5
http://www.webforcecart.com/

Stripped down for SCABN's simple purpose
Class stores a list of items and their prices, weights, 
names, and options and a url for the product description.

*/


class wfCart {

	var $items = array();
	var $itemprices = array();
	var $itemqtys = array();
	var $itemweight = array();
	var $itemname = array();

	var $itemoptions = array();
	var $itemurl = array();
	var $randomid = 0;
	var $total = 0;

	function random() {
		return $this->randomid;
		}

	function get_contents()
	{ // gets cart contents
		$items = array();
		foreach($this->items as $tmp_item) {

		   $item = FALSE;

			$item['id'] = $tmp_item;
			$item['name'] = $this->itemname[$tmp_item];
			$item['qty'] = $this->itemqtys[$tmp_item];
			$item['price'] = $this->itemprices[$tmp_item];
			$item['options'] = $this->itemoptions[$tmp_item];
			$item['url'] = $this->itemurl[$tmp_item];
			$item['weight'] = $this->itemweight[$tmp_item];
         $items[] = $item;
		}
		return $items;
	} // end of get_contents


	function add_item($itemid,$qty=1,$price = FALSE, $name = FALSE, $options, $url = FALSE, $weight=0)	{ 
		if( array_key_exists($itemid,$this->itemqtys) && $this->itemqtys[$itemid] > 0)  {
				//Item already in cart, just increment quantity.
		 		$this->itemqtys[$itemid] = $qty + $this->itemqtys[$itemid];
				//use getItemPricing to get the pricing, rather than using value input from user via website
				//Allows pricebreaks based on quantity, etc, to be reflected in the shopping cart.
				$this->itemprices[$itemid]=apply_filters('scabn_getItemPricing',$itemid,$this->itemqtys[$itemid],$price);


		} else {
			//Adding new items to cart.
			$this->items[]=$itemid;
			$this->itemqtys[$itemid] = $qty;
			$this->itemprices[$itemid] = apply_filters('scabn_getItemPricing',$itemid,$this->itemqtys[$itemid],$price);
			$this->itemname[$itemid] = $name;
			$this->itemoptions[$itemid] = $options;
			$this->itemurl[$itemid] = $url;
			if ( $weight == "" ) {
				$this->itemweight[$itemid] = 0;
			} else {
				$this->itemweight[$itemid] = $weight;
			}
		}
		$this->_update_total();
	} // end of add_item


	function edit_item($itemid,$qty)
	{ // changes an item's quantity

		if($qty < 1) {
			$this->del_item($itemid);
		} else {
			$this->itemqtys[$itemid] = $qty;			
			$this->itemprices[$itemid] = apply_filters('scabn_getItemPricing',$itemid,$this->itemqtys[$itemid],$this->itemprices[$itemid]);			
		}
		$this->_update_total();
	} // end of edit_item


	function del_item($itemid)
	{ // removes an item from cart
		$ti = array();
		$this->itemqtys[$itemid] = 0;
		foreach($this->items as $item)
		{
			if($item != $itemid)
			{
				$ti[] = $item;
			}
		}
		$this->items = $ti;
		$this->_update_total();
	} //end of del_item


   function empty_cart()
	{ // empties / resets the cart
		$this->randomid=rand();
		$this->items = array();
		$this->itemprices = array();
		$this->itemqtys = array();
		$this->itemname = array();
		$this->itemurl = array();
		$this->itemweight = array();
		$this->itemoptions = array();
		$this->total = 0;
	} // end of empty cart


	function _update_total()
	{ // internal function to update the total in the cart
		$this->randomid=rand();
		$this->total = 0;
      	if(sizeof($this->items > 0)) {
         	foreach($this->items as $item) {
            	$this->total = $this->total + ($this->itemprices[$item] * $this->itemqtys[$item]);
				}
			}
	} // end of update_total


}
?>
