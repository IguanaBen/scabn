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



	//Handles all scabn shortcodes
	//Both add to cart items on pages
	// and checkout code.
	//Should separate shortcode stuff from html / display
	//Stuff with display stuff in a function filter.
	function shortcodes($atts) {		
		
		global $post;
		
		$cart = $_SESSION['wfcart'];
	
		extract(shortcode_atts(array(
				'name' => $post->post_title,	           
				'price' => '',
				'fshipping' => '',
				'weight' => '',
				'options_name' => '',
				'options' => '',
				'b_title' => '',
				'qty_field' => '',
				'no_cart' => FALSE
				), $atts));
	
		if (!empty ($atts)){
			//If not empty, then this is an add to cart button we need to generate				

			$id = $post->ID;
			$url =  $post->guid;
	
		   $scabn_options=get_option('scabn_options');		
	      $currency = apply_filters('scabn_display_currency_symbol',$scabn_options['currency']);
	
			if ($no_cart) {				
				$action_url = $scabn_options['cart_url'];
				$add_class = '';
			} else {				
				$action_url = get_permalink();
				$add_class = 'class="add"';
			}
	
			$item_id = sanitize_title($name);
	
			$output = "<div class='addtocart'>\n";
			$output .= "<form method='post' class='".$item_id."' action='".$action_url."'>\n";
			$output .= "<input type='hidden' value='add_item' name='action'/>\n";
			$output .= "<input type='hidden' class='item_url' value='".$url."' name='item_url'/>\n";
			$output .= "<input type='hidden' value='".$item_id."' name='item_id'/>\n";
			$output .= "<input type='hidden' class='item_name' value='".$name."' name='item_name'/>\n";
			$output .= "<input type='hidden' class='item_price' value='".$price."' name='item_price'/>\n";
			$output .= "<input type='hidden' class='item_shipping' value='".$fshipping."' name='item_shipping'/>\n";
			$output .= "<input type='hidden' class='item_weight' value='".$weight."' name='item_weight'/>\n";
				
			$output .= "<p id='cartname'>".$name . "</p>";
			$output .= "<p id='cartcontent'>";
	
	
			if (!empty ($options)){
				$output .= $options_name.": \n";
				$output .= "<input type='hidden' value='".$options_name."' name='item_options_name' class ='item_options_name' />\n";
				$options = explode(',',$options);
	
				$output .= "<select name='item_options' class = 'item_options' >\n";
				foreach ($options as $option){
					$info = explode(':',$option);
					if (count($info) == 1) {
						$output .= "<option value='".$info[0]."'>".$info[0]." (". $currency.number_format($price,2) . ")</option>\n";
					} else {
						$output .= "<option value='".$info[0].":" . $info[1]. "'>".$info[0]." (". $currency.number_format($info[1],2) . ")</option>\n";
					}
				}
				$output .= "</select>\n";
	
			        $output .= "<br/>\n";
	
			} else {
				$output .= "Unit Price: ".$currency.number_format($price,2)." each<br/>";
			}
	
			if($qty_field) {
				$output .= "Qty: <input type='text' class='item_qty' value='1' size='2' name='item_qty'/>\n";
			} else {
				$output .= "<input type='hidden' class='item_qty' value='1' size='2' name='item_qty'/>\n";
			}
	
			if ($no_cart) {
				$output .= "<input type='hidden' value='true' name='no_cart'/>\n";
			}
			$output .= "<input type='submit' id='".$item_id."' ".$add_class." name='add' value='".$b_title."'/>\n";
			$output .= "</form>\n";
			$output .= "</p>\n";
			$output .= "</div>\n";
	
			return $output;
	
		} else {
			//No options, so this is checkout page.
			$tx_token = $_GET['tx'];
	
			if ($tx_token) {
				$cart->empty_cart();
				return scabn_paypal_receipt($tx_token);
			} else {					
				return scabn_process();
			}
	
		}
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