<?php

namespace Never5\LicenseWP\Email;

class Manager {

	/**
	 * Send an email
	 *
	 * @param Email $email
	 * @param string $recipient
	 *
	 * @return bool
	 */
	public function send( Email $email, $recipient ) {
		return wp_mail( $recipient, $email->get_subject(), $email->get_content() );
	}

}