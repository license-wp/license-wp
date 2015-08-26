<?php

namespace Never5\LicenseWP\Email;

class LostLicense extends Email {

	/**
	 * __construct
	 *
	 * @param array $licenses
	 * @param string $first_name
	 */
	public function __construct( $licenses, $first_name ) {
		$subject = sprintf( 'Your %s license keys', get_bloginfo( 'name' ) );
		parent::__construct( $subject, 'lost-license-email.php', array(
			'licenses'         => $licenses,
			'user_first_name' => $first_name
		) );
	}

}