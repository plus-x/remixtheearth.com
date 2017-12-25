<?php
/*
 Plugin Name: WP BandCamp
 Plugin URI: http://longjohndesign.blogspot.com/2011/01/wp-bandcamp-wordpress-bandcamp-plugin.html
 Description: Embed the Bandcamp Player into your website pages using a widget or the post editor shortcode.
 Version: 1.1.1
 Author: LongJohn
 Author URI: http://longjohndesign.blogspot.com/
 */

define('WP_BANDCAMP_PLUGIN_PATH', dirname(__FILE__) . '/');

require_once ( WP_BANDCAMP_PLUGIN_PATH . 'components/general.php' );
require_once ( WP_BANDCAMP_PLUGIN_PATH . 'components/widget.php' );

if (!is_admin())
{
	require_once ( WP_BANDCAMP_PLUGIN_PATH . 'components/shortcode.php' );
}

if (is_admin() and is_post_editor())
{
	require_once ( WP_BANDCAMP_PLUGIN_PATH . 'components/mce_button.php' );
}


