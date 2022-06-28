=== Custom Products for WooCommerce ===
Contributors: grrega
Donate link: https://grrega.com/projects/custom-products-for-woocommerce
Tags: woocommerce, custom, product, component, e-commerce, ecommerce, sales, sell, store
Requires at least: 4.7.0
Tested up to: 5.2.2
WC tested up to: 3.6.5
Requires PHP: 5.4
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Offer your visitors a unique shopping experience. Split products into components and let your visitors customize each one before purchasing.

== Description ==

<h4>Custom Products for WooCommerce</h4>
<p><em>Offer your visitors a unique shopping experience.<br />
</em></p>
<p>This plugin gives you the ability to setup a store where your visitors can customize their products before buying them.</p>
<p>Custom Products for WooCommerce is an extension for WooCommerce that adds a new level to the product-attribute hirearchy. Instead of assigning attributes to products, split products into multiple components and assign attributes to components.</p>
<p>It was made to be as universal as possible so you can customize pretty much every type of product. See the <a href="https://woocp-demo.grrega.com/">demo</a> to get an idea of what this plugin is capable of.</p>

<h4>Some (but not all) features</h4>
<ul>
	<li>Assign components to products</li>
	<li>Add product specific component names</li>
	<li>Add product specific component descriptions</li>
	<li>Assign attributes to components</li>
	<li>Order components</li>
	<li>Order attributes</li>
	<li>Assign an image to product customizer or use products' featured image</li>
	<li>Animated components</li>
	<li>Shortcode - customizer with filterable product selector</li>
	<li>Shortcode - single product customizer</li>
	<li>"Customize" button on product page for products that can be customized</li>
	<li>Add images to WooCommerce attributes</li>
	<li>Images on product and attribute select dropdowns</li>
	<li>Add to cart with ajax</li>
	<li>Show what was customized on cart and checkout pages and invoice</li>
	<li>Template system</li>
	<li>Translation ready</li>
	<li>Seamlessly integrated into WooCommerce</li>
	<li>Works with Polylang</li>
	<li>Works with WooSwatches</li>
</ul>
<blockquote>
<h4>Become a Premium user</h4>
<p>Custom Products for WooCommerce is also available in Premium version that adds a lot of features.</p>
<br />
<b>Premium features</b>
<ul>
	<li>Shortcode - components list w/wo add to cart button</li>
	<li>Shortcode - customizer image</li>
	<li>Shortcode - add to cart button w/wo quantity field</li>
	<li>Shortcode - customizer button</li>
	<li>Variable products integration</li>
	<li>Use only default variation or all available variations</li>
	<li>Show variation (or featured) image if customizer image isn't found</li>
	<li>Charge fees for customizing products
<ul>
	<li>Per order</li>
	<li>Per product</li>
	<li>Per component</li>
</ul>
</li>
	<li>Edit text tag advanced settings (<b>bold</b>,<em>italic</em>,...)</li>
	<li>Add text tag shadow</li>
	<li>10 icon tags to choose from</li>
	<li>Add a "Customize" button to cart page</li>
</ul>
<p><a href="https://grrega.com/projects/custom-products-for-woocommerce" rel="nofollow">View all available features</a></p>
</blockquote>

<h4>Shortcode</h4>
<code>[woocp product_ids="1,2,3" number_of_products="3" width="100%" order="ASC" orderby="title" class="classname" hide_msg_area="true" hide_select="true"]</code>
<p>You can use the shortcode to get product customizer anywhere on your site.
Shortcode accepts the following parameters:</p>
<ul>
<li>product_ids - a comma separated list of product IDs</li>
<li>category_ids - a comma separated list of category IDs</li>
<li>number_of_products - limit of produts to show</li>
<li>width - product customizer width (px, em, %, ...)</li>
<li>image_width - customizer image width (%)</li>
<li>list_width - components list width (%)</li>
<li>class - classname(s) for CSS/JS purposes</li>
<li>order - order products (ASC, DESC)</li>
<li>orderby - order products by (none, ID, title, name, date, modified, rand)</li>
<li>hide_msg_area - hide/show WooCommerce loader and message section under the add to cart button (default: false)</li>
<li>hide_select - hide/show product select dropdown (default: false)</li>
</ul>
<p><b>Note:</b> You can only use category_ids OR products_ids in the shortcode.</p>

