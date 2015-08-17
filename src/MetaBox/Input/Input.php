<?php

namespace Never5\LicenseWP\MetaBox\Input;

abstract class Input {

	/** @var  string */
	private $key;

	/** @var  array */
	private $field;

	/**
	 * __constructor
	 *
	 * @param $key
	 * @param $field
	 */
	public function __construct( $key, $field ) {
		$this->key   = $key;
		$this->field = $field;
	}

	/**
	 * @return string
	 */
	protected function get_key() {
		return $this->key;
	}

	/**
	 * @return array
	 */
	protected function get_field() {
		return $this->field;
	}

	abstract function view();
}