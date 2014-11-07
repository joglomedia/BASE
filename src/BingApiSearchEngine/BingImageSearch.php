<?php
/**
 * This file is part of the BingApiSearchEngine (BASE) library.
 *
 * (c) Edi Septriyanto <me@masedi.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BingApiSearchEngine;

use HttpAdapter\HttpAdapterInterface;
use HttpAdapter\CurlHttpAdapter;

/**
 * BingImageSearch class.
 *
 * @author Edi Septriyanto <me@masedi.net>
 */
class BingImageSearch
{
	/**
	 * The host name of the API endpoint.
 	 *
	 * @var string
	 */
	const BingApiHost = 'api.datamarket.azure.com';

	/**
	 * The API key to use.
	 *
	 * @var string
	 */
	private $bingApiKey;
	
	/**
	 * The adapter to use.
	 *
	 * @var HttpAdapterInterface
	 */
	private $adapter;

	/**
	 * Constructor.
	 *
	 * @param string	$bingApiKey		the Bing API key to use.
	 */
	public function __construct( $bingApiKey = '', HttpAdapterInterface $adapter = null ) 
	{
		$this->bingApiKey = $bingApiKey;
		$this->setAdapter( $adapter );
	}
	
	/**
	 * Set the adapter to use. The cURL adapter will be used by default.
	 *
	 * @param HttpAdapterInterface $adapter The HttpAdapter to use (optional).
	 */
	public function setAdapter( HttpAdapterInterface $adapter = null )
	{
		$this->adapter = ( ! is_null( $adapter ) ) ? $adapter : new CurlHttpAdapter();
	}
	
	/**
	 * Get multiple images from Bing API image search.
	 *
	 * @param array $args Bing API parameters
	 * @return array $images Image from Bing image search
	 */
	public function getImages( $args ) 
	{
		$__args = array_merge( 
			array(
				'options'	=> "'EnableHighlighting'",
				'format'	=> 'json',
				'maxResults'	=> 10, // Max results
				'adultFilters'	=> "'Off'",
				'imageFilters'	=> "'Size:Large'",
				'query'		=> '',
				'return'	=> 'array'
			),
			$args
		);
		extract( $__args, EXTR_SKIP );

		//$http = new CurlHttpAdapter();
		$this->adapter->setConfig( array( 
			'host'		=> self::BingApiHost, 
			'curl_http_auth'=> $this->bingApiKey . ':' . $this->bingApiKey, 
			'use_ssl'	=> true 
		) );
		
		$apiUrl = $this->adapter->setUrl( 'Data.ashx/Bing/Search/v1/Image' );
		$params = array(
			'Options'	=> $options,
			'$format'	=> $format,		// json | xml
			'$top'		=> $maxResults,
			'Adult'		=> $adultFilters,
			'ImageFilters'	=> $imageFilters,	// Size:Small|Medium|Large|
			'Query'		=> "'" . $query . "'",	// should be already url encoded
		);
		$response = $this->adapter->get( $apiUrl, $params );
		$result = json_decode( $response );
		
		if ( $result == false ) 
			return false;
			
		if ( $return == 'object' )
			return $result;

		// If $result is requested as array format.
		$images = array();
		foreach( $result->d->results as $image ) {
			$images[] = array(
				'ID'		=> $image->ID,
				'Title'		=> htmlentities( $image->Title ),
				'MediaUrl'	=> $image->MediaUrl,
				'ThumbnailUrl'	=> $image->Thumbnail->MediaUrl,
				'Width'		=> (int) $image->Width,
				'Height'	=> (int) $image->Height,
				'ContentType'	=> isset( $image->ContentType ) ? $image->ContentType : '',
				'FileSize'	=> (int) $image->FileSize,
				'SourceUrl'	=> $image->SourceUrl
			);
		}
	
		return $images;
	}

}
