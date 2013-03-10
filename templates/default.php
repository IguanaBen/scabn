<?php

/* Do not edit this file. (If fact it isn't executed by SCABN)
   It shows an example for how to write a template file. 
   Copy this file to SOMETHING.php in the same
   directory as this file. Make changes to SOMETHING.php to 
   reflect the modifications you want. Then in Wordpress's
   Dashboard, go to SCABN configuration and select the 
   SOMETHING template.
   
   Note: You do not need to use the template system to modify
   SCABN -- it is designed to use wordpress hooks, so  
   wordpress plugins, themes, etc can modify it. So 
   instead of using SCABN's template, you could write your
   own plugin that executes code similar to what is shown below.
   You may need to reference the global $scabn_B instance of the 
   backend class instead of $this in the remove_filter if you go
   this route.
	
	The next section shows some examples of how to replace functions with custom
	version. Here is a list of functions that can be replaced. The first set 
	are defined in classes/display.php if you want to look at a reference. The
	second set is from classes/backend.php
		
	display_item_options
	display_currency_symbol
	displayCustomCart
	displayCartUUID
	display_add_to_cart
	display_widget
	display_cart
	displayCustomCartContents
	display_paypal_receipt	

	add_css
	getItemPricing
	getItemWeight
	getCustomCart		
	shoppingCartInfo															
	getShippingOptions
	scabn_google_shipping_XML
	
	

	Below are some examples of how to replace these functions. Not all possible
	functions are shown, but hopefully enough to get the idea. Simply uncomment from
	remove_filter to the end of the function definition to enable the replacement function
	and edit as needed. Again, a reminder, do not edit default.php, but make a copy and
	edit that file (see above).
*/


/*
   Use this function (add_css) to load a different css file for SCABN. The default css file
   is style.css -- copy that you templates/YOURCSSFILE.css and edit as desired.
   Then enable the code below to load your css instead of the orginal style.css

/*
/*
remove_filter('scabn_add_css',array($this,'add_css'),10,0);
add_filter('scabn_add_css','add_css',10,0);

function add_css() {
   if (file_exists(SCABN_PLUGIN_DIR."/templates/YOURCSSFILE.css")) {
      $csslink = "<link href=\"".SCABN_PLUGIN_URL."/templates/YOURCSSFILE.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
      return $csslink;
   }
}
*/

/* Use this function (shoppingCartInfo) to add text on the checkout page
	between the shopping cart and the buy now buttons. For example, notes about
	shipping options or a warning if you are buying some weird combination (an
	accessory without the main part, etc.
*/
/*
remove_filter('scabn_shoppingCartInfo',array($this,'shoppingCartInfo'),10);
add_filter('scabn_shoppingCartInfo','shippingCartInfo',10,1);
function shippingCartInfo($items) {
	//print "I'm being called!";
   $output="<i>Shipping method will be selected on Paypal or Google Wallet's website.</i> Please proceed with a checkout to see shipping options and pricing. Standard shipping starts at $5. Note: Tracking is only available on USA Priority and all Express shipments. Orders using Standard Shipping and Global Priority cannot be tracked.";
   return $output;
}


/*
	Use this function (getItemWeight) to return the weight of an item.
	Default is to return what the client browser reports
	which *should* be value listed in the items shortcode:
	[scabn name="ItemName" weight="5"]
	If you want to verify the weight (not trust the client
	computer) or have weight depend on quantity, etc edit
	this function (do a db call, etc)

	Units for weight is lbs
*/
/*	
remove_filter('scabn_getItemWeight',array($this,'getItemWeight'),10,3);
add_filter('scabn_getItemWeight','getItemWeight',10,3);
function getItemWeight($itemname,$qty,$inputweight) {	
	//Note: Paypal doesn't like a weight of zero, this makes
	//sure weight it at least 0.01		
	if ($qty > 10){
		$weight=20;		
	} else {
		$weight = $inputweight;				
	}	
	
	if ($weight <= 0.01) {
		$weight = 0.01;
	}		
	return $weight;
	} 
*/

/*
	Currently only used for Google, as Paypal does not support
	dynamic shipping options with different regions. Set Paypal
	shipping options via Paypal account settings. 		
	Function returns an array of the shipping options and pricing.
	In principle this can be a function of them items being shipped
	their weight, etc. 
	The key "regions" is a list of country codes where the shipping is valid (US, CA, UK, etc)	
	The key "notregions" is a list of country codes where the shipping is not valid.
	Leave key regions/notreginos blank when not used

*/
/*
remove_filter('scabn_getShippingOptions',array($this,'getShippingOptions'),10);
add_filter('scabn_getShippingOptions','getShippingOptions',10,1);
function getShippingOptions($items) {
	$ship=array();
	$ship[]=array("name" => "USPS Standard Shipping (Anywhere)", "price" => "5");
	$ship[]=array("name" => "USPS Priority Shipping (USA Only)", "price" => "10", "regions" => array('US'));
	$ship[]=array("name" => "USPS Express Shipping (USA Only)", "price" => "20", "regions" => array('US'));
	$ship[]=array("name" => "Global Priority Int'l (6-10 days)", "price" => "20", "notregions" => array('US'));
	$ship[]=array("name" => "Global Express Int'l (6 days)", "price" => "30", "notregions" => array('US'));
	return $ship;
}
*/


