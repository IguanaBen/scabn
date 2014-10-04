<?php


/* This class handles SCABN's paypal interactions -- generating
   content to send to Paypal for shopping cart, handling
   receipt page, etc.
*/


class scabn_paypal {

	static function make_button($items) {
		$options=get_option('scabn_options');
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


		//If no Paypal email, skip everything, don't make a button.
		if ( $paypal_email != "" ) {
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
				$ppoptions[]=array("item_name_". (string)$count,$item['name']." (".apply_filters('scabn_display_item_options',$item['options']).")");
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
			exec('export RANDFILE="/tmp/www.rnd";'.$openssl_cmd, $output, $error);
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
		$ppo .= "<input type=\"image\" border=\"0\" name=\"submit\" src=\"https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif\" alt=\"Make payments with PayPal - it's fast, free and secure!\"></form>";
		return $ppo;
		}
	}




	function receipt($tx_token) {
		//This request came from paypal as their receipt page
		//We must send confirmation to them to get info:
		$scabn_options = get_option('scabn_options');
		
		// read the post from PayPal system and add 'cmd'				 
		
		//generate cmd / tx variables to push to Paypal to authorize
		//data dump
		$auth_token = $scabn_options['paypal_pdt_token'];
		$req = 'cmd=_notify-synch';
		$req .= "&tx=$tx_token&at=$auth_token";
	
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

		//Connect to Paypal via http or https depending on settings.
		if ($scabn_options['paypal_connection'] == 'https' ) {
			$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);						
		} else {
			$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);			
		}
					
		if (!$fp) {
			echo "Error Sending data to Paypal -- (order probably completed)<br/>";			
			echo "Errstr:" . $errstr."<br/>Errno: ". $errno. "<br/>";
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
	         
	         /*Add Analytics Ecommerce Code to track purchase in analytics*/
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
		//print_r($keyarray);
		$output .= apply_filters('scabn_display_paypal_receipt',$keyarray);
		//echo $output;

	}
			else if (strcmp ($lines[0], "FAIL") == 0) {
				$output .= "<h4>Paypal failed to recognize order -- Maybe order too old or does not exist.</h4>";
			} else {
				$output .= "Unknown error from Paypal's response. (order probably completed)";
				$output .= "<br/>Details:<br/>";
				print_r($lines);
			}
		}

	//$output .= apply_filters('scabn_display_paypal_receipt','asdf');
	return $output;

	}





}

?>
