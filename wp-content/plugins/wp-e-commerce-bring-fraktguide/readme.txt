=== Plugin Name ===
Contributors: Leif-Arne Helland
Tags: wp e-commerce, e-commerce, wpec, wpsc, shipping, bring fraktguide, bring, frakt, fraktguide, XML API
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 0.8

Bring Fraktguide Shipping Module for WP e-Commerce.

== Description ==

This is a shipping module for WP e-Commerce. It uses the [Bring Fraktguide XML API](http://fraktguide.bring.no/fraktguide/xmlOverHttp.do) to get shipping quotes.

It will get accurate prices based on delivery postcode, weight, length, width, height and volume.

The plugin only supports WP e-Commerce 3.8 and later.

== Installation ==

1. Install and activate [WP e-Commerce](http://wordpress.org/extend/plugins/wp-e-commerce/).
2. Install and activate [WP e-Commerce Bring Fraktguide](http://wordpress.org/extend/plugins/wp-e-commerce-bring-fraktguide/).
3. Log on to WordPress Dashboard.
4. Go to Settings - Store - General.
5. Select Norway as Base Country/Region and check Norway in Target Markets.
6. Go to Settings - Store - Shipping.
7. Update Base Zipcode/Postcode to the postcode from where you will be shipping.
8. Check Bring Shipping Guide under External Shipping Calculators and click Update.
9. Click Edit next to Bring Shipping Guide.
10. Configure Bring Shipping Guide and click Update.
11. Add weight, height, width and length to all products.

== Screenshots ==

1. Screenshot Admin Area 
2. Screenshot Calculate Shipping

== Changelog ==

= 0.8 =
* Added tracking information to purchase log
* Fixed compatibility with WP e-Commerce 3.8.9 and later

= 0.7 =
* Check for existing options before adding options on activation
* Added more inline documentation

= 0.6 =
* Fixed: Unable to add variations to cart
* Removed unnecessary code

= 0.5 =
* Added option to enable handling fee

= 0.4 =
* Set shipping postcode and city to readonly after shipping has been calculated

= 0.3 =
* Fixed issues with saving options

= 0.2 =
* Get price for entire cart as single package
* Get one price per item and sum up total
* The above mentioned options were present in 0.1, but did nothing.
* Calculate volume

= 0.1 =
* Initial Release
