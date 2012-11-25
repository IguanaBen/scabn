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
		add_action('scabn_display_add_to_cart', array($this,'display_add_to_cart'),10,1);
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
		foreach($options_arr as $key=>$value) {				   
			if (isset($options_pair)) {	   	
		   	$options_pair .= "<BR/>".$value;
			} else {
	   		$options_pair = $value;
	   	}
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



	function display_add_to_cart($item) {
		//Displays the 'add to cart' button on pages. Contains
		//both the visual data and the form submission for adding
		//items to the cart.		
		
		global $post;		
		$item_id=sanitize_title($item['name']);		
		$scabn_options=get_option('scabn_options');		
	   $currency = apply_filters('scabn_display_currency_symbol',$scabn_options['currency']);
	   
		if ($item['no_cart']) {				
			$action_url = $scabn_options['cart_url'];
			$add_class = '';
		} else {				
			$action_url = get_permalink();
			$add_class = 'class="add"';
		}		
				
		$output = "<div class='addtocart'>\n";
		$output .= "<form method='post' class='".$item_id."' action='".$action_url."'>\n";
		$output .= "<input type='hidden' value='add_item' name='action'/>\n";
		$output .= "<input type='hidden' class='item_url' value='".$post->guid."' name='item_url'/>\n";
		$output .= "<input type='hidden' value='".$item_id."' name='item_id'/>\n";
		$output .= "<input type='hidden' class='item_name' value='".$item['name']."' name='item_name'/>\n";
		$output .= "<input type='hidden' class='item_price' value='".$item['price']."' name='item_price'/>\n";
		$output .= "<input type='hidden' class='item_shipping' value='".$item['fshipping']."' name='item_shipping'/>\n";
		$output .= "<input type='hidden' class='item_weight' value='".$item['weight']."' name='item_weight'/>\n";
			
		$output .= "<p id='cartname'>".$item['name'] . "</p>";
		$output .= "<p id='cartcontent'>";
		
		if (!empty ($item['options'])){
 			if ( $item['options_name'] != "" ) { 
 	  	   	$output .= $item['options_name'].": \n"; 
		 	} 		
									
			$output .= "<input type='hidden' value='".$item['options_name']."' name='item_options_name' class ='item_options_name' />\n";
			$options = explode(',',$item['options']);			
			$output .= "<select name='item_options' class = 'item_options' >\n";
			foreach ($options as $option){
				$info = explode(':',$option);				
				if (count($info) == 1) {
					$output .= "<option value='".$info[0]."'>".$info[0]." (". $currency.number_format($item['price'],2) . ")</option>\n";
				} else {
					$output .= "<option value='".$info[0].":" . $info[1]. "'>".$info[0]." (". $currency.number_format($info[1],2) . ")</option>\n";
				}
			}
			$output .= "</select>\n";
			$output .= "<br/>\n";

		} else {
			$output .= "Unit Price: ".$currency.number_format($item['price'],2)." each<br/>";
		}

		if($item['qty_field']) {
			$output .= "Qty: <input type='text' class='item_qty' value='1' size='2' name='item_qty'/>\n";
		} else {
			$output .= "<input type='hidden' class='item_qty' value='1' size='2' name='item_qty'/>\n";
		}		
	
		if ($item['no_cart']) {
			$output .= "<input type='hidden' value='true' name='no_cart'/>\n";
		}
		
		$output .= "<input type='submit' id='".$item_id."' ".$add_class." name='add' value='".$item['b_title']."'/>\n";
		$output .= "</form>\n";
		$output .= "</p>\n";
		$output .= "</div>\n";

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