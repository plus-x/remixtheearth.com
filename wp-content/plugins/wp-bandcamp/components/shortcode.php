<?php

add_shortcode('wp_bandcamp_player', 'wp_bandcamp_player_shortcode_old');
add_shortcode('bandcamp', 'wp_bandcamp_player_shortcode');


function wp_bandcamp_player_shortcode ( $atts )
{
	if (isset($atts['album']))
	{
		$atts['type'] = 'album';
		$atts['id'] = $atts['album'];
		unset($atts['album']);
	} elseif (isset($atts['track']))
	{
		$atts['type'] = 'track';
		$atts['id'] = $atts['track'];
		unset($atts['track']);
	}
	return wp_bandcamp_embed_player ( $atts );
}

function wp_bandcamp_player_shortcode_old ( $atts )
{
	$atts = wp_bandcamp_shortcode_old_prepare_atts ( $atts );
	return wp_bandcamp_embed_player ( $atts );
}

function wp_bandcamp_shortcode_old_prepare_atts ( $atts )
{
	$atts['bgcol'] = $atts['bg_color'];
	$atts['linkcol'] = $atts['link_color'];
	unset($atts['bg_color'], $atts['link_color']);
	
	return $atts;
}


