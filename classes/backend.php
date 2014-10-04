<?php

/* This class handles SCABN's backend -- processing
   GET / POST requests, initialization of the web session,
   getting pricing, etc.

   It also contains functions to getting content: lists of
   currencies and their display formats, Paypal URLs, etc.
*/


class scabn_Backend {

	function __construct() {

		if (is_admin()) $this->admin=scabn_Admin::init();
		$this->display=scabn_Display::init();



		add_shortcode('scabn_customcart', array($this,'customcart'));
		add_shortcode('scabn', array($this, 'scabn_Backend::shortcodes'));
		add_action('wp_head', array($this->display, 'scabn_Display::scabn_head'));
		add_filter('scabn_getItemPricing',array($this, 'getItemPricing'),10,3);
		add_filter('scabn_getItemWeight',array($this, 'getItemWeight'),10,3);
		add_filter('scabn_getCustomCart',array($this, 'getCustomCart'),10,1);
		add_filter('scabn_shoppingCartInfo',array($this,'shoppingCartInfo'),10,1);
		add_filter('scabn_getShippingOptions',array($this,'getShippingOptions'),10,1);

		


		$scabn_options = get_option('scabn_options');
		if ( $scabn_options['analytics_id'] != '' ) {
			add_action('wp_head', array($this, 'googleanalytics'));
		}


		//All filters should have been applied by now, so we can now load template
		if (file_exists(SCABN_PLUGIN_DIR. '/templates/'.$scabn_options['template'].'.php') && $scabn_options['template'] != 'default' ) {	
			require_once SCABN_PLUGIN_DIR. '/templates/'.$scabn_options['template'].'.php';
		}

	}


	//I need this and the call to it (scabn_Backend::init() -- I just don't know why
	static function &init() {
		static $instance = false;
		if ( !$instance ) {
			$instance = new scabn_Backend ;
		}
		scabn_Backend::scabn_init();
		return $instance;
	}

	static function scabn_init(){
		session_start();      // start the session
		$cart =& $_SESSION['wfcart']; // load the cart from the session
		if(!is_object($cart)) $cart = new wfCart(); // if there isn't a cart, create a new (empty) one

		scabn_Backend::request();
	}
	
	function getShippingOptions($items){
		$ship=array();
		$ship[]=array("name" => "Standard Shipping", "price" => "5");
		return $ship;	
	}
	

