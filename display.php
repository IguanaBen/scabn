<?php

/* This class handles SCABN's Display -- 
	Generating HTML, etc. 
	
	All functions here are called via actions / filters
	so they can easily be replaced / hijacked for customization.
*/



class scabn_Display {

	function __construct() {
		add_action('scabn_display_item_options',array($this, 'display_item_options'),10,1);
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




}




?>