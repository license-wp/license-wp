<?php

namespace Never5\LicenseWP\Activation;

interface Repository {

	/**
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function retrieve( $id );

	/**
	 * @param Activation $activation
	 *
	 * @return Activation
	 */
	public function persist( $activation );
}