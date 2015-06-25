<?php
/*
Plugin Name: WP e-Commerce Bring Fraktguide
Plugin URI: http://wordpress.org/extend/plugins/wp-e-commerce-bring-fraktguide/
Description: Bring Fraktguide Shipping Module for WP e-Commerce. Uses XML API at http://fraktguide.bring.no/fraktguide/xmlOverHttp.do
Version: 0.8
Author: Leif-Arne Helland
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'BFG_VERSION', '0.8' );

require_once( dirname( __FILE__ ) . '/shipping.class.php' );
require_once( dirname( __FILE__ ) . '/tracking.class.php' );

/** 
 * Load JavaScripts
 * The script will update shippingcity and shippingpostcode and set both fields to read-only
 */
function bring_fraktguide_script() {
	wp_enqueue_script('bfg-script', plugins_url('js/bfg.js', __FILE__), array('jquery'), BFG_VERSION, true);
}

/** 
 * Add shipping module
 */
function bring_fraktguide_add($wpsc_shipping_modules) {
	global $bring_fraktguide;
	$bring_fraktguide = new bring_fraktguide();
	$wpsc_shipping_modules[$bring_fraktguide->getInternalName()] = $bring_fraktguide;
	return $wpsc_shipping_modules;
}

register_deactivation_hook( __FILE__, array('bring_fraktguide', 'bring_fraktguide_deactivate') );
register_activation_hook( __FILE__, array('bring_fraktguide', 'bring_fraktguide_install') );
load_plugin_textdomain('bfg', false, dirname( plugin_basename(__FILE__) ) . '/lang');
add_filter('wpsc_shipping_modules', 'bring_fraktguide_add');
add_action('template_redirect', 'bring_fraktguide_script');
add_action( 'wpsc_user_log_after_order_status', 'BFG_Tracking::bfg_display_tracking_link' );
?>