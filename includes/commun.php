<?php

/**
 * Cart Request
 */





/**
 * Return Items Options Pair
 */

function scabn_item_options_old ($options_arr){

	foreach($options_arr as $key=>$value){

	   $options_pair = $key."-".$value."<br />";

	}

	return $options_pair;

}

/* Simple options in - format */
function scabn_item_options ($options_arr,$separator="<br/>"){
	$begin=TRUE;
	if ( count($options_arr) == 1 )	{
   	    foreach($options_arr as $key=>$value) {
		$options_pair = $value;
            }
	} else {
   	    foreach($options_arr as $key=>$value) {
	        $options_pair = $key."-".$value;
	        if ($begin == FALSE) {
	            $options_pair .= $separator;
	        } else {
		    $begin=FALSE;
	       }
            }
        }
	return $options_pair;

}




/**
 *  Currency code & symbol
 */


function scabn_curr_symbol($code){	

    $scabn_currency_codes= scabn_Backend::getCurrencies();
	
	$d = $scabn_currency_codes[$code][0];
	
	$symbol = "&#".$d.";";
	
	return $symbol;

}



?>
