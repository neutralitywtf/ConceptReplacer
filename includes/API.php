<?php

namespace ConceptReplacer;

class API {
	protected $manager;

	protected $sessionID ='';
	protected $url = false;
	protected $module = 'swapgender';
	protected $localize = false;

	protected $recognizedModules = [ 'swapgender' ];

	public function __construct( $url, $module = 'swapgender', $localize = false ) {
		$this->manager = new DictionaryManager();
		$this->url = $url;
		$this->module = $this->isRecognizedModule( $module ) ? $module : 'swapgender';
		$this->localize = $localize;
	}

	public function process() {
		$output = $this->prep();

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

	protected function prep() {
		$output = Fetcher::fetch( $this->url );
		$output = Replacer::removeScripts( $output );

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
