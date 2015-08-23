<?php

namespace Never5\LicenseWP\Email;

class NewLicense extends Email {

	/**
	 * __construct
	 *
	 * @param string $license_key
	 * @param string $first_name
	 */
	public function __construct( $license_key, $first_name ) {
		$subject = sprintf( 'Your %s license keys', get_bloginfo( 'name' ) );
		parent::__construct( $subject, 'new-licence-email.php', array(
			'key'             => $license_key,
			'user_first_name' => $first_name
		) );
	}

}