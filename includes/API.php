<?php

namespace ConceptReplacer;

class API {
	protected $manager;

	protected $sessionID ='';
	protected $url = false;
	protected $module = 'swapgender';
	protected $localize = false;

	protected $recognizedModules = [ 'swapgender' ];

	protected $fetcher;

	public function __construct( $url, $module = 'swapgender', $localize = false, $isMobile = false ) {
		$this->manager = new DictionaryManager();
		$this->url = $url;
		$this->module = $this->isRecognizedModule( $module ) ? $module : 'swapgender';
		$this->localize = $localize;
		$this->mobile = $isMobile;

		$this->fetcher = new Fetcher();
	}

	public function process() {
		$output = $this->prep();

		if ( $this->fetcher->isError() ) {
			return 'ERROR: ' . $this->fetcher->getError();
		}
		if ( $this->localize ) {
			$parse = parse_url( $this->url );
			$output = Replacer::fixLinks(
				$parse[ 'scheme' ],
				$parse[ 'host' ],
				$output
			);
		}
		$output = $this->runModule( $output );

		return $output;
	}

	/**
	 * Check whether a requested URL is in a specified domain
	 * @param [type] $url [description]
	 * @return boolean [description]
	 */
	public static function isInDomain( $domain, $url ) {
		$parse = parse_url( $url );
		return strpos( $parse[ 'host' ], $domain ) !== false;
	}

	/**
	 * Prepare the url for processing
	 *
	 * @return string Prepared HTML
	 */
	protected function prep() {
		$output = $this->fetcher->fetch( $this->url, $this->mobile );

		if ( !$this->fetcher->isError() ) {
			$output = Replacer::removeScripts( $output );
		}

		return $output;
	}

	protected function runModule( $output = '' ) {
		switch ( $this->module ) {
			case 'swapgender':
			default:
				$output = Replacer::replaceTerms(
					$output,
					$this->manager->getDictionary( 'women' ),
					$this->manager->getDictionary( 'men' )
				);

				$output = Replacer::replaceTerms(
					$output,
					$this->manager->getDictionary( 'men' ),
					$this->manager->getDictionary( 'women' )
				);
				break;
		}

		return $output;
	}

	protected function isRecognizedModule( $moduleName ) {
		return in_array( $moduleName, $this->recognizedModules );
	}
}
