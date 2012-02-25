=== Simple Cart & Buy Now ===
Contributors: bluey80
Donate link: http://
Tags: shopping cart, e-commerce, buy now, buynow, google checkout, encrypted carts,checkout, products, selling, sell, paypal, jquery, shopping cart widget, ajax, widget
Requires at least: 3.2
Tested up to: 3.3.1
Stable tag: 1.2.2

Simple shopping cart system provides buy now buttons
to purchase items via Paypal or Google Checkout. Base on Wordpress Checkout

== Description ==

Easy to use and customize this is a shopping cart that uses your posts or pages as products. With an editor button you can create products on the fly.

Features:

*   Easily add "Add to Cart" buttons to pages / posts
*   Secure, encrypted Buy Now buttons for Paypal and Google Checkout	
*   You can include options to your products
*   Easy to customize, including custom functions to get pricing, shipping options, etc



== Installation ==

1. Upload the entire `scabn` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'SCABN Settings' menu in your WordPress admin panel and the icon in your post editor panel.


== Screenshots ==

1. Wordpress site using "Add to Cart" plugin on main page and a plugin on right sidebar showing current contents of the shopping cart.
2. Wordpress site of the final checkout page.

== Changelog ==  
   
= 0.0.1 =  
 * Initial Upload

= 1.1.0 =
 * I've been using it enough to think it generally working.
 * Fixed javascript problem when using gui to add [scabn] text to page -- used to be [wp-checkout] from original.

= 1.1.1 =
 * Fixed error when using currency other than USD -- USD hard-coded in two places, now uses $currency option.

= 1.1.2 =
 * Options for products can have different pricing
 * Can update quantities in sidebar mini-shopping cart
 * Custom Cart page non option in gui page editor widget
 * Minor aesthetic tweaks

= 1.2.0 =
 * Now supports Paypal PDT for return url, including processing of txn to display Name, purchase amount, on return after purchase
 * Weight field properly passed onto Paypal for calculating shipping costs. (Note if using your own template, please update customize.php's getitemweight function as if return left null (as before) paypal breaks. 

= 1.2.1 = 
  * Fixed bad url for plugin page url

= 1.2.2 =
  * Shopping cart sent to google or paypal did not include item's options. Now adds items' options information to the cart.
  * Cleaned up some css on placement of add to cart button. 
