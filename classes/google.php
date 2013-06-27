<?php


/* This class handles SCABN's Google Wallet interactions -- generating
   content to send to Google Wallet for shopping cart, handling
   receipt page, etc.
*/


class scabn_google {

	function google_shipping_XML($shipoptions){
		$options=get_option('scabn_options');
		$gc = "\n<checkout-flow-support>
	    <merchant-checkout-flow-support>
	      <shipping-methods>";

		foreach($shipoptions as $soption) {
			$gc .= "\n\t<flat-rate-shipping name=\"". $soption['name'] . "\">";
			$gc .= "\n\t<price currency=\"".$options['currency']."\">".$soption['price']. "</price>";
			$gc .= "\n\t<shipping-restrictions>";
			$gc .= "\n\t\t<allowed-areas>";

			if (array_key_exists('regions', $soption )) {
				$gc .= "\n\t\t\t<postal-area>";
				foreach($soption['regions'] as $region) {
					$gc .= "\n\t\t\t\t<country-code>".$region."</country-code>";
				}
				$gc .= "\n\t\t\t</postal-area>";
			} else {
				$gc .= "\n\t\t\t<world-area/>";
			}
			
			$gc .= "\n\t\t</allowed-areas>";
			
			$gc .= "\n\t\t<excluded-areas>";			
			if (array_key_exists('notregions', $soption )) {
				$gc .= "\n\t\t\t<postal-area>";
				foreach($soption['notregions'] as $region) {						
					$gc .= "\n\t\t\t\t<country-code>".$region."</country-code>";
				} 
			$gc .= "\n\t\t\t</postal-area>";
			}
						
			$gc .= "\n\t\t</excluded-areas>";
						
			$gc .= "\n\t</shipping-restrictions>";
	      $gc .= "\n\t</flat-rate-shipping>";
		}		
		$gc .= "\n</shipping-methods></merchant-checkout-flow-support></checkout-flow-support>\n";
				
		return $gc;		
	}


			
	function make_button($shipoptions,$items) {
		$options=get_option('scabn_options');
		$gc_merchantid = $options['gc_merchantid'];
		$gc_merchantkey=$options['gc_merchantkey'];

		//If no merchant ID, don't bother or create new button.
		if ( $gc_merchantid != "" ) {

		$gc="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<checkout-shopping-cart xmlns=\"http://checkout.google.com/schema/2\">";
		$gc .= "<shopping-cart>\n\t<items>";
	
		foreach($items as $item) {
			$gc .= "\n\t\t<item>";
			if ( $item['options']  ) {
				$gc .= "\n\t\t\t<item-name>".$item['name']." (".apply_filters('scabn_display_item_options',$item['options']).")</item-name>";
			} else {
				$gc .= "\n\t\t\t<item-name>".$item['name']."</item-name>";
			}
			$gc .= "\n\t\t\t<item-description>".$item['name']."</item-description>";
			$gc .= "\n\t\t\t<unit-price currency=\"".$options['currency']."\">".$item['price']."</unit-price>";
			$gc .= "\n\t\t\t<quantity>".$item['qty']."</quantity>";
			$gc .= "\n\t\t</item>";
			}
	
		$gc .= "\n\t</items></shopping-cart>";
		$gc .= apply_filters('scabn_google_shipping_XML',$shipoptions);				
		//End Google Cart
		$gc .= "\n</checkout-shopping-cart>"; 
		$b64=base64_encode($gc);
		$gout="";
	 	if ( $options['analytics_id'] != '' ) {
			$gout.= "<form method=\"POST\" onsubmit=\"_gaq.push(function() {var pageTracker = _gat._getTrackerByName('myTracker');setUrchinInputCode(pageTracker);});\" action=\"https://checkout.google.com/api/checkout/v2/checkout/Merchant/".$gc_merchantid."/\">";
		} else {
			$gout.= "<form method=\"POST\" action=\"https://checkout.google.com/api/checkout/v2/checkout/Merchant/".$gc_merchantid."/\">";
		}
	
	 	$gout .= "<input type=\"hidden\" name=\"cart\" value=\"". $b64."\">";
		$gout .= "<input type=\"hidden\" name=\"analyticsdata\" value=\"\">";
	
		if ( $gc_merchantkey != "" ) {
 			$gcsig = base64_encode(scabn_google::CalcHmacSha1($gc,"$gc_merchantkey"));			
			$gout .= "<input type=\"hidden\" name=\"signature\" value=\"$gcsig\">";
		}
	
		$gout .= "<input type=\"image\" border=\"0\" name=\"submit\" src=\"https://checkout.google.com/buttons/checkout.gif?merchant_id=".$gc_merchantid."&w=160&h=43&style=trans&variant=text&loc=en_US\" alt=\"Make payments with Google Wallet\"></form>";		
		return $gout;
	
		}
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
			return $hmac;
	}



}


?>
