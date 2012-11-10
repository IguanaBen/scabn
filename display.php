<?php

/* This class handles SCABN's Display -- 
	Generating HTML, etc. 
	
	All functions here are called via actions / filters
	so they can easily be replaced / hijacked for customization.
*/



class scabn_Display {

	function __construct() {
		add_action('scabn_display_item_options',array($this, 'display_item_options'),10,1);
		add_action('scabn_display_currency_symbol',array($this, 'display_currency_symbol'),10,1);
		add_action('scabn_add_css',array($this, 'add_css'),10,0);
		add_action('scabn_displayCustomCart',array($this,'displayCustomCart'),10,1);
		add_action('scabn_displayCartUUID', array($this,'enter_cart_uuid'),10,0);
	}	
	
	//Again, not sure why I need this, but I do
	static function &init() {
				
		static $instance = false;
		if ( !$instance ) {
				$instance = new scabn_Display ;
		}
		return $instance;
	}		
	
	
	//How should item options be displayed in the cart
	//Do we display the option name (eg color)? Or just the value (eg Red)?
	//Shirt
	//Color: Red
	
	//or
	
	//Shirt
	//Red
	function display_item_options ($options_arr){				
		foreach($options_arr as $key=>$value){
	   	$options_pair = $value;
		}
		return $options_pair;
	}

	
	


	function enter_cart_uuid(){
		$output="<BR>Please enter the custom cart id here:
			<form name=\"input\" action=\"custom-cart\" method=\"GET\">
			Custom Cart ID: <input type=\"text\" name=\"ccuuid\" /><p>
			<input type=\"submit\" value=\"Submit\" /></p>
			</form>";
		return $output;
	}

	
	function displayCustomCart($uuid) {				
		//This is a function that takes as custom cart uuid number
		//and generates a custom cart. We do a db query to get
		//the item(s) and pricing, etc, and then call paypal / google functions
		//to make a buy now buttons.
		$options = get_option('scabn_options');		
		$output = "";
		$items=apply_filters('scabn_getCustomCart',$uuid);
		if ($items) {
	
			$output .= displayCustomCartContents($items);
			$output .= scabn_make_paypal_button($options,$items);
			$output .= scabn_make_google_button($options,$items);
		} else {
			$output .= '<h4>Could not find your custom cart, or the cart has expired</h4>';
			$output .= apply_filters('scabn_displayCartUUID','');
		}
		return $output;
	}








	function display_currency_symbol($code){	
  		$scabn_currency_codes= scabn_Backend::getCurrencies();
		$d = $scabn_currency_codes[$code][0];
		$symbol = "&#".$d.";";
		return $symbol;
	}



	/**	
 	* Inserting files on the header
 	*/
	function scabn_head() {
		$scabn_header =  "\n<!-- Simple Cart and Buy Now -->\n";	
		$scabn_header .= apply_filters('scabn_add_css','');
		$scabn_header .=  "\n<!-- Simple Cart and Buy Now End-->\n";
		echo $scabn_header;
}


	function add_css() {
		if (file_exists(SCABN_PLUGIN_DIR."/style.css")) {
			$csslink = "<link href=\"".SCABN_PLUGIN_URL."/style.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
			return $csslink;	
		}
		
		
	}



}




?>