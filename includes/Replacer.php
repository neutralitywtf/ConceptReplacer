<?php

namespace ConceptReplacer;
use DOMDocument;

class Replacer {
	protected static $usedReplacedTerms = [];
	protected static $usedReplacingTerms = [];

	protected static function parseHTML( $doc, $html ) {
		// set error level
		$internalErrors = libxml_use_internal_errors(true);

		$doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8') );

		// Restore error level
		libxml_use_internal_errors($internalErrors);
	}

	/**
	 * Replaces terms and related terms with the replacement
	 * words, according to chosen type.
	 */
	public static function replaceTerms( $originalHtml, Dictionary $searchDictionary, Dictionary $replaceDictionary ) {

		$doc = new \DOMDocument();
		static::parseHTML( $doc, $originalHtml );
		$xpath = new \DOMXpath( $doc );
		$searchTerms = $searchDictionary->getDefinition();

		// Look for all iterations that were not yet replaced
		// (That are not wrapped already)
		// TODO: The wrapping should be configurable
		$textNodes = $xpath->query( "//text()[parent::*[not(contains(@class, 'conceptreplacer-replaced'))]]" );

		foreach ( $textNodes as $textNode ) {
			$nodeContent = htmlspecialchars( $textNode->wholeText );

			self::$usedReplacedTerms = [];

			$opts = [ 'singular', 'plural' ];

			foreach ( $searchTerms as $category => $data ) {

				foreach ( $opts as $opt ) {
					if (
						isset( $data[ $opt ] ) &&
						!in_array(
							$data[ $opt ][ 0 ],
							self::$usedReplacedTerms
						)
						// !in_array(
						// 	$data[ $opt ][ 0 ],
						// 	self::$usedReplacingTerms
						// ) &&
					) {
						if ( count( $data[ $opt ] ) > 1 ) {
							$regexSearch = '(' . join( array_map( 'preg_quote', $data[ $opt ] ), '|' ) . ')';

							self::$usedReplacedTerms = array_merge( self::$usedReplacedTerms, $data[ $opt ] );
						} else {
							$regexSearch = $data[ $opt ][ 0 ];

							self::$usedReplacedTerms[] = $data[ $opt ][ 0 ];
						}

						$replaceWordsArray = $replaceDictionary->getTerms(
							$category,
							$opt === 'plural'
						);

						$nodeContent = preg_replace_callback(
							"/\b$regexSearch\b/i",
								function( $match ) use ( $replaceWordsArray, $doc ) {
									return self::randomReplace(
										$doc,
										$match,
										$replaceWordsArray
									);
								},
							$nodeContent
						);
					}
				}

			}

			$fakedoc = new \DOMDocument();
			static::parseHTML( $fakedoc, '<div>' . $nodeContent . '</div>' );

			$div = $fakedoc->getElementsByTagName( 'div' )->item( 0 );

			foreach ( $div->childNodes as $child ) {
				$child = $doc->importNode( $child, true );
				$textNode->parentNode->insertBefore( $child, $textNode );
			}
			$textNode->parentNode->removeChild( $textNode );
		}

		return $doc->saveHTML();
	}

	public static function removeScripts( $originalHtml ) {
		$doc = new \DOMDocument();
		static::parseHTML( $doc, $originalHtml );

		// Clean out all scripts
		// See https://stackoverflow.com/questions/15925961/domdocument-remove-script-tags-from-html-source
		while ( ( $r = $doc->getElementsByTagName( "script" ) ) && $r->length ) {
			$r->item( 0 )->parentNode->removeChild( $r->item( 0 ) );
		}

		return $doc->saveHTML();
	}

	/**
	 * Add a 'base href' rule to the HTML so the links work even if they
	 * are localized.
	 *
	 * @param string $scheme Domain scheme
	 * @param string $host Domain host
	 * @param string $originalHtml Original HTML of the page
	 * @return string Fixed HTML
	 */
	public static function fixLinks( $scheme, $host, $originalHtml ) {
		$domain = $scheme . '://' . $host;

		$doc = new \DOMDocument();
		static::parseHTML( $doc, $originalHtml );

		$base = $doc->createElement('base');
		$base->setAttribute( 'href', $domain );

		$head = $doc->getElementsByTagName('head')->item(0);
		if ( !$head ) {
			$html = $doc->getElementsByTagName( 'html' )->item( 0 );
			$head = $doc->createElement( 'head' );
			$html->insertBefore( $head, $html->firstChild );
		}

		if ( $head->hasChildNodes() ) {
			$head->insertBefore( $base, $head->firstChild );
		} else {
			$head->appendChild( $base );
		}
		return $doc->saveHTML();
	}

	public static function addExtras( $originalHtml ) {
		$doc = new \DOMDocument();
		$doc->loadXML( $originalHtml );
		$body = $doc->getElementsByTagName( 'body' )->item( 0 );
		// $body->setAttribute(
		// 	'style',
		// 	// TODO: Make this configurable, and in a file
		// 	'.conceptreplacer-replaced {' .
		// 	'	border-bottom: 1px dashed #222;' .
		// 	'	font-size: 1.2em;' .
		// 	'}'
		// );

		return $doc->saveHTML();
	}

	/**
	 * Replace a match with a random replacement
	 *
	 * @param array $match Matches
	 * @param array $replaceWordsArray Replacement options
	 * @return DOMNode Replacement
	 */
	protected static function randomReplace( DOMDocument $doc, array $match, array $replaceWordsArray ) {
		$randomIndex = array_rand( $replaceWordsArray );
		$replacementWord = $replaceWordsArray[ $randomIndex ];

		$styles = [
			'background-color: rgba(139, 195, 74, 0.5);',
			'padding: 0 0.2em;'
		];

		if ( ctype_upper( substr( $match[ 0 ], 0, 1 ) ) ) {
			$replacementWord = ucfirst( $replacementWord );
		}
		self::$usedReplacingTerms[] = strtolower( $replacementWord );

		$span = $doc->createElement( 'span' );
		$span->setAttribute( 'title', 'Original: ' . addslashes( $match[0] ) );
		$span->setAttribute( 'style', join( ' ', $styles ) );
		$span->setAttribute( 'class', 'conceptreplacer-replaced' );

		$text = $doc->createTextNode( $replacementWord );
		$span->appendChild( $text );

		return $doc->saveHTML( $span );
	}
}
