<?php
/*
   Plugin Name: CodeGuard Website Backups
   Plugin URI: https://codeguard.com/wordpress
   Author: The CodeGuard Team
   Description: Get a time machine for your website!  CodeGuard will monitor your site for changes.  When a change is detected, we will alert you and take a new backup of your database and site content.
   Version: 0.66
   Requires at least: 3.0
   Tested up to: 4.8
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

error_reporting(0);
@ignore_user_abort(true);
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', 0);
@ini_set('upload_max_filesize', '256M');
@ini_set('post_max_size', '256M');
@ini_set('max_input_time', 0);
@set_time_limit(0);
ini_set("default_socket_timeout", 0);


/**
 * Show notice for outdated PHP versions
 */
if ( ! function_exists( 'cg_php_version_notice' )) {
	function cg_php_version_notice() {
	  global $pagenow;
	  if ( $pagenow == 'plugins.php' ) {
		echo '<div class="notice-warning notice"><p>';
		echo ''.__( "CodeGuard is optimized to work with PHP 5.2 and above. You are currently using version","codeguard").' '.PHP_VERSION.'. '.__( "If you experience any issues, please update PHP or connect to CodeGuard using <a href='https://www.codeguard.com/how-it-works' target='_blank'>FTP/SFTP and MySQL</a>.","codeguard").'';
		echo '</p></div>';
	  }
	}
}

if (version_compare(PHP_VERSION, '5.2.0', '<')) {
  add_action('admin_notices', 'cg_php_version_notice');
}

/**
* Add plugin settings link to plugins page
 */
if( ! function_exists("cg_plugin_action_links") ){
	add_filter('plugin_action_links', 'cg_plugin_action_links', 10, 2);
	function cg_plugin_action_links($links, $file) {
		static $this_plugin;

		if (!$this_plugin) {
			$this_plugin = plugin_basename(__FILE__);
		}

		if ($file == $this_plugin) {
			$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=amazon-s3-backup">Settings</a>';
			array_unshift($links, $settings_link );
		}

		return $links;
	}
}

/**
* Add plugin settings link to WP Toolbar
 */
function cg_toolbar_link($cg_wp_admin_bar) {
	$args = array(
		'id' => 'codeguard',
		'title' => 'CodeGuard',
		'href' => '' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=amazon-s3-backup',
		'meta' => array(
			'class' => 'codeguard',
			'title' => 'CodeGuard'
			)
	);
	$cg_wp_admin_bar->add_node($args);

	$args = array(
		'id' => 'codeguard-support',
		'title' => 'CodeGuard Support',
		'href' => '' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=codeguard-support',
		'parent' => 'codeguard',
		'meta' => array(
			'class' => 'codeguard-support',
			'title' => 'CodeGuard Support'
			)
	);
	$cg_wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'cg_toolbar_link', 999);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . "main.php";
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'libs' . DIRECTORY_SEPARATOR . 'rest-api' . DIRECTORY_SEPARATOR . 'endpoints' . DIRECTORY_SEPARATOR . 'class-cg-rest-controller.php';

main::setPluginDir(dirname(__FILE__));
main::setPluginName('codeguard');
main::init();
add_action('init', array('main', 'run') );

add_action('admin_print_scripts', array('main', 'include_admins_script' ));

add_action( 'rest_api_init', array( CG_REST_Controller::get_instance(), 'register_api_routes' ) );
// Hooks to set up the crons
register_activation_hook( __FILE__, array( 'main', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'main', 'deactivate' ) );

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://s3.amazonaws.com/codeguard-wordpress-plugin-updates/codeguard.json',
    __FILE__,
    'codeguard'
);