/* Less common, but if you want to change how the the cart in a custom cart looks,
	this is function to edit. Look at displayCustomCartContents in classes/display.php
	for reference.
*/
/*
remove_filter('scabn_displayCustomCartContents',array($this,'displayCustomCartContents'),10);
add_filter('scabn_displayCustomCartContents','displayCustomCartContents',10,1);
function displayCustomCartContents($items) {
	//your code here
}
*/

/*
	This is displayed on the checkout page url
	when an order has been placed via paypal
	and Paypal redirects the shopper back here
	via PDT and auto return to your website.
	See Auto Return under paypal's Website
	Payments section for more details and
	full list of available variables, etc
*/
/*
remove_filter('scabn_display_paypal_receipt',array($this,'display_paypal_receipt'),10);
add_filter('scabn_display_paypal_receipt','display_paypal_receipt',10,1);
function display_paypal_receipt($keyarray) {
		$output="";
		$firstname = $keyarray['first_name'];
		$lastname = $keyarray['last_name'];

		$amount = $keyarray['payment_gross'];

		$output .= "<p><h3>Checkout Complete -- Thank you for your purchase!</h3></p>";
		$output .= "<h4>Payment Details</h4><ul>\n";
		$output .= "<li>Name: $firstname $lastname</li>\n";
		$output .= "<li>Total Amount: $amount</li>\n";
		$output .= "</ul>";
		$output .= "You will receive a confirmation e-mail when payment for the order clears and a second email when your order ships. "; 
		$output .= "You may log into your paypal account at <a href=\"https://www.paypal.com/us\">paypal</a> to view details of this transaction.";
		return $output;
}
*/


/*
	getItemPricing is the function that enforces pricing on
	all items in your cart. If you want to use the pricing that is
	input by the user then just return the $inputprice (default
	behavior of SCABN). This lets you set pricing in the simple
	wordpress syntax of [scabn name="ItemName" price="1.00"]	
 	however, this number is set by the user's computer and
	thus can easily be edited by a hacker to set the price
	to anything. For better security and the ability to
	automatically apply price-breaks, etc, you can have this
	function return the pricing based on some internal criteria
	and ignore the user-supplied price ($inputprice).
   I recommend using a db query where the db stores your pricing. 
	Putting the pricing in the database will have to be done outside SCABN.
	Alternatively, you can just put a lookup table here.
	
*/

/*
remove_filter('scabn_getItemPricing',array($this,'getItemPricing'),10,3);
add_filter('scabn_getItemPricing','getItemPricing',10,3);

function getItemPricing($itemname,$qty,$inputprice) {		
		//Sample db query
		global $wpdb;
		$sql=$wpdb->prepare('SELECT price FROM pricing where name=%s and minimum <= %s order by minimum desc',$itemname,$qty);
		$price = $wpdb->get_var($sql);
		if ( $price == Null ) {
			print 'Error getting price for item '.$itemid.'. Please contact us about this problem.';
			//This will get the buyer to notice the error and complain. Price should not be null from db query
			$price=99999.99;
		}			
		return $price;
	}
*/



/*
	You will need to write your own function here to
	take a uuid input and return a list of items
	for purchase. This mechanism allows a customer to edit
	a custom cart ID and pull up a fixed shopping cart
	for purchase. The mechanism for determining the contents
	of the shopping cart is up to you. I use a db call.
*/

/*
remove_filter('scabn_getCustomCart',array($this,'getCustomCart'),10);
add_filter('scabn_getCustomCart','getCustomCart',10,3);
function getCustomCart($uuid) {	
	//Sample db query to get custom cart:
	global $wpdb;	
	$sql=$wpdb->prepare('SELECT id,name,qty,price, weight FROM customcartitems, customcart where customcart.id = customcartitems.id and customcart.id =%s and customcart.expire > now()',$uuid);	
	$items = $wpdb->get_results($sql);	
	$cartitems=array();	
	foreach ($items as $item) {
		$cartitems[]=array("id"=>$item->id,"name"=>$item->name,"qty"=>$item->qty,"price"=>$item->price,"weight"=>$item->weight);
	}	
	return $cartitems;		
				
}		
*/








?>
