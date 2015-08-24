<?php

namespace Never5\LicenseWP\ApiProduct;

interface Repository {

	/**
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function retrieve( $id );

	/**
	 * @param ApiProduct $license
	 *
	 * @return ApiProduct
	 */
	public function persist( $license );
}