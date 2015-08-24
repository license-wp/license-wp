<?php

namespace Never5\LicenseWP\License;

interface Repository {

	/**
	 * @param string $key
	 *
	 * @return \stdClass
	 */
	public function retrieve( $key );

	/**
	 * @param License $license
	 *
	 * @return License
	 */
	public function persist( $license );
}