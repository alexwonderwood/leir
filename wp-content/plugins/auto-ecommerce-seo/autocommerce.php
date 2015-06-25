<?php
/**
 * @package Auto_eCommerce_SEO
 * @version 1.0
 */
/*
Plugin Name: Auto eCommerce SEO
Plugin URI: http://www.lazy-seo-plugin.com/
Description: The Auto eCommerce SEO plugin with automatically optimize product pages on an eCommerce site for search engines using onsite innovative SEO techniques.
Author: Daniel Morris
Author URI: http://www.linkedin.com/in/danielryanmorris
Version: 1.0

    Copyright 2014  Daniel Morris  (email : danielryanmorris@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/* Filters */
//Adds title tags to site - 21 priority to ensure last run
add_filter( 'wp_title', 'auto_commerce_set_title', 21 );

//Adds settings option to installed plugin page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'auto_commerce_add_plugin_action_links' );


/* Actions */
//adds meta description to <head>
add_action( 'wp_head', 'auto_commerce_meta_desc' );

//removes meta description  from <head>
remove_action('wp_head', 'description');


/* Custom Functions */
//Adds item to front of array, removes duplicates
function auto_commerce_unshift( $array, $first) {
  
  //checks to make sure that the new string is valid  
  if( strlen($first) > 0 ) {  
    //adds priority kw to beginning
    array_unshift($array, $first);
    
    //makes sure that titles are unique
    return array_values(array_unique($array));
  }
}

//Defines and sets the title tags based for the add_filter call
function auto_commerce_set_title ( $title ) {
    //checks to see if wooCommerce is installed
    if ( function_exists( 'is_product' ) && function_exists ('get_terms') ) {  
      if( is_product() ){   
        //Gets product categories
        $titles = array();
        $args = array(
        'number'     => $number,
        'orderby'    => $orderby,
        'order'      => $order,
        'hide_empty' => $hide_empty,
        'include'    => $ids,
         'parent'    => 0
        );
        $product_categories = get_terms( 'product_cat', $args );
        foreach( $product_categories as $cat ) {
          $titles[] = $cat->name;
        }
        
        //Gets product name       
        $firstkw = get_the_title();
                
      } else {
        return $title;
      }
    } else {
      return $title;
    }
    
    shuffle($titles);
    
    //sets first keyword combo
    if(strlen($firstkw) > 0 ) {
      $first = $firstkw;
    } else {
      $first = "";
    } 
    
    //adds first keyword combo to front of array, removes duplicates
    $titles = auto_commerce_unshift( $titles, $first);
    
  if(count($titles) > 0 ){
    //Returns the first three keywords separated by |
    return join( " | ", array_slice($titles,0,min(3, count($titles) ) ) ); 
  } else {    
    return $title;
  }   
}     

//generates the meta description from the_exerpt
function auto_commerce_meta_desc() {
  
  if ( function_exists( 'is_product' ) ) {  
      if( is_product() ){               
        //adds meta description to <head>
        echo "<meta name='description' content='".esc_attr( get_the_excerpt() )."' />";      
    }
  }
}

//Adds Donate link in plugin menu
function auto_commerce_add_plugin_action_links( $links ) {
   
  return array_merge( array( 'settings' => '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DMMPW2R2XCXHJ">Donate</a>'),$links);
   
}