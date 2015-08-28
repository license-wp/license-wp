<?php

namespace Never5\LicenseWP\Api;

class ApiException extends \Exception {

	/**
	 * Custom
	 *
	 * @param string $message
	 * @param int $code
	 */
	public function __construct( $message, $code ) {
		parent::__construct( $message, absint( $code ) );
	}

	/**
	 * Return ApiException JSON error
	 *
	 * @return string
	 */
	public function __toString() {
		return json_encode( array( 'error_code' => $this->getCode(), 'error' => $this->getMessage() ) );
	}

}