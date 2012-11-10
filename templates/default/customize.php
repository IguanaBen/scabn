<?php

function ShopingCartInfo($items) {
	//Use this function to return a message at the bottom of the checkout page.
	//Maybe a note about shipping options. Maybe a warning that you bought
	//a combination of items that doesn't make sense.
	return "";;
}



function getItemPricingold($itemname,$qty,$inputprice) {
	//This is the function that sets the pricing
	//for all items in your cart. If you want to use
	//the pricing that is input by the user then just return
	//the $inputprice. This lets you set pricing in the simple
	//wordpress syntax of [scabn name="ItemName" price="1.00"]
	//however, this number is set by the user's computer and
	//thus can easily be edited by a hacker to set the price
	//to anything. For better security, and the ability to
	//automatically apply price-breaks, you can have this
	//function return the pricing based entirely on some
	//internal criteria and ignore the user-supplied price ($inputprice)

	//Not secure but simple
	//return $inputprice

	//Sample db query
	//global $wpdb;
	//$sql=$wpdb->prepare('SELECT price FROM pricing where name=%s and minimum <= %s order by minimum desc',$itemname,$qty);
	//$price = $wpdb->get_var($sql);
	//if ( $price == Null ) {
		//print 'Error getting price for item '.$itemid.'. Please contact us about this problem.';
		//This will get the buyer to notice the error and complain. Price should not be null from db query
		//$price=99999.99;

	//default to just use input for user
	$price=$inputprice;
	return $price;

	}




function getItemWeight($itemname,$qty,$inputweight) {
	//This is the function that sets the weight
	//for all items in your cart. If you want to use
	//the weight that is input by the user then just return
	//the $inputweight. This lets you set pricing in the simple
	//wordpress syntax of [scabn name="ItemName" weight="1.00"]

	//Note: Paypal doesn't like a weight of zero, this makes
	//sure weight it at least 0.01
	if ($inputweight <= 0.01) {
		$inputweight = 0.01;
	}
	return $inputweight;


	} 




function getShippingOptions($items) {
	//Currently only used for Google, as Paypal needs this hard-coded into
	//their website, this gives a list of the shipping options and pricing.
	//in principle this can be a function of them items being shipped
	//their weight, etc. 
	//We could put option on Paypal button for shipping, but shipping options
	//depend on shipping location which we don't ask for. So put your shipping
	//formula into paypal website. 
	//region can be "all" "NotUSA" or "USA"
	$ship=array();
	$ship[]=array("name" => "USPS Standard Shipping", "price" => "5", "region" => "all");
	$ship[]=array("name" => "USPS Priority Shipping (USA Only)", "price" => "10", "region" => "USA");
	$ship[]=array("name" => "USPS Express Shipping (USA Only)", "price" => "20", "region" => "USA");
	$ship[]=array("name" => "Global Priority (6-10 days)", "price" => "20", "region" => "NotUSA");
	$ship[]=array("name" => "Global Express (6 days)", "price" => "30", "region" => "NotUSA" );
	return $ship;
}


function displayCustomCartContents($items) {
	$output="";		
	if ($items) {			
		$output .="<table border='0' cellpadding='5' cellspacing='1' class='entryTable' align='center' width='96%'>	
		<thead>
		<tr class=\"thead\">
			<th scope=\"col\">Qty</th>
			<th scope=\"col\">Items</th>
			<th scope=\"col\" align=\"right\">Unit Price</th>
		</tr>
		</thead>";	
		$options = get_option('scabn_options');		
		$currency = apply_filters('scabn_display_currency_symbol',$options['currency']);						
		foreach($items as $item) {

			$output .= "<tr class = \"ck_content\">
				<td>" . $item['qty'] . "</td>            
				<td>" . $item['name'] ."</td>
				<td align='right'>" . $currency . number_format($item['price'],2) . "</td>
			</tr>";
			 
		}
		$output .= "</table>";		
	}
	return $output;
}


	
function display_paypal_receipt($keyarray) {
		//This is displayed on the checkout page url
		//When an order has been placed via paypal
		//and after the order is placed Paypal redirects
		//the shopper back here via PDT and auto return.
		//See Auto Return under paypal's Website
		//Payments section for more details.
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



?>
