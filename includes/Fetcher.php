<?php

namespace ConceptReplacer;

class Fetcher {
	/**
	 * Reads the source of a remote URL, strips <script>
	 * and other potentially harmful content.
	 *
	 * @param String $url The URL of the requested page
	 */
	static public function fetch( $url ) {
		if ( !function_exists( 'curl_init' ) ){
			return false; // die( 'Missing cURL module.' );
		}

		$ch = curl_init();

		/* Get the URL */
		curl_setopt( $ch, CURLOPT_URL, $url );

		/* Referer */
		curl_setopt( $ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME'] );

		/* User Agent */
		curl_setopt( $ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");

		/* Don't include header */
		curl_setopt( $ch, CURLOPT_HEADER, 0 );

		/* Return the data (do not print) */
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

		/* Retrieve */
		$output = curl_exec( $ch );

		/* Close cURL */
		curl_close( $ch );

		return $output;
	}

}
