=== Simple Cart & Buy Now === 
Contributors: bluey80  
Tags: shopping cart, e-commerce, buy now, buynow, Paypal, Google Checkout, Google Wallet, encrypted carts, checkout, shopping cart widget, ajax, Google analytics, analytics, custom carts, custom shopping carts
Requires at least: 3.2 
Tested up to: 3.5.1
Stable tag: 1.4.8

Simple shopping cart system provides buy now buttons to purchase items via Paypal or Google Wallet. Also links to Google Analytics to tracks sales. 

== Description ==

Add "Add to Cart" buttons to your pages / posts with sidebar widget showing items currently in customer's shopping cart. When customer clicks on 'Go to Checkout' 
then can purchase items via Paypal or Google Wallet. Simple, secure shopping cart system stores no user information (email, address, credit card, etc) but uses
encrypted buy now buttons to pass customer over to Paypal / Google for providing purchasing information. 

Features:

*   Easily add "Add to Cart" buttons to pages / posts
*   Secure, encrypted Buy Now buttons for Paypal and Google Wallet
*   You can include options to your products, with different prices if necessary
*   Easy to customize, including custom functions to get pricing (e.g. volume discounts), shipping options, etc
*   Optionally adds Google Analyics tracking code and links Paypal & Google Wallet Purchases with Google Analytics Ecommerce


== Installation ==

1. Upload the entire `scabn` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

You will find 'SCABN Settings' menu in your WordPress admin panel and the icon in your post editor panel.

== Frequently Asked Questions ==

= I just installed the plugin. Now what? = 

1. Create a new page. Title something like 'Checkout'. In the page edit toolbar, click on the icon of a shopping cart with an 'S' (Add SCABN Item or Checkout). Check "Make this page my Checkout Page". Save the page and note its url.
2. Go to Appearance / Widgets and add the 'SCABN Checkout Cart' widget somewhere on your side. This is a mini shopping cart that will be displayed on all your pages.
3. Goto Plugins / SCABN Settings. Under 'Checkout/Process Page URL' put the url from step 1. Fill out other information as desired.
4. Edit a page where you want an 'add to cart' item. Click on the icon of a shopping cart with an 'S' as in step one, but this time fill out the item name, cost, etc.
5. Done! View your page. When you click on the add to cart button, it should show up in the mini shopping cart widget. That widget will have a link to your checkout page. Your checkout page will then show you the full shopping cart and provide buy now buttons for Paypal and Google Wallet.

= How do I select shipping options? = 

Unfortunately, Paypal and Google Wallet handle this differently. The shipping cost will depend on the shipping address (international, domestic, etc) and we don't ask for a customer's address -- Paypal or Google Wallet does. 
With Google Wallet, we provide different shipping options for different locations. Such as:

1. Domestic Ground ($5)
2. Domestic Express ($10)
3. International Ground ($10)
4. International Express ($20)

Then Google Wallet lets the customer pick from any of the shipping options valid for their address. This means you can write your own function to determine shipping options via location, quantity, items, item weight, etc. Take a look at the getShippingOptions function in templates/default/customize.php and copy it 
to templates/SOMETHING/customize.php and edit it to your needs. Then in SCABN settings, select SOMETHING as your template. 

As for Paypal, it doesn't support this. Instead log in to your Paypal account and goto Profile, More Options, My Selling Tools, Shipping Calculations and 
you can define different shipping options for different locations and weight OR price of the order.


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

= 1.2.9 = 
  * Added feature: Google Analytics tracking with Ecommerce Support
  * Fixed bug with removing items from cart causing new items added to be ignored
  * Fixed bug with having items with options where pricing is different for each option. 

= 1.3.0 =
  * Same as 1.2.9, but I'm marking this as stable after further testing of 1.2.9

= 1.3.1 =
  * Fixed bug where some users got 'Undefined index: item_options' error

= 1.3.2 =
  * Added ability to set weight of an item in short code.

= 1.3.3 =
  * Empty cart when Paypal directs you to a receipt page. (IPN)

= 1.4.0 =
  * Attempt to remove echos from code and use function returns as should be done with shotcodes. Not heavily tested

= 1.4.1 =
 * More testing and it works fine, so tagging as stable. Also tested against Wordpress 3.4.2.

= 1.4.2 =
  * Fixed bug where Google Analytics Tracking Code was not getting onto website.

= 1.4.3 =
  * Fixed bug where ":" was displayed for item options name even when name was blank.

= 1.4.4 =
  * Title set on SCABN's widget (hardcoded to 'Simple Checkout')

= 1.4.5 = 
  * Changed loading of tiny_mce_popup to work with WordPress 3.5

= 1.4.6 =
  * Fixed missing ">" typo in Paypal checkout button when using unencrypted buttons.

= 1.4.7 =
  * Undid 1.4.6 as that didn't fix the problem.

= 1.4.8 =
  * Changed return of html content in scabn_sc to echo as it seems on some these that return gets sanitized to html, which is bad.

= 1.9.1 =
  * Major rewrite for SCABN 2.0. Uses classes, removes dead code from WP Checkout. Better customization / template system.
  * Because of major rewrite to template system, please backup your template before upgrading and you will need to port your customization to new system.
