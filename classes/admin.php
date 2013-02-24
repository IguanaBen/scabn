<?php
/*
   This class handles all functions used to configure SCABN:
  	Adding Configure SCABN buttons to main configuration page,
  	Adding SCABN icon to edit page/post toolbar, generating
  	content for SCABN's configuration page, etc.
*/


class scabn_Admin {

	function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('admin_init', array($this, 'options_init'));
		add_action('admin_init',array($this,'addbuttons'));
   }


	//I don't understand this -- I copied it, can someone explain?
	static function &init() {
		static $instance = false;
		if ( !$instance ) {
				$instance = new scabn_Admin ;
		}
		return $instance;
	}


	//Add buttons to edit window for easy adding of SCABN shortcode into pages/posts.
	function addbuttons() {
		//If user can edit pages or posts and rich editing is enabled, then add button widget.
		if ( (  current_user_can('edit_posts') || current_user_can('edit_pages') ) && ( get_user_option('rich_editing') == 'true') ) {
   		add_filter('mce_external_plugins', array($this, 'add_scabn_tinymce_plugin'));
   		add_filter('mce_buttons', array($this,'register_button'));

   	}
	}

	function register_button($buttons) {
   	array_push($buttons, "separator", "scabn");
   	return $buttons;
	}

	function add_scabn_tinymce_plugin($plugin_array) {
   	$plugin_array['scabn'] = SCABN_PLUGIN_URL.'/includes/js/tinymce/editor_plugin.js';
   	return $plugin_array;
	}


	//Add page generated by admin_page to admin menu
	function admin_menu() {
	add_submenu_page('plugins.php', 'SCABN Settings', 'SCABN Settings', 'administrator', 'scabn_admin_page', array($this, 'admin_page'));
	}

	//Look for files ending in .php in templates directory
	function get_templates() {
		$templates = array();
		$dir = SCABN_PLUGIN_DIR."/templates/";
		$dh  = opendir($dir);
		while (false !== ($filename = readdir($dh))) {
			if ( substr($filename,strlen($filename)-4,4) == '.php'){
				$templates[substr($filename,0,strlen($filename)-4)]=substr($filename,0,strlen($filename)-4);
			}       				
    	}
		return $templates;
	}
	
	
	
	
	//Loads all the options to be configured using custom_add_settings_field function to
	//simplify process of adding new options.
	function options_init() {				
				
		register_setting( 'scabn_options', 'scabn_options',array($this, 'options_validate'));		
		
		add_settings_section('general_options', 'General Configuration:', array($this,'section_text'), 'general');
		scabn_Admin::custom_add_settings_field('cart_url', 'Checkout Page Url: ', 'general', 'general_options','input_text_option');		
		scabn_Admin::custom_add_settings_field('currency', 'Select Currency: ', 'general', 'general_options','input_selection_custom1',scabn_Backend::getCurrencies());
		scabn_Admin::custom_add_settings_field('template', 'Select Template: ', 'general', 'general_options','input_selection',scabn_Admin::get_templates());

		add_settings_section('paypal_options', 'Required Paypal Settings:', array($this,'section_text'), 'paypal');			
		scabn_Admin::custom_add_settings_field('paypal_email', 'Paypal Email Address: ', 'paypal', 'paypal_options','input_text_option');
		scabn_Admin::custom_add_settings_field('paypal_url', 'Paypal URL: ', 'paypal', 'paypal_options','input_radio',scabn_Admin::display_paypal_url_options());								
		//scabn_Admin::custom_add_settings_field('paypal_cancel_url', 'Paypal Return URL after order cancelled: ', 'paypal', 'paypal_options','input_text_option');
		scabn_Admin::custom_add_settings_field('paypal_connection', 'Connect to Paypal (for receipt page) via HTTPS or HTTP (some servers don\'t support HTTPS', 'paypal', 'paypal_options','input_radio',array('https'=>'Secure (https)','http'=>'Unencrypted (http)'));
		add_settings_section('paypal_optional', 'Optional Paypal Settings:', array($this,'section_text'), 'paypal_op');
		
		scabn_Admin::custom_add_settings_field('paypal_cancel_url', 'Paypal Return URL after order cancelled: ', 'paypal_op', 'paypal_optional','input_text_option');		
		scabn_Admin::custom_add_settings_field('paypal_pdt_token', 'Paypal Payment Data Transfer (PDT) Identity Token: ', 'paypal_op', 'paypal_optional','input_text_option');   
		
		add_settings_section('paypal_encrypt', 'Paypal Encryption Settings:', array($this,'section_text'), 'paypal_en');   
		scabn_Admin::custom_add_settings_field('openssl_command', 'Full system path for openssl command (typical: /usr/bin/openssl): ', 'paypal_en', 'paypal_encrypt','input_text_option');   
		scabn_Admin::custom_add_settings_field('paypal_my_cert_file', 'Full system path for Paypal Certificate File: ', 'paypal_en', 'paypal_encrypt','input_text_option');
		scabn_Admin::custom_add_settings_field('paypal_key_file', 'Full system path for Paypal Key File: ', 'paypal_en', 'paypal_encrypt','input_text_option');
		scabn_Admin::custom_add_settings_field('paypal_paypal_cert_file', 'Full system path for Paypal\'s Certificate File: ', 'paypal_en', 'paypal_encrypt','input_text_option');
		scabn_Admin::custom_add_settings_field('paypal_cert_id', 'Certificate ID (see paypal\'s website): ', 'paypal_en', 'paypal_encrypt','input_text_option');
   
		add_settings_section('google_options', 'Google Wallet Settings:', array($this,'section_text'), 'google');   
   	scabn_Admin::custom_add_settings_field('gc_merchantid', 'Google Merchant ID: ', 'google', 'google_options','input_text_option');
   	scabn_Admin::custom_add_settings_field('gc_merchantkey', 'Google Merchant Key (optional, but required for encrypted carts): ', 'google', 'google_options','input_text_option');
   
		add_settings_section('google_analytics', 'Google Analytics Settings (Optional):', array($this,'section_text'), 'google_ac');   
		scabn_Admin::custom_add_settings_field('analytics_id', 'Google Analytics ID (UA-XXXXX-X): ', 'google_ac', 'google_analytics','input_text_option');   
   }
	

	//Validates that the input for various fields meets certain criteria.
	function options_validate($input) {
		if (! is_email($input['paypal_email'])) $input['paypal_email'] = 'Invalid Email Entered';				
		$input['cart_url'] = esc_url($input['cart_url'],array('http','https'));
		$input['cart_cancel_url'] = esc_url($input['cart_cancel_url'],array('http','https'));
		if ( $input['gc_merchantid'] != "" ) 	$input['gc_merchantid']=substr($input['gc_merchantid'],0,32);
		if ( $input['gc_merchantkey'] != "" ) 	$input['gc_merchantkey']=substr($input['gc_merchantkey'],0,32);
		if ( $input['analytics_id'] != "" ) 	$input['analytics_id']=substr($input['analytics_id'],0,12);
		if ( $input['currency'] != "" ) 	$input['currency']=substr($input['currency'],0,4);
		if ( $input['template'] != "" ) 	$input['template']=substr($input['template'],0,16);
		if ( $input['paypal_pdt_token'] != "" ) 	$input['paypal_pdt_token']=substr($input['paypal_pdt_token'],0,24);
		if ( $input['paypal_cert_id'] != "" ) 	$input['paypal_cert_id']=substr($input['paypal_cert_id'],0,16);
		return $input;
	}

	
	//Below are functions used in options_init to generate html for forms to set SCABN configuration settings. 
	//Most are general purpose to the input type (radio, text, etc), a few are custom.
	
	//Return array with list of Paypal URL
	//Options formatted nicely.
	function display_paypal_url_options() {
		$out=array();
		foreach(scabn_Backend::paypal_urls() as $key=>$url) {
				$out[$key]="<strong>".$key."</strong> (".$url.")";												
		}
		return $out;
	}
			

	//Set text, if any, to display after new section.
	function section_text($arg) {				
		if (is_array($arg))	echo $arg[0];
	}
	
	//Generate html for a normal text input option
	function input_text_option($arg) {				
		$options = get_option('scabn_options');
		echo "<input id='" . $arg['id'] . "' name='scabn_options[" . $arg['id'] . "]' size='40' type='text' value='" . $options[$arg['id']] . "'/>";	

	}
	
	//Wrapper for add_settings_field 
	function custom_add_settings_field($id,$desc,$section_id,$section_name,$callfunc,$extratext="") {
		add_settings_field($id,$desc,array($this, $callfunc), $section_id,$section_name, array("id"=>$id, "desc"=>$desc,"extra"=>$extratext));									
	}	
	
	//Generate html for input radio selection.
	function input_radio($arg) {
		$options=get_option('scabn_options');				
		//echo var_dump($arg);		
		//echo "test";
   	foreach ($arg['extra'] as $key=>$value) { 						
			echo "<p><input type='radio' " .checked($options[$arg['id']],$key, False). " name='scabn_options[" . $arg['id'] . "]' value='" . $key . "' />". $value. "</p>";
			} 
	}

	//Generate html for input selection
	function input_selection($arg) {
		$options=get_option('scabn_options');				
		echo "<select id='". $arg['id'] ."' name='scabn_options[" . $arg['id'] . "]'>";
   	foreach ($arg['extra'] as $key=>$value){ 
			echo "<option value='$key'". selected($options[$arg['id']],$key) . ">". $value. "</option>";
			}
		echo "</select>";  
	}


	//Tweak of input_selection function to have each item in array be an
	//array with two elements, but only display the second item in the array.	
	function input_selection_custom1($arg) {
		$options=get_option('scabn_options');				
		echo "<select id='". $arg['id'] ."' name='scabn_options[" . $arg['id'] . "]'>";
   	foreach ($arg['extra'] as $key=>$value){ 
			echo "<option value='$key'". selected($options[$arg['id']],$key) . ">". $value[1]. "</option>";
			}
		echo "</select>";  
	}

 		
	//Generate the html for the admin page.		
	function admin_page() {
		echo "<div><h2>SCABN Configuration Options</h2>";
		echo "Options relating to the Custom Plugin.";
		echo "<form action='options.php' method='post'>";
		settings_fields('scabn_options');
		do_settings_sections('general'); 
		do_settings_sections('paypal');
 		do_settings_sections('paypal_op'); 
 		do_settings_sections('paypal_en');
 		do_settings_sections('google');
 		do_settings_sections('google_ac');   
		echo "<br/><input name='Submit' type='submit' value='";
		esc_attr_e('Save Changes');
		echo  "'/>";
		echo "</form></div>";
		echo "<br /><p><a href='http://wordpress.org/extend/plugins/simple-cart-buy-now/' target='_blank'>Visit the plugin page for more information.</a></p>";
		
	}
}


?>