<h4>Support</h4>
<p>Contact me by <a href="https://grrega.com/contact">Grrega.com contact form</a> or find me on social media if you are in need of support.</p>
<p>Please take a look at the <a href="https://grrega.com/documentation/custom-products-for-woocommerce-docs">Custom Products for WooCommerce documentation</a> as well.</p>
<h4>Translations</h4>
<p>The plugin is translated using Transifex. If you want to help out, please head over to the <a href="https://www.transifex.com/grregacom/custom-products-for-woocommerce/" rel="nofollow">translation project on Transifex</a>.</p>

== Installation ==

SERVER REQUIREMENTS

1. PHP version 5.4 or greater (PHP 7.2 or greater is recommended)
2. MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)


AUTOMATIC INSTALLATION

1. Log in to your WordPress dashboard, navigate to the Plugins menu and click Add New
2. In the search field type "Custom Products for WooCommerce" and click Search Plugins
3. Install the plugin by simply clicking "Install Now"

MANUAL INSTALLATION

The manual installation method involves downloading the Custom Products for WooCommerce plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains instructions on how to do this <a href="https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation">here</a>.

== FAQ ==

= How to override template files =

Template files are stored at the "/custom-products-for-woocommerce/templates" folder.

1. Create a subfolder "/custom-products-for-woocommerce" in your child themes folder.
2. Copy the template that you want to override from the plugins "/templates" folder to the new folder in your child theme
3. Edit the template

= Why do I get redirected to the homepage after clicking the "Customize" button on product page? =

You need to set "Customizer URL" field in the <strong>Customizer settings</strong> section of the <strong>Custom Products tab</strong> on the <strong>WooCommerce settings</strong> page.
Enter an URL of a page that has the <strong>[woocp]</strong> shortcode somewhere in the content.
If you do not set the "Customizer URL" field the plugin will assume your <strong>[woocp]</strong> shortcode is on your homepage.

== Screenshots ==

1. Settings tab on WooCommerce settings page
2. Manage components page
3. Manage terms page
4. Edit product page - components
5. Edit product page - image
6. Shortcode - product selector
7. Shortcode - open component
8. Cart page
9. Email invoice
10. Edit order page

== Changelog ==

= 1.1.1 =
* FIX
  - get_posts not returning all customizable products

= 1.1.0 =
* NOTE
  - Clear you cache
  - Check and update your custom CSS if necessary
  - Check and update you custom templates
* ADD
  - [woocp_single_product] shortcode
  - [woocp_components_list] shortcode (PREMIUM)
  - [woocp_customizer_image] shortcode (PREMIUM)
  - [woocp_add_to_cart] shortcode (PREMIUM)
  - [woocp_customize_button] shortcode (PREMIUM)
  - hide_select="true/false" parameter to [woocp] shortcode
  - msg_area="true/false" parameter to [woocp] shortcode
  - class="CLASSNAME(s)" parameter to [woocp] shortcode
* FIX
  - deprecated functions
  - fix some PHP warnings/notices
  - component CSS fixed
  - component & attribute labels
* MODIFY
  - lose wp_query, use get_posts instead (not working on some sites)
  - when changing variation on single product tab only image is changed (user selection of attributes is not reset anymore)
  - JS targeting was updated, so you can have multiple shortcodes on one page (check shortcode docs for more info) and each add to cart button will work separately
  - show default variation in [woocp] shortcode if product ID (instead of variation ID) of a variable product is passed (from the "customize" button). You can override this behaviour by inserting custom [woocp_customize_button id="VARIATION_ID"] shorcodes for each variation.

= 1.0.2 =
* FIX: woocp-public.js forced to load in footer for better theme compatibility

= 1.0.1 =
* FIX: jQuery tipTip not refreshing after ajax calls in admin
* FIX: Added missing strings to translation files
* FIX: Font selection fix