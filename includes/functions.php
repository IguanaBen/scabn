<?php


/**
 *  Shortcode
 */





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
		$title = isset($instance['title']) ? esc_attr($instance['title']) : 'Cart Checkout';
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

	$options = get_option('scabn_options');
	$post_url = add_query_arg( array() );
	$remove_url = "";
	$cart_url = $options['cart_url'];
	$cart_theme = $options['cart_theme'];
	$cart_type = $options['cart_type'];
	//$currency = scabn_curr_symbol($options['currency']);
	$currency = apply_filters('scabn_display_currency_symbol',$options['currency']);
	$cart = $_SESSION['wfcart'];
	$output="";
	
	if ( $options['analytics_id'] != '' ) {
		$output .= "<script src=\"http://checkout.google.com/files/digital/ga_post.js\"  type=\"text/javascript\"></script>";
	}

	if ( file_exists(SCABN_PLUGIN_DIR."/templates/".$cart_theme."/shopping_cart.php")) {
		include (SCABN_PLUGIN_DIR."/templates/".$cart_theme."/shopping_cart.php");
	} else {
		include (SCABN_PLUGIN_DIR."/templates/default/shopping_cart.php");
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

	//global $scabn_options;

	//$options = $scabn_options;
	$options = get_option('scabn_options');
	//require_once SCABN_PLUGIN_DIR. '/includes/paypal.class.php';
	//global $scabn_states_code;
	//global $scabn_country_code;


	$post_url = add_query_arg( array() );
	$currency = apply_filters('scabn_display_currency_symbol',$options['currency']);		
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
		
		if(count($cart->items) > 0) {						
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
			$ppoptions[]=array("item_name_". (string)$count,$item['name']." (".apply_filters(scabn_display_item_options,$item['options']).")");
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

	$gc_merchantid = $options['gc_merchantid'];
	$gc_merchantkey=$options['gc_merchantkey'];

	$gc="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
	<checkout-shopping-cart xmlns=\"http://checkout.google.com/schema/2\">";
	$gc .= "<shopping-cart>\n\t<items>";

	foreach($items as $item) {
		$gc .= "\n\t\t<item>";
		if ( $item['options']  ) {
			$gc .= "\n\t\t\t<item-name>".$item['name']." (".apply_filters(scabn_display_item_options,$item['options']).")</item-name>";
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
}
return $output;
?>
