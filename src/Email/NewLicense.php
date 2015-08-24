<?php

namespace Never5\LicenseWP\Email;

class NewLicense extends Email {

	/**
	 * __construct
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param string $first_name
	 */
	public function __construct( $license, $first_name ) {
		$subject = sprintf( 'Your %s license keys', get_bloginfo( 'name' ) );
		parent::__construct( $subject, 'new-licence-email.php', array(
			'license'         => $license,
			'user_first_name' => $first_name
		) );
	}

}