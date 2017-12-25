<?php






function is_post_editor()
{
	$scriptName = basename($_SERVER['SCRIPT_NAME']);
	if (('post.php' == $scriptName) and ('edit' == $_GET['action']))
		return TRUE;
	if ('post-new.php' == $scriptName)
		return TRUE;
	return FALSE;
}


function is_widget_editor()
{
	return ('widgets.php' == basename($_SERVER['SCRIPT_NAME']));
}


function wp_bandcamp_apikey()
{
	return get_option('wp_bandcamp_apikey', FALSE);
}


function wp_bandcamp_set_apikey($apikey)
{
	return update_option('wp_bandcamp_apikey', $apikey);
}


function wp_bandcamp_get_client()
{
	require_once ( WP_BANDCAMP_PLUGIN_PATH . 'components/bandcampClient.php' );
	if ($apikey = wp_bandcamp_apikey())
		return new bandcampClient($apikey);
	return FALSE;
}


function wp_bandcamp_default_atts()
{
	return array(
		'type' => 'album',
		'bgcol' => '#FFFFFF',
		'linkcol' => '#4285BB',
		'size' => 'grande',
		'width' => 300,
		'height' => 100
	);
}


function wp_bandcamp_content_types()
{
	$array = array(
		'track' => __('Track'),
		'album' => __('Album')
	);
	/**if (wp_bandcamp_apikey())
	{
		$array['band'] = __('Band');
	}*/
	return $array;
}


function wp_bandcamp_player_sizes()
{
	return array(
		'venti' => 'Venti (400x100)',
		'grande' => 'Grande (300x100)',
		'grande2' => 'Grande w/tracklist (300x355)',
		'grande3' => 'Grande w/tracklist+art (300x415)',
		'tall' => 'Tall (150x295)',
		'tall2' => 'Tall w/track list (150x450)',
		'short' => 'Short (46x23)'
	);
}


function wp_bandcamp_player_dimensions ( $size = null )
{
	$dimensions = array (
		'venti' => array(400,100),
		'grande' => array(300,100),
		'grande2' => array(300,355),
		'grande3' => array(300,415),
		'tall' => array(150,295),
		'tall2' => array(150,450),
		'short' => array(46,23)
	);
	return strlen($size) ? $dimensions[$size] : $dimensions;
}


/**
 * Player Embedding Helpers 
 */
function wp_bandcamp_build_movie_url ( $atts )
{
	extract ( array_merge ( wp_bandcamp_default_atts (), $atts ));
	$bgcol = str_replace('#', '', $bgcol);
	$linkcol = str_replace('#', '', $linkcol);
	$transparent_text = ($transparent == 'true') ? 'transparent=true' : '';
	return "http://bandcamp.com/EmbeddedPlayer/v=2/$type=$id/size=$size/bgcol=$bgcol/linkcol=$linkcol/$transparent_text";
}

function wp_bandcamp_embed_player ( $atts )
{
	extract ( array_merge ( wp_bandcamp_default_atts (), $atts ));
	list($width, $height) = wp_bandcamp_player_dimensions ( $size );
	$movieUrl = wp_bandcamp_build_movie_url ( $atts );
	return '<iframe width="' . $width . '" height="' . $height . '" src="' . $movieUrl . '" style="position:relative;display:block;width:' . $width . 'px;height:' . $height . 'px;" allowtransparency="true" frameborder="0"></iframe>';
}






