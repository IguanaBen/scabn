<?


/* This class handles SCABN's paypal interactions -- generatingg
   content to send to Paypal for shopping cart, handling
   receipt page, etc.
*/


class scabn_paypal {

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
			echo "<br/>Errstr:" . $errstr."<br/>Errno: ". $errno. "<br/>";
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
	
	
				$output .= display_paypal_receipt($keyarray);
	
			}
			else if (strcmp ($lines[0], "FAIL") == 0) {
				$output .= "<h4>Paypal failed to recognize order -- Maybe order too old or does not exist.</h4>";
				//print_r($lines);		
				//$output .= display_paypal_receipt($keyarray);
			} else {
				$output .= "Unknown error from Paypal's response. (order probably completed)";
				$output .= "<br/>Details:<br/>";
				print_r($lines);
			}
		}

	
	return $output;

	}





}