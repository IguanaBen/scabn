<?php

class scabn_Backend {
	
	function getCurrencies() {
		return array(
						"AUD" => array (36, "Australian Dollar AUD"),
						"CAD" => array (36, "Canadian Dollar CAD"),
						"CZK" => array (75, "Czech Koruna CZK"),
						"DKK" => array (107, "Danish Krone DKK"),
						"EUR" => array (8364, "Euro EUR"),
						"HKD" => array (36, "Hong Kong Dollar HKD"),
						"HUF" => array (70, "Hungarian Forint HUF"),
						"ILS" => array (8362, "Israeli New Sheqel ILS"),
						"JPY" => array (165, "Japanese Yen JPY"),
						"MYR" => array ('82;&#77', "Malaysia Ringgit MYR"),
						"MXN" => array (36, "Mexican Peso MXN"),
						"NOK" => array (107, "Norwegian Krone NOK"),
						"NZD" => array (36, "New Zealand Dollar NZD"),
						"PLN" => array (122, "Polish Zloty PLN"),								
						"GBP" => array (163, "Pound Sterling GBP"),
						"SGD" => array (36, "Singapore Dollar SGD"),
						"SEK" => array (107, "Swedish Krona SEK"),
						"CHF" => array (67, "Swiss Franc CHF"),
						"USD" => array (36, "U.S. Dollar USD")
						);						  
	}
	
	//List of Paypal URLs with Label. Used to generate form for
	//Paypal butnow button.	
	function paypal_urls() {
		return	array('Live'=>'https://www.paypal.com/cgi-bin/webscr','Sandbox'=>'https://www.sandbox.paypal.com/cgi-bin/webscr');
	}

	
}


?>