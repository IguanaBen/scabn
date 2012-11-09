<?php

/**
 * Cart Request
 */


function scabn_request(){
	//This function handles all the IO via GET / POST requests.
	//Probably a good place to sanitize the data.
	
	$cart =& $_SESSION['wfcart']; // get the cart

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_item'  ){
		

		if ( isset($_REQUEST['item_options']) && (  $_REQUEST['item_options'] != '')  ) {
			//item options set -- check if it is a list of options with ':' as separator			        		
        	$temp=explode(':',$_REQUEST['item_options']);
        	//if it is, it should be formatted as 'optionname:price' 
			if ( count($temp) == 2) {
				$price=floatval($temp[1]);
				$itemoptionvalue=sanitize_text_field($temp[0]);

			} else {
				$price=floatval($_REQUEST['item_price']);
				$itemoptionvalue=sanitize_text_field($_REQUEST['item_options']);
			}

			$item_options = array (sanitize_title($_REQUEST['item_options_name']) => $itemoptionvalue);
			$item_id = sanitize_title($_REQUEST['item_id']."-".$itemoptionvalue);

		} else {
			$item_options = array ();
			$price = floatval($_REQUEST['item_price']);
			$item_id = sanitize_title($_REQUEST['item_id']);
		}		
		$cart->add_item($item_id,intval($_REQUEST['item_qty']),$price,sanitize_text_field($_REQUEST['item_name']),$item_options,esc_url($_REQUEST['item_url']),floatval($_REQUEST['item_weight']));		
	}

	if (isset ($_REQUEST['remove']) && $_REQUEST['remove'] ){
	   $cart->del_item(sanitize_title($_REQUEST['remove']));
	}

	if (isset($_REQUEST['empty']) && $_REQUEST['empty']  ){
	   $cart->empty_cart();
	}

	if (isset($_REQUEST['update']) && $_REQUEST['update']  ){				
		for ($i=0; $i<sizeof($cart->items); $i++){
			if (ctype_digit($_POST['qty_'.$i])){												
				$cart->edit_item(sanitize_title($_POST['item_'.$i]),intval($_POST['qty_'.$i]));
		   	}
		}
	}

	if (isset($_REQUEST['update_item']) && $_REQUEST['update_item']  ){
	   if (ctype_digit($_REQUEST['qty'])){
	   	$cart->edit_item(sanitize_title($_REQUEST['id']),intval($_REQUEST['qty']));
	   }
	}


}


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
