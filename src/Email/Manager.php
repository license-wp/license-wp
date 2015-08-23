<?php

namespace Never5\LicenseWP\Email;

class Manager {

	/**
	 * Send an email
	 *
	 * @param Email $email
	 * @param string $recipient
	 */
	public function send( Email $email, $recipient ) {
		wp_mail( $recipient, $email->get_subject(), $email->get_content() );
	}

}