<?php

namespace ConceptReplacer;

class Dictionary {
	protected $dir = '';
	protected $dictionaries;

	/**
	 * Set up the contents of the available dictionaries
	 * for reading.
	 */
	public function __construct( $name, $definition ) {
		$this->name = $name;
		$this->definition = $definition;
	}

	/**
	 * Get the name of this dictionary
	 *
	 * @return string Name of the dictionary
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Get the array of terms belonging to a dictionary
	 * group and category
	 *
	 * @param string $group Definition group
	 * @param string [$isPlural] Get plural words.
	 *  If plural doesn't exist, returns the singular
	 * @return array Array of names
	 */
	public function getTerms( $group, $isPlural = false ) {
		$definition = $this->getDefinitionGroup( $group );

		if ( count( $definition ) === 0 ) {
			return [];
		}

		if (
			$isPlural &&
			isset( $definition[ 'plural' ] ) &&
			count( $definition[ 'plural' ] ) > 0
		) {
			return $definition[ 'plural' ];
		}

		return $definition[ 'singular' ];
	}

	/**
	 * Get a single random term from the array of terms
	 * from this group and category.
	 *
	 * @param string $group Definition group
	 * @param string [$isPlural] Get plural words.
	 *  If plural doesn't exist, returns the singular
	 * @return string Single term
	 */
	public function getRandomTerm( $group, $isPlural = false ) {
		$wordsArray = $this->getTerms( $group, $isPlural );
		$randomIndex = array_rand( $wordsArray );

		return $wordsArray[ $randomIndex ];
	}

	/**
	 * Get the full definition of the requested dictionary
	 *
	 * @return array Associative array of dictionary definitions
	 */
	public function getDefinition() {
		return $this->definition;
	}

	/**
	 * Get the definition group of the requested dictionary
	 * and group.
	 *
	 * @param string $name Dictionary name
	 * @param string $group Definition group
	 * @return array Associative array of dictionary definitions
	 */
	public function getDefinitionGroup( $group ) {
		return isset( $this->definition[ $group ] ) ?
			$this->definition[ $group ] : [];
	}
}
