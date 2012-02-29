<?php
global $wpdb;
$wpdb->show_errors();
$options = $scabn_options;





$cart = $_SESSION['wfcart'];

$holditems=array();
foreach($cart->get_contents() as $item) {
	$weight = 2.4; //Should get this from db query in future.
	$holditems[]=array("id"=>$item['id'],"name"=>$item['name'],"qty"=>$item['qty'],"price"=>getItemPricing($item['id'],$item['qty'],$item['price']),"options"=>$item['options'],"weight"=>getItemWeight($item['id'],$item['qty'],$item['weight']));	
	}

echo ShopingCartInfo($holditems);
echo scabn_make_paypal_button($options,$holditems);
echo scabn_make_google_button($options,getShippingOptions($holditems),$holditems);

 ?>
