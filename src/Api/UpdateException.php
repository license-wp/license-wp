<?php

namespace Never5\LicenseWP\Api;

/**
 * Class UpdateException
 * @package Never5\LicenseWP\Api
 */
class UpdateException extends \Exception {

	private $key = '';

	/**
	 * __construct
	 *
	 * @param string $message
	 * @param string $key
	 */
	public function __construct( $message, $key ) {
		$this->key = $key;
		parent::__construct( $message );
	}

	/**
	 * Return UpdateException data in array
	 *
	 * @return string
	 */
	public function __toArray() {
		return array( $this->key => $this->getMessage() );
	}

}