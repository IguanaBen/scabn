<?php

/* This class handles SCABN's Display --
	Generating HTML, etc.

	All functions here are called via actions / filters
	so they can easily be replaced / hijacked for customization.
*/



class scabn_Display {

	function __construct() {
		add_action('scabn_display_item_options',array($this, 'display_item_options'),10,1);
		add_action('scabn_display_currency_symbol',array($this, 'display_currency_symbol'),10,0);
		add_action('scabn_add_css',array($this, 'add_css'),10,0);
		add_action('scabn_displayCustomCart',array($this,'displayCustomCart'),10,1);
		add_action('scabn_displayCartUUID', array($this,'enter_cart_uuid'),10,0);
		add_action('scabn_display_add_to_cart', array($this,'display_add_to_cart'),10,1);
		add_action('scabn_display_widget', array($this,'display_widget'),10,1);
		add_action('scabn_display_cart', array($this,'display_cart'),10,1);
		add_action('scabn_displayCustomCartContents', array($this,'displayCustomCartContents'),10,1);
		add_action('scabn_display_paypal_receipt',array($this, 'display_paypal_receipt'),10,1);

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


	function display_paypal_receipt($keyarray) {
		$output="";
		$firstname = $keyarray['first_name'];
		$lastname = $keyarray['last_name'];

		$amount = $keyarray['payment_gross'];

		$output .= "<p><h3>";
		$output .= __("Checkout Complete -- Thank you for your purchase!",'SCABN');
		$output .= "</h3></p>";
		$output .= "<h4>";
		$output .=__("Checkout Complete -- Thank you for your purchase!Payment Details",'SCABN');
		$output .="</h4><ul>\n";
		$output .= "<li>";
		$output .= __("Name:",'SCABN').trim($firstname)." ".trim($lastname);
		$output .= "</li>\n";
		$output .= "<li>";
		$output .=__("Total Amount:",'SCABN').trim($amount)."</li>\n"; 
		$output .= "</ul>";
		$output .= __("You will receive a confirmation e-mail when payment for the order clears and a second email when your order ships.",'SCABN');  
		$output .= __("You may log into your paypal account at <a href=\"https://www.paypal.com/us\">paypal</a> to view details of this transaction.",'SCABN'); 
		return $output;
}



	function enter_cart_uuid(){
		$output="<BR>".__("Please enter the custom cart id here",'SCABN').":
			<form name=\"input\" action=\"custom-cart\" method=\"GET\">
			Custom Cart ID: <input type=\"text\" name=\"ccuuid\" /><p>
			<input type=\"submit\" value=\"__(Submit','SCABN')\" /></p>
			</form>";
		return $output;
	}

	function displayCustomCartContents($items) {
		$output="";
		if ($items) {
			$output .="<table border='0' cellpadding='5' cellspacing='1' class='entryTable' align='center' width='96%'>
		<thead> <tr class=\"thead\"><th scope=\"col\">";
		$output .=__("Qty",'SCABN');
		$output .="</th>			<th scope=\"col\">";
		$output .=__("Items",'SCABN');
		$output .="</th><th scope=\"col\" align=\"right\">";
		$output .=__("Unit Price",'SCABN');
		$output .="</th></tr></thead>";	
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

	
	function displayCustomCart($uuid) {				
		//This is a function that takes as custom cart uuid number
		//and generates a custom cart. We do a db query to get
		//the item(s) and pricing, etc, and then call paypal / google functions
		//to make a buy now buttons.
				
		$output = "";		
		$items=apply_filters('scabn_getCustomCart',$uuid);		
		if ($items) {
	
			$output .= apply_filters('scabn_displayCustomCartContents',$items);
			$output .= scabn_paypal::make_button($items);	
			//$output .= scabn_google::make_button(getShippingOptions($items),$items);
		} else {
			$output .= '<h4>'.__("Could not find your custom cart, or the cart has expired",'SCABN').'</h4>';
			$output .= apply_filters('scabn_displayCartUUID','');
		}
		return $output;
	}



	function display_add_to_cart($item) {
		//Displays the 'add to cart' button on pages. Contains
		//both the visual data and the form submission for adding
		//items to the cart.

		global $post;
                $cart =& $_SESSION['wfcart']; // load the cart from the session

		if (array_key_exists('name',$item)) {
			$item_id=sanitize_title($item['name']);
			$name=$item['name'];
		} else {
			$item_id=sanitize_title($post->post_title);
			$name=$post->post_title;
		}
		$scabn_options=get_option('scabn_options');
		$currency = apply_filters('scabn_display_currency_symbol',NULL);

		if (array_key_exists('no_cart',$item)) {
			$action_url = $scabn_options['cart_url'];
			$add_class = '';
		} else {
			$action_url = get_permalink();
			$add_class = 'class="add"';
		}

		$output = "<div class='addtocart'>\n";
		$output .= "<form method='post' class='".$item_id."' action='".$action_url."'>\n";
		$output .= wp_nonce_field( 'add_to_cart', 'scabn-add', false, false );
		$output .= "<input type='hidden' value='add_item' name='action'/>\n";
		$output .= "<input type='hidden' class='item_url' value='".get_permalink()."' name='item_url'/>\n";
		$output .= "<input type='hidden' value='".$cart->random()."' name='randomid'/>\n";
		$output .= "<input type='hidden' value='".$item_id."' name='item_id'/>\n";
		$output .= "<input type='hidden' class='item_name' value='".$name."' name='item_name'/>\n";
		if (array_key_exists('price',$item)) $output .= "<input type='hidden' class='item_price' value='".$item['price']."' name='item_price'/>\n";
		if (array_key_exists('fshipping',$item)) $output .= "<input type='hidden' class='item_shipping' value='".$item['fshipping']."' name='item_shipping'/>\n";
		if (array_key_exists('weight',$item)) $output .= "<input type='hidden' class='item_weight' value='".$item['weight']."' name='item_weight'/>\n";

		$output .= "<p id='cartname'>".$name . "</p>";
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
			$output .= 	__("Unit Price: ",'SCABN').trim($currency.number_format($item['price'],2)).__(" each",'SCABN')."<br/>";
			
		}

		if(array_key_exists('qty_field',$item)) {
			$output .= __("Qty:",'SCABN');
			$output .= "<input type='text' class='item_qty' value='1' size='2' name='item_qty'/>\n";
		} else {
			$output .= "<input type='hidden' class='item_qty' value='1' size='2' name='item_qty'/>\n";
		}

		if (array_key_exists('no_cart',$item)) {
			$output .= "<input type='hidden' value='true' name='no_cart'/>\n";
		}
		if (array_key_exists('b_title',$item)) {
			$b_title=$item['b_title'];
		} else {
			$b_title=__('Add to Cart','SCABN');
		}
		$output .= "<input type='submit' id='".$item_id."' ".$add_class." name='add' value='".$b_title."'/>\n";
		$output .= "</form>\n";
		$output .= "</p>\n";
		$output .= "</div>\n";

	return $output;
	}



	function display_currency_symbol(){	
  		$scabn_currency_codes= scabn_Backend::getCurrencies();
  		$options = get_option('scabn_options');
		$d = $scabn_currency_codes[$options['currency']][0];
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


	function display_cart($carttype){
				
		//$carttype is 'widget' or 'checkout'					
		$cart = $_SESSION['wfcart'];
		$options = get_option('scabn_options');
		$cart_url = $options['cart_url'];
		$currency = apply_filters('scabn_display_currency_symbol',Null);

		if ($carttype == 'widget'){
			$output = "<div id='scabn_widget'>"; 						
		} else {			
			$output = "<div id='wpchkt_checkout'>";			
		}


		if(count($cart->items) != 0) {
			$output .= "<form action='' method='post'>";
			$output .= "<input type='hidden' value='".$cart->random()."' name='randomid'/>\n";
			$output .= "<table border='0' cellpadding='5' cellspacing='1' class='entryTable' align='center' width='96%'>";	
			$output .= "	<thead><tr class='thead'>";
			$output .= "   	<th scope='col'>".__("Qty",'SCABN')."</th>";
			$output .= "     <th scope='col'>".__("Items",'SCABN')."</th>";
			$output .= "     <th scope='col' align='right'>".__("Unit Price",'SCABN')."</th>";
			$output .= "	</tr></thead>";	
			$i=0;
			foreach($cart->get_contents() as $item) {
				$output .= "<tr class = 'ck_content'><td>";
                $output .= "<input type='hidden' name='item_".$i."' value='".$item['id']."' />";
                 $output .= "<input type='text' name='qty_".$i."' size='2' value='".$item['qty']."' class = 'qty_".$item['id']."' title='".$item['id']."' /></td>";
				$output .= "<td><a href='". $item['url']."'><strong>".$item['name']."</strong><br />";                				
				if (count($item['options']) > 0){
					$output .= apply_filters('scabn_display_item_options',$item['options']);
				} 
				$output .= "</a></td>";
				$output .= "<td align='right'>".$currency." ".number_format($item['price'],2)."<br />";

				$remove_query = array();
				$remove_query['remove'] = $item['id'];
				$remove_query['randomid'] = $cart->random();
				$remove_url = add_query_arg($remove_query);
				
				$output .= "<a href='".$remove_url."' class ='remove_item' name = '".$item['id']."'>".__("Remove",'SCABN')."</a></td></tr>";
				$i ++;
			}

			$output .= "<tr class='ck_content'>";
			$output .= "<td><input type='submit' name='update' value="."'".__("Update",'SCABN')."'". "class ='update_cart' /></td>";				

			if ($carttype == 'widget' ){
				$output .= "<td align='right' colspan='1'><strong>".__("Sub-total",'SCABN')."</strong></td>";
				$output .= "<td align='right'><strong>".$currency." ".number_format($cart->total,2)."</strong></td>";	
			} else {
				$output .= "<td align='right' colspan='1'>Sub-total</td>";
				$output .= "<td align='right'>".$currency." ".number_format($cart->total,2)."</td>";			
			}
			
			$output .= "</tr>";
			
			
			if ($carttype != 'widget') {
				$output .= "<tr class='ck_content shipping'>";				
				$output .= "<td align='right' colspan='2'>".__("Shipping",'SCABN')."</td>";
				$output .= "<td align='right'>TBD</td></tr>";
			}
     		if (empty($cart_url)) {
				 $output .= "<span class='val_error'><strong>Configuration Error:</strong> Include the Checkout/Process Page Url in the SCABN Plugin Settings</span>";
			} elseif ($carttype == 'widget') {	 
         	$output .= "<tr><td class='ck_content go_to_checkout' colspan='3'>";
				$output .= "<div style='text-align: right'><span class='go_to_checkout'><a href='".$cart_url."'><strong>".__("Go to Checkout",'SCABN')."</strong></a> </span></div>"; 
			}
									
			if ($carttype != 'widget') {
				$output .= "<tr class='ck_content total'>";
				$output .= "<td align='right' colspan='2'><strong>".__("Total",'SCABN')."</strong></td>";
				$output .= "<td align='right'><strong>".$currency." ".number_format($cart->total,2)."</strong></td>";
				$output .= "</tr>";
			}
			$output .= "</td></tr></table></form>";	
        
		} else {  	   
			$output .= "<span class='no_items'>".__("No items in your cart","SCABN")."</span>";
        
  		} 	
		$output .= "</div>";				
		return $output;
	}

				  









}




?>
