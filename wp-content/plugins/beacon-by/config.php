<?php 


//Prevent directly browsing to the file
if (function_exists('plugin_dir_url')) 
{

	$host = $_SERVER['HTTP_HOST'];

	switch ($host)
	{
		case 'wordpress':
			$create_target = 'beacon.dev';
		break;

		case 'wp.beacon.by':
			$create_target = 'sandbox.beacon.by';
		break;

		default:
			$create_target = 'beacon.by';
		break;
	}

    define('BEACONBY_VERSION',      '1.0');
    define("BEACONBY_HOMEPAGE",     "http://beacon.by/");
    define("BEACONBY_HELPLINK",     "http://beacon.by/wordpress");
    define('BEACONBY_PLUGIN_URL',   plugin_dir_url(__FILE__));
    define('BEACONBY_PLUGIN_PATH',  plugin_dir_path(__FILE__));
	define('BEACONBY_SITE_URL',		get_site_url());
	define('BEACONBY_PER_PAGE',		50);

	define('BEACONBY_CREATE_TARGET',	$create_target);

}



