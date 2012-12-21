<?php


function scabn_ini(){

	session_start();      // start the session

	$cart =& $_SESSION['wfcart'];
	if(!is_object($cart)) $cart = new wfCart();

	global $scabn_options;
	$options = $scabn_options;
	$cart->c_info($options['cart_type'], $options['cart_title'], $options['cart_url'], $options['currency'], $options['cart_theme']);

	scabn_request();

	//scabn_w_register();

}

/**
 * Inserting files on the header
 */
function scabn_head() {

	global $scabn_options;
	$options = $scabn_options;

	$scabn_header =  "\n<!-- Simple Cart and Buy Now -->\n";
	$scabn_header .= "<script type=\"text/javascript\">scabn_c_url =\"".SCABN_PLUGIN_URL."/includes/scabn_ajax.php\";</script>\n";
	if (file_exists(SCABN_PLUGIN_DIR . "/templates/" . $options['cart_theme'] . "/scabn.js ")) {
		$scabn_header .= "<script type=\"text/javascript\" src=\"".SCABN_PLUGIN_URL."/templates/".$options['cart_theme']."/scabn.js\"></script>\n";	
	} else {
	$scabn_header .= "<script type=\"text/javascript\" src=\"".SCABN_PLUGIN_URL."/templates/default/scabn.js\"></script>\n";
	}
	if (file_exists(SCABN_PLUGIN_DIR."/templates/".$options['cart_theme']."/style.css")) {
		$scabn_header .= "<link href=\"".SCABN_PLUGIN_URL."/templates/".$options['cart_theme']."/style.css\" rel=\"stylesheet\" type=\"text/css\" />\n";	
	} else {
		$scabn_header .= "<link href=\"".SCABN_PLUGIN_URL."/templates/default/style.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
	}
	$scabn_header .=  "\n<!-- Simple Cart and Buy Now End-->\n";

	print($scabn_header);

}

/**
 *  Get Options or default
 */
function get_scabn_options(){

	global $scabn_options;

	// Default Values
	$scabn_options_d = array(
								'cart_url' => '',
								'currency' => 'USD',
								'paypal_url' => 'sandbox',
								'paypal_email' => '',
								'cart_theme' => 'default',
								'cart_title' => 'Shopping Cart',
								'cart_type' => 'full',
								'version' => '1.0.2'
							  );

	if ( isset($scabn_options) )	return $scabn_options;

	$scabn_options = get_option('scabn_options');

	if (  empty($scabn_options) ) $scabn_options = $scabn_options_d;

	return $scabn_options;

}

function displayCustomCart($uuid) {
	//This is a function that takes as custom cart uuid number
	//and generates a custom cart. We do a db query to get
	//the item(s) and pricing, etc, and then call paypal / google functions
	//to make a buy now buttons.

	$options=get_scabn_options();
	$output = "";
	$items=getCustomCart($uuid);
	if ($items) {

		$output .= displayCustomCartContents($items);
		$output .= scabn_make_paypal_button($options,$items);
		$output .= scabn_make_google_button($options,$items);
	} else {
		$output .= 'Could not find your custom cart, or the cart has expired';
	}
	return $output;
}

add_action('displayCustomCart','displayCustomCart',10,1);



function scabn_customcart() {
	if ( isset($_GET['ccuuid'])) {
		$uuid=$_GET['ccuuid'];
	} else if ( isset($_POST['ccuuid'])) {
		$uuid=$_POST['ccuuid'];
	}


	if ( isset($uuid)) {
		/*$output=displayCustomCart($uuid);*/
		$output=do_action('displayCustomCart',$uuid);

	} else {
		$output="<BR>Please enter the custom cart id here:
		<form name=\"input\" action=\"custom-cart\" method=\"GET\">
		Custom Cart ID: <input type=\"text\" name=\"ccuuid\" /><p>
		<input type=\"submit\" value=\"Submit\" /></p>
		</form>";


	}
	return $output;

}

