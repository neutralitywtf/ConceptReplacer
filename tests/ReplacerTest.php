<?php

use PHPUnit\Framework\TestCase;
use ConceptReplacer\Dictionary;
use ConceptReplacer\Replacer;

class ReplacerTest extends TestCase {
	protected $searchDefinition = [
		'noun' => [
			'singular' => [ 'fooNoun1', 'fooNoun2' ],
			'plural' => [ 'fooNounPlural1', 'fooNounPlural2' ],
		],
		'verb' => [
			'singular' => [ 'fooVerb1', 'fooVerb2' ],
		],
	];
	protected $replaceDefinition = [
		'noun' => [
			// Include only one value so we know to
			// expect it in the test
			'singular' => [ 'barNoun1' ],
			'plural' => [ 'barNounPlural1', 'barNounPlural2' ],
		],
		'verb' => [
			'singular' => [ 'barVerb1', 'barVerb2' ],
		],
	];
	protected $htmlCases = [
		// Direct
		'<p>foo<span>bar</span>fooNoun1</p>' => '<p>foo<span>bar</span><span title=\'Original: fooNoun1\' class=\'conceptreplacer-replaced\'>barNoun1</span></p>',
		// Repeated; do not replace already-existing term
		'<p>fooNoun2<span>bar</span><span title=\'Original: somethingThatWasPreviouslyReplaced\' class=\'conceptreplacer-replaced\'>fooNoun1</span></p>' => '<p><span title=\'Original: fooNoun2\' class=\'conceptreplacer-replaced\'>barNoun1</span><span>bar</span><span title=\'Original: somethingThatWasPreviouslyReplaced\' class=\'conceptreplacer-replaced\'>fooNoun1</span></p>',
	];

	public function testReplaceTerms() {
		$searchDictionary = new Dictionary(
			'search',
			$this->searchDefinition
		);
		$replaceDictionary = new Dictionary(
			'replace',
			$this->replaceDefinition
		);

		foreach ( $this->htmlCases as $input => $result ) {
			$this->assertXmlStringEqualsXmlString(
				Replacer::replaceTerms(
					$input,
					$searchDictionary,
					$replaceDictionary
				),
				'<html><body>' . $result . '</body></html>'
			);
		}
	}
}
