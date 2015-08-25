<?php

namespace Never5\LicenseWP\License;

class Factory {

	/** @var Repository */
	private $repository;

	/**
	 * __construct
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Create License object
	 *
	 * @param string $key
	 *
	 * @return License
	 */
	public function make( $key = '' ) {

		// empty license object
		$license = new License();

		// check if id is set
		if ( '' !== $key ) {

			// fetch data from repository
			$data = $this->repository->retrieve( $key );

			// set data from repository in license object
			foreach ( $data as $dkey => $dval ) {
				$method = 'set_' . $dkey;
				if ( method_exists( $license, $method ) ) {
					$license->$method( $dval );
				}
			}

		}

		// return license
		return $license;
	}

}