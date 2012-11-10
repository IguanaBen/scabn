<?php
global $wpdb;
$wpdb->show_errors();
$options = get_option('scabn_options');
$cart = $_SESSION['wfcart'];

$holditems=array();
foreach($cart->get_contents() as $item) {
	$weight = 2.4; //Should get this from db query in future.
	$holditems[]=array("id"=>$item['id'],"name"=>$item['name'],"qty"=>$item['qty'],"price"=>apply_filters(scabn_getItemPricing,$item['id'],$item['qty'],$item['price']),"options"=>$item['options'],"weight"=>apply_filters(scabn_getItemWeight,$item['id'],$item['qty'],$item['weight']));	
}

$output .= ShopingCartInfo($holditems);
$output .= scabn_make_paypal_button($options,$holditems);
$output .= scabn_make_google_button($options,getShippingOptions($holditems),$holditems);

 ?>
