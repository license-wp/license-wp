<?php

namespace Never5\LicenseWP\ApiProduct;

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
	 * Create ApiProduct object
	 *
	 * @param int $id
	 *
	 * @return ApiProduct|bool False if doesn't exist.
	 */
	public function make( $id = 0 ) {

		// empty license object
		$product = new ApiProduct();

		// check if id is sset
		if ( $id > 0 ) {
			// fetch data from repository
			$data = $this->repository->retrieve( $id );

			// If there is no data, the API product may no longer exist.
			if ( empty( $data->id ) ) {
				return false;
			}

			// set data from repository in API product object
			foreach ( $data as $dkey => $dval ) {
				$method = 'set_' . $dkey;
				if ( method_exists( $product, $method ) ) {
					$product->$method( $dval );
				}
			}
		}

		// return product
		return $product;
	}

}
