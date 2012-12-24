<?php





/**
 * The Cart
 */


/**
 * Cart Process
 */


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
	
	if ($paypal_url == "Live" ) {
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