	static function request(){
		//This function handles all the client input to change cart via GET / POST requests.
		//Probably a good place to sanitize the data.
	
		$cart =& $_SESSION['wfcart']; // get the cart
		if ( isset($_REQUEST['randomid']) &&  $_REQUEST['randomid'] == $cart->random() ) 
			{
			//Only update cart, etc, if randomid matches post requiest id. Other it is reload event and we ignore.

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_item'  ){
				require_once ABSPATH . 'wp-includes/pluggable.php'; // It looks like pluggable.php is loaded too late, so I'll do it
				if ( ! wp_verify_nonce($_REQUEST['scabn-add'],'add_to_cart') ) {
					wp_die('Security Check Failed!');
				}

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

				$temparray=array();
				foreach (array("item_qty","item_name","item_url","item_weight") as $label) {
					if (array_key_exists($label,$_REQUEST)) {
						$temparray[$label]=$_REQUEST[$label];
					} else {
						$temparray[$label]="";
					}
				}
				$cart->add_item($item_id,intval($temparray['item_qty']),$price,sanitize_text_field($temparray['item_name']),$item_options,esc_url($temparray['item_url']),floatval($temparray['item_weight']));
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
	
	}


	//Handles all scabn shortcodes
	//Both add to cart items on pages
	// and checkout code.
	function shortcodes($atts) {

		if (!empty ($atts)){
			//If arguments in shortcode, then it is add to cart button
			$output=apply_filters('scabn_display_add_to_cart',$atts);
			return $output;

		} else {
			//No options, so this is checkout page.
			//Check for Paypal token in case this is a receipt page
			if ( array_key_exists('tx',$_GET)) {
				$tx_token = $_GET['tx'];
			}

			if (isset($tx_token)) {
				//Paypal redirected here should be receipt.
				//Empty cart and show receipt
				$cart = $_SESSION['wfcart'];
				$cart->empty_cart();
				require_once SCABN_PLUGIN_DIR. '/classes/paypal.php';
				echo scabn_paypal::receipt($tx_token);
			} else {
				//Normal checkout page.
				echo scabn_Backend::checkout_page();
			}
	
		}
	}


	
	function checkout_page() {
		//  Delay loading Paypal & Google classes as we only need them
		//	 on final checkout page and custom cart page.	
		require_once SCABN_PLUGIN_DIR. '/classes/paypal.php';
		//require_once SCABN_PLUGIN_DIR. '/classes/google.php';
		
		//add_filter('scabn_google_shipping_XML','scabn_google::google_shipping_XML',10,1);

		//main checkout page when shopping (not receipt for transaction)
										
		//display cart
		$output = apply_filters('scabn_display_cart','checkout');
		
		//build array of items for passing to Paypal / Google button generating functions
		$cart = $_SESSION['wfcart'];
		
		if(count($cart->items) > 0) {						
			$options = get_option('scabn_options');	
			
			$holditems=array();
			foreach($cart->get_contents() as $item) {			
				$holditems[]=array("id"=>$item['id'],"name"=>$item['name'],"qty"=>$item['qty'],"price"=>apply_filters('scabn_getItemPricing',$item['id'],$item['qty'],$item['price']),"options"=>$item['options'],"weight"=>apply_filters('scabn_getItemWeight',$item['id'],$item['qty'],$item['weight']));	
			}
			//print_r($holditems);		
			$output .= apply_filters('scabn_shoppingCartInfo',$holditems);
			$output .= scabn_paypal::make_button($holditems);
			//$output .= scabn_google::make_button(apply_filters('scabn_getShippingOptions',$holditems),$holditems);			
	
		}
				
		return $output;
		
	}
	


	function customcart() {
		//  Delay loading Paypal & Google classes as we only need them
		//	 on final checkout page and custom cart page.	
		require_once SCABN_PLUGIN_DIR. '/classes/paypal.php';
		require_once SCABN_PLUGIN_DIR. '/classes/google.php';

		if ( isset($_GET['ccuuid'])) {
			$uuid=$_GET['ccuuid'];
		} else if ( isset($_POST['ccuuid'])) {
			$uuid=$_POST['ccuuid'];
		}
		
		if ( isset($uuid)) {			
			$output=apply_filters('scabn_displayCustomCart',$uuid);
	
		} else {
			$output=apply_filters('scabn_displayCartUUID','');			
			
		}
		return $output;
	}


	function getItemPricing($itemname,$qty,$inputprice) {				
		return $inputprice;
	}
	
	function shoppingCartInfo($items) {		
		return '';
	}

	
	function getCustomCart($uuid) {
		return Null;									
	}
		
		
	
	/* Google Analytics Functions */
	function googleanalytics() {						
		$options=get_option('scabn_options');
		$output="";
		if ( $options['analytics_id'] != '' ) {
	
			$output .= "<script type=\"text/javascript\">
	var _gaq = _gaq || [];
	_gaq.push(['myTracker._setAccount', '" . $options['analytics_id'] . "']);
	_gaq.push(['myTracker._trackPageview']);
	(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
	})();
	</script>";
		
		}
	echo $output;
	}

	
	
	function getItemWeight($itemname,$qty,$inputweight) {
		//Note: Paypal doesn't like a weight of zero, this makes
		//sure weight it at least 0.01				
		if ($inputweight <= 0.01) {
			$inputweight = 0.01;
		}
	return $inputweight;				
	}
	
	
	static function getCurrencies() {
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
						"PLN" => array ('122;&#322', "Polish Zloty PLN"),								
						"GBP" => array (163, "Pound Sterling GBP"),
						"SGD" => array (36, "Singapore Dollar SGD"),
						"ZAR" => array (82, "South African Rand ZAR"),
						"SEK" => array (107, "Swedish Krona SEK"),
						"CHF" => array (67, "Swiss Franc CHF"),
						"USD" => array (36, "U.S. Dollar USD")
						);						  
	}
	
	//List of Paypal URLs with Label. Used to generate form for
	//Paypal butnow button.	
	static function paypal_urls() {
		return	array('Live'=>'https://www.paypal.com/cgi-bin/webscr','Sandbox'=>'https://www.sandbox.paypal.com/cgi-bin/webscr');
	}

	
}


?>
