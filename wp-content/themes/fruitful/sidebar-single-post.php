<?php
/**
 * The Single Post Page Sidebar widget areas.
 *
 * @package WordPress
 * @subpackage Fruitful theme
 * @since Fruitful theme 1.2
 */
?>
	<div id="secondary" class="widget-area" role="complementary">
		<?php do_action( 'before_sidebar' ); ?>
		<?php if ( ! dynamic_sidebar( 'sidebar-3' ) ) : ?>

		<?php endif; // end sidebar widget area ?>
	</div><!-- #secondary .widget-area -->
