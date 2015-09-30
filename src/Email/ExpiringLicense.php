<?php

namespace Never5\LicenseWP\Email;

class ExpiringLicense extends Email {

	/**
	 * __construct
	 *
	 * @param string $subject
	 * @param string $body
	 * @param \Never5\LicenseWP\License\License $license
	 */
	public function __construct( $subject, $body, $license ) {
		parent::__construct( $subject, 'expiring-license-email.php', array(
			'body'          => $body,
			'license'       => $license,
			'email_heading' => $subject
		) );
	}

}