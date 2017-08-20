<?php

use PHPUnit\Framework\TestCase;
use ConceptReplacer\Dictionary;

class DictionaryTest extends TestCase {
	protected $definition = [
		'noun' => [
			'singular' => [ 'fooNoun1', 'fooNoun2' ],
			'plural' => [ 'fooNounPlural1', 'fooNounPlural2' ],
		],
		'verb' => [
			'singular' => [ 'fooVerb1', 'fooVerb2' ],
		],
	];

	public function testConstruction() {
		$testDictionary = new Dictionary(
			'test',
			$this->definition
		);

		$this->assertEquals(
			$testDictionary->getName(),
			'test'
		);

		$this->assertEquals(
			$testDictionary->getDefinition(),
			$this->definition
		);

		return $testDictionary;
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetTerms( Dictionary $dictionary ) {

		$this->assertEquals(
			$dictionary->getTerms( 'noun' ),
			[ 'fooNoun1', 'fooNoun2' ]
		);
		$this->assertEquals(
			$dictionary->getTerms( 'noun', true ),
			[ 'fooNounPlural1', 'fooNounPlural2' ]
		);

		// When plural is empty, fallback on singular
		$this->assertEquals(
			$dictionary->getTerms( 'verb', true ),
			[ 'fooVerb1', 'fooVerb2' ]
		);
	}

	/**
	 * @depends testConstruction
	 */
	public function testGetRandomTerm( Dictionary $dictionary ) {
		$this->assertTrue(
			in_array(
				$dictionary->getRandomTerm( 'noun' ),
				$dictionary->getTerms( 'noun' )
			)
		);
	}
}
