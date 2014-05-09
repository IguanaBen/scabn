<?php


/* This class handles SCABN's paypal interactions -- generating
   content to send to Paypal for shopping cart, handling
   receipt page, etc.
*/

//add_action( 'wp_ajax_cartcontents', 'scabn_authorize::cartcontents');
//add_action( 'wp_ajax_nopriv_cartcontents', 'scabn_authorize::cartcontents');


class scabn_authorize {

	function __construct() {
		//add_action( 'wp_ajax_cartcontents', array($this, 'cartcontents'));
		//add_action( 'wp_ajax_nopriv_cartcontents', array($this, 'cartcontents'));
	}


	static function charge(){
		$data=array();					
		$options=get_option('scabn_options');
		$cart = $_SESSION['wfcart'];
				
		if ( $options['auth_login_id'] != "" ) { 	
			$transkey=$options['auth_transaction_key'];
			$url='https://iguanaworks.net/Scripts/Log.php';
			$url='https://test.authorize.net/gateway/transact.dll';
			$data['x_login']=$options['auth_login_id'];			
			$data['x_fp_sequence']=time();
			$data['x_fp_timestamp']=$data['x_fp_sequence'];
			$data['x_currency_code']=$options['currency'];											
			$data['x_amount'] = sprintf("%.2f",$cart->total,2);
			
			$hash=$data['x_login'].'^'.$data['x_fp_sequence'].'^'.$data['x_fp_timestamp'].'^'.$data['x_amount'].'^'.$data['x_currency_code'];
			$data['x_fp_hash']= hash_hmac("md5",$hash,$transkey);			

			
			$data['x_recurring_billing']='F';
			$data['x_relay_response']='TRUE';
			$data['x_relay_url']='https://iguanaworks.net/Scripts/Log.php';
			$data['x_type']='AUTH_CAPTURE';
			$data['x_method']='CC';
			$data['x_version']='3.1';
			
			$options = array(
    			'http' => array(
	        		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
   	     		'method'  => 'POST',
        			'content' => http_build_query($data),
    			),
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			
			$output="This is what was sent\n";
			foreach ($data as $key => $value) {
        		$value = urlencode(stripslashes($value));
        		$output .= $key . " -> " . $value . "\n";
        	}
        	mail('b@iguanaworks.net', "Sent to Auth.net", $output);
	
			
			
		}		

	} 
	
	


	static function make_button($items) {
				
		
		$options=get_option('scabn_options');
		$currency = $options['currency'];
		$cart_url = $options['cart_url'];
					
		$url=site_url('/wp-admin/admin-ajax.php');				
		//$out = "<form><input type=\"submit\"></form>";		
		$out ="<script src='https://ajax.googleapis.com/ajax/libs/angularjs/1.2.7/angular.min.js'></script>";		
		//$out .="<script src='//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.9.0/ui-bootstrap-tpls.min.js'></script>";
		$out .="<script src='//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.10.0/ui-bootstrap-tpls.min.js'></script>";
		
		//$out .="<link rel='stylesheet' href='//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css'>";
		$out .="<link rel='stylesheet' href='".site_url('wp-content/plugins/simple-cart-buy-now/bootstrap-modal.css')."'/>";		
		//$out .="<link rel='stylesheet' href='".site_url('wp-content/plugins/simple-cart-buy-now/bootstrap-modal-full.css')."'/>";
		//$out .="<link rel='stylesheet' href='".site_url('wp-content/plugins/simple-cart-buy-now/bootstrap.css')."'/>";

		$out .="<script src='".site_url('wp-content/plugins/simple-cart-buy-now/classes/authorize.js')."'></script>";
		$out .="<script src='".site_url('wp-content/plugins/simple-cart-buy-now/angular-payments/lib/angular-payments.js')."'></script>";

		$out .="<script type='text/javascript'>var ajax_url='".site_url( '/wp-admin/admin-ajax.php' )."';</script>";
		//$out.="<style>.modal-content {padding-left:15px;padding-right:15px; }</style>";
		$out.="<style>.modal-open {overflow:hidden; }</style>";

		$out.="<ANY ng-app='auth'><ANY ng-controller='Authorize'>";	
		//$out.="<button class='btn btn-default' ng-click=\"open('".$url."')\"> Purchase directly with Credit Card</button>";
		$out.= "<form><input type=\"submit\" value=\"Secure Checkout\" ng-click=\"open('".$url."')\"></form>";	
		//$out.="<br/><div ng-show='selected'>Selection from a modal: {{ selected }}</div>";
		//$out.="Testing: url is {{ajax_url}}<br/>";		
		$out.="</ANY></ANY>";		
		
		$amount=0;
		foreach($items as $item) {
			$amount+=$item['price']*$item['qty'];			
	   }
		
		$fp_sequence = time(); // Any sequential number like an invoice number.
		$login=$options['auth_login_id'];
		$transkey=$options['auth_transaction_key'];
		$fp_timestamp=$fp_sequence;
		$hash=$login.'^'.$fp_sequence.'^'.$fp_timestamp.'^'.sprintf("%.2f",$amount,2).'^';

		$outhash = hash_hmac("md5",$hash,$transkey);
		
		//$out.="Total to charge: $amount<br/>";		
		//$out.="Hash based on '$hash'<br/>";
		//$out.="Real Hash: $outhash <br/>";
		//$out.="Ajax URL is ".site_url( '/wp-admin/admin-ajax.php' )."<br/>";
		
		return $out;
	}

	
}




?>