/**
 *  Shortcode
 */
function scabn_sc($atts) {

	global $post;

	//session_start();

	$cart = $_SESSION['wfcart'];

	extract(shortcode_atts(array(
			'name' => $post->post_title,
           //'url' => $post->guid,
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

		global $post;

		$id = $post->ID;
		$url =  $post->guid;

	   global $scabn_options;

      $currency = scabn_curr_symbol($scabn_options['currency']);

		if ($no_cart) {
			$action_url = SCABN_PLUGIN_URL."/includes/scabn_ajax.php";
			$add_class = '';
		} else {
			$action_url = add_query_arg( array() );
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

		//$output .= "<table border='0' cellspacing='0' cellpadding='5'>\n";
		//$output .= "<p id='cartname'>".$name . " (".$currency.number_format($price,2)." each)</p>";
		$output .= "<p id='cartname'>".$name . "</p>";
		$output .= "<p id='cartcontent'>";
		//$output .= "Unit Price: ".$currency.number_format($price,2)." each<br/>";
		//$output .= "<tr><td align='right'>Price:</td><td align='left'>".$currency." ".number_format($price,2)."</td></tr>\n";


		if (!empty ($options)){
			if ( $options_name != "" ) {
				$output .= $options_name.": \n";
			}
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

		$tx_token = $_GET['tx'];

		if ($tx_token) {
			$cart->empty_cart();
			return scabn_paypal_receipt($tx_token);
		} else {
			return scabn_process();
		}

	}
}


function scabn_paypal_receipt($tx_token) {
	global $scabn_options;
	//This request came from paypal as their receipt page
	//We must send confirmation to them to get info:

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-synch';
	$auth_token = $scabn_options['paypal_pdt_token'];
	//$auth_token='asdf';
	$req .= "&tx=$tx_token&at=$auth_token";

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
	// If possible, securely post back to paypal using HTTPS
	// Your PHP server will need to be SSL enabled
	// $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

	if (!$fp) {
		echo "Error Sending data to Paypal -- (order probably completed)<br/>";
		echo $errno."<br/><br/>" . $errstr."<br/>";
		return False;
	} else {
		fputs ($fp, $header . $req);
		// read the body data 
		$res = '';
		$headerdone = false;
		while (!feof($fp)) {
			$line = fgets ($fp, 1024);
			if (strcmp($line, "\r\n") == 0) {
				// read the header
				$headerdone = true;
			}
			else if ($headerdone)
			{
				// header has been read. now read the contents
				$res .= $line;
			}
		}
		fclose ($fp);
		$output="";
		// parse the data
		$lines = explode("\n", $res);
		$keyarray = array();
		if (strcmp ($lines[0], "SUCCESS") == 0) {
			for ($i=1; $i<count($lines);$i++){
				list($key,$val) = explode("=", $lines[$i]);
				$keyarray[urldecode($key)] = urldecode($val);
			}
         
         /*Add Analytics Ecommerce Code */
         if ($scabn_options['analytics_id'] != '' ) {
         	$output .= "<script type=\"text/javascript\">";
            $output .= "_gaq.push(function() { var pageTracker = _gat._getTrackerByName('myTracker');";
            $output .= "pageTracker._addTrans('" . $keyarray['txn_id'] ."','','" . $keyarray['payment_gross'] . "','" . $keyarray['tax'] . "','" . $keyarray['mc_shipping'] . "','" . $keyarray['address_city'] . "','" . $keyarray['address_state']. "','". $keyarray['address_country_code']. "');";
				$count=$keyarray['num_cart_items'];
				for ( $i = 1; $i <= $count; $i++ ) {
					$item="item_name" . $i;
	            $qty="quantity" . $i;
        	      $cost="mc_gross_" . $i;
					$totalprice=($keyarray[$cost]-$keyarray[$ship]);
		         $price=$totalprice/$keyarray[$qty];
					$output .= "pageTracker._addItem('" . $keyarray['txn_id'] . "','" . $keyarray[$item] . "','" . $keyarray[$item] . "','','" . $price . "','" . $keyarray[$qty] . "');";
				}
				$output.="pageTracker._trackTrans();";
            $output.= "});</script>";
         }


			$output .= display_paypal_receipt($keyarray);

		}
		else if (strcmp ($lines[0], "FAIL") == 0) {
			$output .= "Error parsing Paypal's response. (order probably completed)<br/>";
			$output .= display_paypal_receipt($keyarray);
		} else {
			$output .= "Unknown error from Paypal's response. (order probably completed)";
		}
	}

	
return $output;

}


/**
 *  The widget
 */


class scabnWidget extends WP_Widget {
    /** constructor */
    function scabnWidget() {
		$widget_ops = array('classname' => 'wpchkt_w', 'description' => __( 'Allows to display the shopping cart'));
		$this->WP_Widget('wpchkt_w', __('SCABN Checkout Cart'), $widget_ops);
	}

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
    	extract( $args );
      $title = apply_filters('widget_title', 'Simple Checkout');
		$type= isset($instance['type']) ? esc_attr($instance['type']) : 'full';
		$output="";
      $output .= $before_widget;
      if ($type == 'full' && isset( $title )) {
      	$output .= $before_title . $title . $after_title;
      }
		$output .= "<div id='wpchkt_widget'>";
		$output .=	scabn_cart();
		$output .= "</div>";
		$output .= $after_widget;
		return $output;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {

	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['type'] = strip_tags($new_instance['type']);

	global $scabn_options;

	$options = $scabn_options;

	$cart_type = $options['cart_type'];
	$cart_title = $options['cart_title'];

	$cart_options = array('cart_type' => $cart_type, 'cart_type' => $cart_title);

	$newoptions = array();

	$newoptions['cart_type'] = strip_tags($new_instance['type']);
	$newoptions['cart_title'] = strip_tags($new_instance['title']);

	if ( $cart_options != $newoptions ) {
		$options['cart_type'] = $newoptions['cart_type'];
		$options['cart_title'] = $newoptions['cart_title'];
		update_option('scabn_options', $options);

		$scabn_options = $options;
	}




        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {

		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$type= isset($instance['type']) ? esc_attr($instance['type']) : 'full';
		$output="";
		$output .= "<div class = \"wpchkt_cart_w\">
            <table border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
              <tr>
                <td><input name=\"";
      $output .= $this->get_field_name('type') . "\" type=\"radio\" value=\"full\" ";
      if ( $type == 'full') {
      	$output .= "checked ";
      }
      $output .= "class = \"wpchkt_cart_f\"/></td>
                <td><strong>Use Full Cart</strong></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td>Title <input class=\"widefat\" id=\"" . $this->get_field_id('title') . "\" name=\"" . $this->get_field_name('title') . "\" type=\"text\" value=\"" .  $title; "\" /></td>
              </tr>
            </table>
            </div>

            <div class = \"wpchkt_cart_w\">
            <table border=\"0\" cellspacing=\"5\" cellpadding=\"0\">
              <tr>";
      $output .= "<td><input name=\"" . $this->get_field_name('type') ."\" id=\"" . $this->get_field_id('type') . "\" type=\"radio\" value=\"tiny\" "; 
      if( $type == 'tiny') {
      	$output .= "checked ";
      	}
      $output .= " /></td>
                <td><strong>Use Tiny Cart</strong></td>
              </tr>

            </table>
            </div>";
      return $output;  
    }

} // class scabnWidget


// register scabnWidget widget
add_action('widgets_init', create_function('', 'return register_widget("scabnWidget");'));



/**
 * The Cart
 */

function scabn_cart($type = 'full',$checkout = FALSE) {

	global $scabn_options;

	$options = $scabn_options;

	$post_url = add_query_arg( array() );
	$remove_url = "";
	$cart_url = $options['cart_url'];
	$cart_theme = $options['cart_theme'];
	$cart_type = $options['cart_type'];
	$currency = scabn_curr_symbol($options['currency']);

	$cart = $_SESSION['wfcart'];
	$output="";
	if ($type == "tiny" || $cart_type == "tiny" && !$checkout){
	    foreach($cart->get_contents() as $item) {

		   $item_num = $item_num + $item['qty'];

		}

		if ( file_exists(SCABN_PLUGIN_DIR."/templates/".$cart_theme."/tiny_cart.php")) {
			include (SCABN_PLUGIN_DIR."/templates/".$cart_theme."/tiny_cart.php");
		} else {
			include (SCABN_PLUGIN_DIR."/templates/default/tiny_cart.php");
		}
	} else {
		if ( $options['analytics_id'] != '' ) {
			$output .= "<script src=\"http://checkout.google.com/files/digital/ga_post.js\"  type=\"text/javascript\"></script>";
		}

		if ( file_exists(SCABN_PLUGIN_DIR."/templates/".$cart_theme."/shopping_cart.php")) {
			include (SCABN_PLUGIN_DIR."/templates/".$cart_theme."/shopping_cart.php");
		} else {
			include (SCABN_PLUGIN_DIR."/templates/default/shopping_cart.php");
		}
	}
	return $output;
}

function scabn_add_query_arg ($key,$value){

	$remove_query = array();
	$remove_query[$key] = $value;

	$url = 'http://'.$_SERVER['HTTP_HOST'].add_query_arg($remove_query);

	return $url;

}

/**
 * Cart Process
 */

function scabn_process() {

	global $scabn_options;

	$options = $scabn_options;

	//require_once SCABN_PLUGIN_DIR. '/includes/paypal.class.php';
	//global $scabn_states_code;
	//global $scabn_country_code;


	$post_url = add_query_arg( array() );
	$currency = scabn_curr_symbol($options['currency']);
	$currency_code = $options['currency'];

	$cart =& $_SESSION['wfcart'];
	$show_form=true;
	$output="";
	if(true == $show_form){

		if (isset($error_hash)){

			$output .= "<div class='val_error'>";
			$output .=  "<p><strong>Please fill out the required fields</strong></p>";
			foreach($error_hash as $inpname => $inp_err)
			{
			  $output .=  "<p>$inp_err</p>\n";
			}
			$output .= "</div>";
		}
	   //$output .= "<h3>".$options['cart_title']."</h3>";
		$output .=  "<div id='wpchkt_checkout'>";
		$output .= scabn_cart('full', TRUE);
		$output .=  "</div>";

		if($cart->itemcount > 0) {
			if ( file_exists(SCABN_PLUGIN_DIR."/templates/".$options['cart_theme']."/process.php")) {
				include (SCABN_PLUGIN_DIR."/templates/".$options['cart_theme']."/process.php");
			} else {
				include (SCABN_PLUGIN_DIR."/templates/default/process.php");
			}
		}
	}
	return $output;
}


function scabn_make_paypal_button($options,$items) {
	$currency = $options['currency'];
	$cart_url = $options['cart_url'];
	$paypal_email = $options['paypal_email'];
	$paypal_url = $options['paypal_url'];
	$paypal_pdt_token=$options['paypal_pdt_token'];
	$paypal_cancel_url=$options['paypal_cancel_url'];
	$paypal_cert_id = $options['paypal_cert_id'];
	$OPENSSL=$options['openssl_command'];
	$MY_CERT_FILE= $options['paypal_my_cert_file'];
	$MY_KEY_FILE = $options['paypal_key_file'];
	$PAYPAL_CERT_FILE=$options['paypal_paypal_cert_file'];

	if ($paypal_url == "live" ) {
		$ppo="<form method=\"post\" action=\"https://www.paypal.com/cgi-bin/webscr\">\n";
	} else {
	 	$ppo="<form method=\"post\" action=\"https://www.sandbox.paypal.com/cgi-bin/webscr\"> \n";
	}

	$ppoptions=array();
	$ppoptions[]=array("business",$paypal_email);
	$ppoptions[]=array("cmd","_cart");
	$ppoptions[]=array("currency_code",$currency);
	$ppoptions[]=array("lc","US");
	$ppoptions[]=array("bn","PP-BuyNowBF");
	$ppoptions[]=array("upload","1");
	if ( $paypal_pdt_token != "" ) $ppoptions[]=array("return",$cart_url);
	if ( $paypal_cancel_url != "" ) $ppoptions[]=array("cancel_return",$paypal_cancel_url);
	$ppoptions[]=array("weight_unit","lbs");

	$count=0;
	foreach($items as $item) {
		$count++;
		$ppoptions[]=array("quantity_". (string)$count, $item['qty']);
		if ( $item['options'] ) {
			$ppoptions[]=array("item_name_". (string)$count,$item['name']." (".scabn_item_options($item['options'],'--').")");
		} else {
			$ppoptions[]=array("item_name_". (string)$count,$item['name']);
		}
		$ppoptions[]=array("amount_". (string)$count, $item['price']);
		$ppoptions[]=array("weight_". (string)$count, $item['weight']);
      }

	if (  ( $options['paypal_paypal_cert_file'] != "" ) & ( $options['paypal_key_file'] != "" ) & ( $options['paypal_my_cert_file'] !=  "" ) & ( $options['openssl_command'] != "" ) & (  $options['paypal_cert_id'] !="" ) ) {						
		$ppoptions[]=array("cert_id",$paypal_cert_id);

		$ppencrypt="";
		foreach($ppoptions as $value) {
			$ppencrypt .= $value[0] . "=" . $value[1] . "\n";
			}
		$openssl_cmd = "($OPENSSL smime -sign -signer $MY_CERT_FILE -inkey $MY_KEY_FILE " .
						"-outform der -nodetach -binary <<_EOF_\n$ppencrypt\n_EOF_\n) | " .
						"$OPENSSL smime -encrypt -des3 -binary -outform pem $PAYPAL_CERT_FILE 2>&1";
		exec($openssl_cmd, $output, $error);
		#echo "<BR>DATA:<BR>".$ppencrypt. "<BR>END DATA<BR>";
		if ($error) {
			echo "ERROR: encryption failed: $error<BR>" . implode($output) ;
 		} else {

		$ppo .= "<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">\n";
		$ppo .= "<input type=\"hidden\" name=\"encrypted\" value=\"" . implode("\n",$output) . "\">\n";
		}

	} else {
		//echo "No Encryption";
		foreach($ppoptions as $value) {
			$ppo .= "<input type=\"hidden\" name=\"" . $value[0] . "\" value=\"" . $value[1] . "\">\n";
		}
	}
	$ppo .= "<input type=\"image\" border=\"0\" name=\"submit\"
         src=\"https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif\"
         alt=\"Make payments with PayPal - it's fast, free and secure!\"></form>";
	return $ppo;

}


function CalcHmacSha1($data,$key) {
		$blocksize = 64;
		$hashfunc = 'sha1';
		if (strlen($key) > $blocksize) {
			$key = pack('H*', $hashfunc($key));
		}
		$key = str_pad($key, $blocksize, chr(0x00));
		$ipad = str_repeat(chr(0x36), $blocksize);
		$opad = str_repeat(chr(0x5c), $blocksize);
		$hmac = pack(
			'H*', $hashfunc(
				($key^$opad).pack(
					'H*', $hashfunc(
						($key^$ipad).$data
					)
				)
			)
		);
		//echo $hmac;		
		return $hmac;
}



function scabn_make_google_button($options,$shipoptions,$items) {

	$gc_merchantid= $options['gc_merchantid'];
	$gc_merchantkey=$options['gc_merchantkey'];

	$gc="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<checkout-shopping-cart xmlns=\"http://checkout.google.com/schema/2\">";
	$gc .= "<shopping-cart>\n\t<items>";

	foreach($items as $item) {
		$gc .= "\n\t\t<item>";
		if ( $item['options']  ) {
			$gc .= "\n\t\t\t<item-name>".$item['name']." (".scabn_item_options($item['options'],'--').")</item-name>";
		} else {
			$gc .= "\n\t\t\t<item-name>".$item['name']."</item-name>";
		}
		$gc .= "\n\t\t\t<item-description>".$item['name']."</item-description>";
		$gc .= "\n\t\t\t<unit-price currency=\"".$options['currency']."\">".$item['price']."</unit-price>";
		$gc .= "\n\t\t\t<quantity>".$item['qty']."</quantity>";
		$gc .= "\n\t\t</item>";
		}

	$gc .= "\n\t</items></shopping-cart>";
	$gc .= "\n<checkout-flow-support>
    <merchant-checkout-flow-support>
      <shipping-methods>";

	foreach($shipoptions as $soption) {
		$gc .= "\n\t<flat-rate-shipping name=\"". $soption['name'] . "\">";
		$gc .= "\n\t<price currency=\"".$options['currency']."\">".$soption['price']. "</price>";
		$gc .= "\n\t<shipping-restrictions>";
		$gc .= "\n\t\t<allowed-areas>";
		if ($soption['region'] == "USA" ) {
			$gc .= "\n\t\t<us-country-area country-area=\"ALL\"/>";
		} else {
			$gc .= "\n\t\t<world-area/>";
		}
		$gc .= "\n\t\t</allowed-areas>";
		$gc .= "\n\t\t<excluded-areas>";
		if ($soption['region'] == "NotUSA" ) {
			$gc .= "\n\t\t<us-country-area country-area=\"ALL\"/>";
		}
		$gc .= "\n\t\t</excluded-areas>";
		$gc .= "\n\t</shipping-restrictions>";
        	$gc .= "\n\t</flat-rate-shipping>";
	}
	//End Shipping for Google Wallet
	$gc .= "\n</shipping-methods></merchant-checkout-flow-support></checkout-flow-support>\n";
	//End Google Cart
	$gc .= "\n</checkout-shopping-cart>";

	$b64=base64_encode($gc);
	if ( $gc_merchantkey != "" ) $gcsig=base64_encode(CalcHmacSha1($gc,"$gc_merchantkey"));

 	if ( $options['analytics_id'] != '' ) {
		$gout.= "<form method=\"POST\" onsubmit=\"_gaq.push(function() {var pageTracker = _gat._getTrackerByName('myTracker');setUrchinInputCode(pageTracker);});\" action=\"https://checkout.google.com/api/checkout/v2/checkout/Merchant/".$gc_merchantid."/\">";
	} else {
		$gout.= "<form method=\"POST\" action=\"https://checkout.google.com/api/checkout/v2/checkout/Merchant/".$gc_merchantid."/\">";
	}

 	$gout .= "<input type=\"hidden\" name=\"cart\" value=\"". $b64."\">";
	$gout .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";

	if ( $gc_merchantkey != "" ) {
		$gout .= "<input type=\"hidden\" name=\"signature\" value=\"$gcsig\">";
	}

	$gout .= "<input type=\"image\" border=\"0\" name=\"submit\" src=\"https://checkout.google.com/buttons/checkout.gif?merchant_id=".$gc_merchantid."&w=160&h=43&style=trans&variant=text&loc=en_US\" alt=\"Make payments with Google Wallet\"></form>";
	return $gout;

	}







/* Google Analytics Functions */
function scabn_googleanalytics() {
	global $scabn_options;
	$options = $scabn_options;
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
?>
