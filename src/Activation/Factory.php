<?php

namespace Never5\LicenseWP\Activation;

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
	 * Create Activation object
	 *
	 * @param int $id
	 *
	 * @return Activation
	 */
	public function make( $id = 0 ) {

		// empty license object
		$activation = new Activation();

		// check if id is sset
		if ( $id > 0 ) {

			// fetch data from repository
			$data = $this->repository->retrieve( $id );

			// set data from repository in activation object
			foreach ( $data as $dkey => $dval ) {
				$method = 'set_' . $dkey;
				if ( method_exists( $activation, $method ) ) {
					$activation->$method( $dval );
				}
			}

		}

		// return license
		return $activation;
	}

}