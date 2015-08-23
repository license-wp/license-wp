<?php

namespace Never5\LicenseWP\Email;

abstract class Email {

	/** @var string */
	private $subject = '';

	/** @var string */
	private $template = '';

	/** @var array */
	private $args = array();

	/** @var string */
	private $content = '';

	/**
	 * __construct
	 *
	 * @param $subject
	 * @param $template
	 */
	public function __construct( $subject, $template, $args = array() ) {
		$this->subject  = $subject;
		$this->template = $template;
		$this->args     = $args;
	}

	/**
	 * @return string
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * @return string
	 */
	public function get_content() {

		// load template file into content if still empty
		if ( empty( $this->content ) ) {
			ob_start();
			wc_get_template( $this->template, $this->args, 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
			$this->content = ob_get_clean();
		}

		return $this->content;
	}

}