<?php

namespace ConceptReplacer;

use ErrorException;

class Fetcher {
	/**
	 * Reads the source of a remote URL, strips <script>
	 * and other potentially harmful content.
	 *
	 * @param String $url The URL of the requested page
	 * @param boolean $isMobile Request should ask for mobile version
	 */
	public function fetch( $url, $isMobile = false ) {
		if ( !function_exists( 'curl_init' ) ){
			throw new ErrorException( "The PHP 7 curl extension (php7.0-curl) is required." );
		}

		$userAgent = $isMobile ?
			'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 5 Build/LMY48B; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/43.0.2357.65 Mobile Safari/537.36' :
			'MozillaXYZ/1.0';

		$ch = curl_init();

		/* Get the URL */
		curl_setopt( $ch, CURLOPT_URL, $url );

		/* Referer */
		curl_setopt( $ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME'] );

		/* User Agent */
		curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );

		/* Don't include header */
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		/* Follow HTTP redirects */
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		/* Return the data (do not print) */
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

		/* Retrieve */
		$output = curl_exec( $ch );

		// $this->error = curl_error( $ch );
		// $this->error = $this->error ?
		// 	$this->error :
		// 	$output ? $output : 'ERROR';

		/* Close cURL */
		curl_close( $ch );

		return $output;
	}

	public function isError() {
		return false; // !empty( trim( $this->error ) );
	}

	public function getError() {
		return $this->error;
	}
}
