<?php


/**
 * @version 1.0
 * @uses Bandcamp API Version 1
 * @author longjohn
 */
class bandcampClient
{
	
	protected $apiKey = null; 
	
	
	/**
	 * @param string $apiKey
	 */
	function __construct ( $apiKey )
	{
		$this -> apiKey = $apiKey;
	}
	
	
	/**
	 * @param string $url
	 * @return stdClass|false
	 */
	protected function getJsonDataFrom ( $url, $raw = FALSE )
	{
		$result = FALSE;
		if (extension_loaded('curl'))
		{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			curl_close($ch);
			
		} elseif ( ini_get ( 'allow_url_fopen' ) )
		{
			$result = file_get_contents ( $url );
		}
		
		if ( $result )
		{
			if ($raw)
			{
				return $result;
			}
			$result = json_decode( $result );
			if ($this -> isValid($result))
			{
				return $result;
			}
		}
		return FALSE;
	}
	
	
	/**
	 * Returns information about a band.
	 * @param int $bandId
	 * @return stdClass|false
	 */
	function getBandInfo ( $bandId )
	{
		$url = "http://api.bandcamp.com/api/band/1/info?key={$this -> apiKey}&";
		$url .= is_numeric($bandId) ? "band_id={$bandId}" : "band_url={$bandId}";
		return $this -> getJsonDataFrom ( $url );
	}
	
	
	/**
	 * Returns a band’s top level discography.
	 * @param int $bandId
	 * @return stdClass|false
	 */
	function getBandDiscography ( $bandId )
	{
		$url = "http://api.bandcamp.com/api/band/1/discography?key={$this -> apiKey}&";
		$url .= is_numeric($bandId) ? "band_id={$bandId}" : "band_url={$bandId}";
		return $this -> getJsonDataFrom($url);
	}
	
	
	/**
	 * Returns information about an album.
	 * @param int $albumId
	 * @return stdClass|false
	 */
	function getAlbumInfo ( $albumId )
	{
		$url = "http://api.bandcamp.com/api/album/1/info?key={$this -> apiKey}&album_id={$albumId}";
		return $this -> getJsonDataFrom($url);
	}
	
	
	/**
	 * Returns information about a track.
	 * @param int $trackId
	 * @return stdClass|false
	 */
	function getTrackInfo ( $trackId )
	{
		$url = "http://api.bandcamp.com/api/track/1/info?key={$this -> apiKey}&track_id={$trackId}";
		return $this -> getJsonDataFrom($url);	
	}
	
	
	function getUrlInfo ( $url, $raw = FALSE )
	{
		$url = "http://api.bandcamp.com/api/url/1/info?key={$this -> apiKey}&url={$url}";
		return $this -> getJsonDataFrom($url, $raw);
	}
	
	
	protected function isValid ( $result )
	{
		return (!isset($result -> error) or !$result -> error);
	}
	
}





