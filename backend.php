<?php

class scabn_Backend {

	function __construct() {
		add_action('scabn_getItemPricing',array($this, 'getItemPricing'),10,3);
		
	}	
	
	static function &init() {
		static $instance = false;
		if ( !$instance ) {
				$instance = new scabn_Backend ;
		}
		return $instance;
	}	
	
	
	function initold(){
	}

	function scabn_init(){

		session_start();      // start the session
		$cart =& $_SESSION['wfcart']; // load the cart from the session
		if(!is_object($cart)) $cart = new wfCart(); // if there isn't a cart, create a new (empty) one	
		scabn_Backend::scabn_request();

	

}

	function scabn_request(){
		//This function handles all the client input to change cart via GET / POST requests.
		//Probably a good place to sanitize the data.
	
		$cart =& $_SESSION['wfcart']; // get the cart

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_item'  ){
		

			if ( isset($_REQUEST['item_options']) && (  $_REQUEST['item_options'] != '')  ) {
				//item options set -- check if it is a list of options with ':' as separator			        		
	        	$temp=explode(':',$_REQUEST['item_options']);
	        	//if it is, it should be formatted as 'optionname:price' 
				if ( count($temp) == 2) {
					$price=floatval($temp[1]);
					$itemoptionvalue=sanitize_text_field($temp[0]);
	
				} else {
					$price=floatval($_REQUEST['item_price']);
					$itemoptionvalue=sanitize_text_field($_REQUEST['item_options']);
				}
	
				$item_options = array (sanitize_title($_REQUEST['item_options_name']) => $itemoptionvalue);
				$item_id = sanitize_title($_REQUEST['item_id']."-".$itemoptionvalue);
	
			} else {
				$item_options = array ();
				$price = floatval($_REQUEST['item_price']);
				$item_id = sanitize_title($_REQUEST['item_id']);
			}		
			$cart->add_item($item_id,intval($_REQUEST['item_qty']),$price,sanitize_text_field($_REQUEST['item_name']),$item_options,esc_url($_REQUEST['item_url']),floatval($_REQUEST['item_weight']));		
		}
	
		if (isset ($_REQUEST['remove']) && $_REQUEST['remove'] ){
		   $cart->del_item(sanitize_title($_REQUEST['remove']));
		}
	
		if (isset($_REQUEST['empty']) && $_REQUEST['empty']  ){
		   $cart->empty_cart();
		}
	
		if (isset($_REQUEST['update']) && $_REQUEST['update']  ){				
			for ($i=0; $i<sizeof($cart->items); $i++){
				if (ctype_digit($_POST['qty_'.$i])){												
					$cart->edit_item(sanitize_title($_POST['item_'.$i]),intval($_POST['qty_'.$i]));
			   	}
			}
		}
	
		if (isset($_REQUEST['update_item']) && $_REQUEST['update_item']  ){
		   if (ctype_digit($_REQUEST['qty'])){
		   	$cart->edit_item(sanitize_title($_REQUEST['id']),intval($_REQUEST['qty']));
		   }
		}
	
	
	}




	function getItemPricing($itemname,$qty,$inputprice) {
		//This is the function that sets the pricing
		//for all items in your cart. If you want to use
		//the pricing that is input by the user then just return
		//the $inputprice. This lets you set pricing in the simple
		//wordpress syntax of [scabn name="ItemName" price="1.00"]
		//however, this number is set by the user's computer and
		//thus can easily be edited by a hacker to set the price
		//to anything. For better security and the ability to
		//automatically apply price-breaks, you can have this
		//function return the pricing based entirely on some
		//internal criteria and ignore the user-supplied price ($inputprice)
		//I recommend using a db query where the db stores your pricing. 
		//Putting the pricing in the database will have to be done outside SCABN.
		
		//Sample db query
		//global $wpdb;
		//$sql=$wpdb->prepare('SELECT price FROM pricing where name=%s and minimum <= %s order by minimum desc',$itemname,$qty);
		//$price = $wpdb->get_var($sql);
		//if ( $price == Null ) {
			//print 'Error getting price for item '.$itemid.'. Please contact us about this problem.';
			//This will get the buyer to notice the error and complain. Price should not be null from db query
			//$price=99999.99;
		//return $price;
		
		
		//Not secure but simple
		return $inputprice;

	}
	
	
	
	
	
	function getCurrencies() {
		return array(
						"AUD" => array (36, "Australian Dollar AUD"),
						"CAD" => array (36, "Canadian Dollar CAD"),
						"CZK" => array (75, "Czech Koruna CZK"),
						"DKK" => array (107, "Danish Krone DKK"),
						"EUR" => array (8364, "Euro EUR"),
						"HKD" => array (36, "Hong Kong Dollar HKD"),
						"HUF" => array (70, "Hungarian Forint HUF"),
						"ILS" => array (8362, "Israeli New Sheqel ILS"),
						"JPY" => array (165, "Japanese Yen JPY"),
						"MYR" => array ('82;&#77', "Malaysia Ringgit MYR"),
						"MXN" => array (36, "Mexican Peso MXN"),
						"NOK" => array (107, "Norwegian Krone NOK"),
						"NZD" => array (36, "New Zealand Dollar NZD"),
						"PLN" => array (122, "Polish Zloty PLN"),								
						"GBP" => array (163, "Pound Sterling GBP"),
						"SGD" => array (36, "Singapore Dollar SGD"),
						"SEK" => array (107, "Swedish Krona SEK"),
						"CHF" => array (67, "Swiss Franc CHF"),
						"USD" => array (36, "U.S. Dollar USD")
						);						  
	}
	
	//List of Paypal URLs with Label. Used to generate form for
	//Paypal butnow button.	
	function paypal_urls() {
		return	array('Live'=>'https://www.paypal.com/cgi-bin/webscr','Sandbox'=>'https://www.sandbox.paypal.com/cgi-bin/webscr');
	}

	
}


?>