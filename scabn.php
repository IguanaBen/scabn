<?php
/*
Plugin Name: Simple Cart & Buy Now
Plugin URI: http://wordpress.org/extend/plugins/simple-cart-buy-now/
Description: Simple Cart and BuyNow for Wordpress
Version: 1.9.1
Author: Ben Luey
Author URI: http://iguanaworks.net
*/

/*
This plugin was once Wordpress-Checkout. Thanks to Alain Gonzalez for
that plugin -- I've forked it to remove features / complexity I don't
need and to add support for Google Wallet, signed carts, etc and support
newer versions of Wordpress as Wordpress-Checkout is no longer maintained.

Simple Cart & Buy Now (SCABN) is designed to implement a basic shopping
cart system and checkout system for wordpress e-commerce websites with two
principal goals:

1) No storing of user information
2) Security: encrypted 'buynow' buttons and pricing information not obtained
   from data provided by the user's browser.

Paypal BuyNow & Google Wallet both supported with encrypted BuyNow buttons.

Also has hooks support for giving customers custom shopping carts via url or uuid and 
tracking visitors and customers with Google Analytics.

Template system for customize look of the shopping cart
and to use optional db backend or other phph functions to get 
pricing, shipping, weight information, bulk discounts, etc

*/

/*
    Copyright (C) 2010 Alain Gonzalez (support@web-argument.com)
    Copyright (C) 2011 Ben Luey (bluey@iguanaworks.net)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'SCABN_PLUGIN_DIR' ) ) 	define( 'SCABN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
if ( ! defined( 'SCABN_PLUGIN_URL' ) )  define( 'SCABN_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) );

require_once SCABN_PLUGIN_DIR. '/includes/cart.php';
require_once SCABN_PLUGIN_DIR. '/includes/functions.php';
require_once SCABN_PLUGIN_DIR. '/includes/commun.php';
require_once SCABN_PLUGIN_DIR. '/includes/scabn_codes.php';
require_once SCABN_PLUGIN_DIR. '/admin/scabn_admin.php';
require_once SCABN_PLUGIN_DIR. '/backend.php';
require_once SCABN_PLUGIN_DIR. '/display.php';



if (file_exists(SCABN_PLUGIN_DIR. '/templates/'.$scabn_options['cart_theme'].'/customize.php') ) {
        require_once SCABN_PLUGIN_DIR. '/templates/'.$scabn_options['cart_theme'].'/customize.php';
} else {
        require_once SCABN_PLUGIN_DIR. '/templates/default/customize.php';
}






//Add hook to run SCABN -- just starts session
//for getting cart info
add_action('init','scabn_Backend::scabn_init');

$scabn_options = get_option('scabn_options');



if ( $scabn_options['analytics_id'] != '' ) {
	add_action('wp_head', 'scabn_googleanalytics');
}

add_action('wp_head', 'scabn_head');

add_shortcode('scabn', 'scabn_sc');
add_shortcode('scabn_customcart', 'scabn_customcart');


//Add SCABN Settings page to Admin view
//And button for adding SCABN options to
//pages &
scabn_Admin::init();
scabn_Backend::init();
scabn_Display::init();


?>
