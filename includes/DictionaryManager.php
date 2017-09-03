<?php

namespace ConceptReplacer;

class DictionaryManager {
	protected $dictionaries;

	/**
	 * Set up the contents of the available dictionaries
	 * for reading.
	 */
	public function __construct() {
		$this->dir = dirname( __DIR__ ) . DIRECTORY_SEPARATOR
			. 'dictionaries' . DIRECTORY_SEPARATOR;

		$this->dictionaries = [];

		// Initialize
		$this->readFiles();
	}

	public function getDictionary( $name ) {
		return $this->exists( $name ) ?
			$this->dictionaries[ $name ] :
			null;
	}

	/**
	 * Check whether the dictionary exists
	 *
	 * @param string $name Dictionary name
	 * @return bool Dictionary exists
	 */
	protected function exists( $name ) {
		$name = strtolower( $name );

		return isset( $this->dictionaries[ $name ] );
	}

	/**
	 * Read the files in the server and store as dictionaries
	 */
	protected function readFiles() {
		$files = glob( $this->dir . "*.json" );

		foreach ( $files as $filename ) {
			$content = file_get_contents( $filename );
			// Decode as an associative array
			$data = json_decode( $content, true );

			if (
				isset( $data[ 'name' ] ) &&
				isset( $data[ 'definitions' ] )
			) {
				$this->dictionaries[ strtolower( $data[ 'name' ] ) ] = new Dictionary( $data );
			}
		}
	}
}
