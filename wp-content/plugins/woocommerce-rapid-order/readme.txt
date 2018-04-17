=== Rapid Order for WooCommerce ===
Contributors: greatwitenorth
Tags: woocommerce, order system, eCommerce
Requires at least: 4.0
Tested up to: 4.5.3
Stable tag: 2.2
License: GNU
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Turn your WooCommerce store into a fast ordering system

== Description ==
Rapid Order for WooCommerce makes shopping fast and easy for your customers. It turns your WooCommerce store into a fast wholesale ordering system. Infinite scroll, live search and rapid add-to-cart are just some of the features your customers will love.

== Installation ==
1. Upload the folder \'woocommerce-rapid-order\' to the \'/wp-content/plugins/\' directory
2. Activate \'Rapid Order for WooCommerce\' through the \'Plugins\' menu in WordPress

== Changelog ==
= 2.2 = Sept 24 2017
* Full compatibility with WooCommerce v3.1. Remove uses of deprecated functions.
* Hide out of stock items on shortcode page if option checked in WC admin

= 2.1.7 = Sept 4 2017
* Fixed issue where variable products were not showing correct photo in certain situations.

= 2.1.5 = July 31, 2017
* Fixed issue where previously ordered items were showing all items on next page

= 2.1.4 = July 17, 2017
* Fixed issue when user ordered no products and previously_ordered shortcode showed all item. 

= 2.1.3 = May 23, 2017
* Fixed bug where pressing enter in quantity didn't update cart for search results

= 2.1.2 = May 16, 2017
* Fixed bug where pressing enter in quantity box didn't update the cart

= 2.1.1 = April 7, 2017
* Fixed issue where variation quantity was not showing on refresh

= 2.1 = January 18, 2017
* Added filtering by tags for shortcode

= 2.0.6 = January 15, 2017
* removed html from post excerpt
* added spaces for variable products with multiple attributes

= 2.0.5 = November 29, 2016
* css styling fixes
* Only update on input 'change' to prevent number validation issues with Q&U

= 2.0.4 = August 16, 2016
* Fixed additional tax bug
* Fixed Quantity and Unit integration where price suffix disappeared

= 2.0.3 = August 16, 2016
* Fixed issue were prices were not taking tax preferences into account when displaying.
* Fixed an issue where Woosidebars was causing infinite scroll to not work on shortcode pages.

= 2.0.2 = August 3, 2016
* Fixed issue where variation names were sometimes showing the slug instead of name. 

= 2.0.1 = July 29, 2016
* Fixed and issue where shortcodes were displaying out of stock items when they shouldn't

= 2.0 = July 14 2016
* Shortcode Support: Added the ability to only show Rapid Order form on certain pages via shortcode
* Ability to turn off Rapid Order overriding shop, archive, and search result pages
* fixed situation where other plugin's ajax calls could be overidden by Rapid Order

= 1.3 = June 1, 2016
* Added ability to remove thumbnail images
* properly apply woocommerce_short_description filter to product description

= 1.2.3 = May 18, 2016
* fixed pagination issue

= 1.2.2 = May 17, 2016
* fixed issue where variation attributes showing slug name
* fixed mobile styling issue. added sticky review cart on mobile footer

= 1.2.1 = March 24, 2016
* fixed activation bug

= 1.2 =
* New feature: Allowed image to link to nothing, product page, or larger lightbox image
* fixed display of subtotal and cart button on mobile.
* fixed out of stock items still showing quantity input

= 1.1.2 =
* fixed sub total not showing

= 1.1.1 =
* updated ElementQueries.js and ResizeSensor.js to fix IE loading issues
* fixed IE infinite scroll issues

= 1.1 =
* Provided better support for Dynamic pricing style plugins (specifically 'WooCommerce Dynamic Pricing' and 'WooCommerce Dynamic Pricing & Discounts')
* fixed issue for currencies that don't have 2 decimals
* fixed issue where clicking '-' on an item with quantity 0 added 1 to cart
* fixed issue where extra checkbox not staying checked on reload

= 1.0.10 = 
* fixed issue where rapid order was displaying in related products

= 1.0.9 = 
* Added ability to specify a pre-existing fixed header so that it won't interfere with the table sticky header.

= 1.0.8 =
* changed uninstall to static method 

= 1.0.7 = 
* fix for get_editable_roles error

= 1.0.6 = 
* added Svenska language support provided by Jonas Andersson
* Improved currency symbol and decimal support for line item totals

= 1.0.5 =
* fixed language file to include all strings.
* fixed mobile css issue with sticky header turned off.
* fixed issue where plugin was saying it had an update even though it didn't

= 1.0.4 = 
* increment button bug fix

= 1.0.3 =
* Better support for Dynamic Pricing and Discounts plugin
* prevent list output on 404 page

= 1.0.2 =
* Added the ability to display wholesale form to guests and/or specific roles.