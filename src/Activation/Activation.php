<?php

namespace Never5\LicenseWP\Activation;

use Never5\LicenseWP\ApiProduct;

class Activation {

	/** @var int */
	private $id = 0;

	/** @var string */
	private $license_key = '';

	/** @var int */
	private $api_product_id = 0;

	/** @var string */
	private $instance = '';

	/** @var \DateTime */
	private $activation_date;

	/** @var \DateTime */
	private $activation_active = 0;

	/**
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function get_license_key() {
		return $this->license_key;
	}

	/**
	 * @param string $license_key
	 */
	public function set_license_key( $license_key ) {
		$this->license_key = $license_key;
	}

	/**
	 * @return int
	 */
	public function get_api_product_id() {
		return $this->api_product_id;
	}

	/**
	 * @param int $api_product_id
	 */
	public function set_api_product_id( $api_product_id ) {
		$this->api_product_id = $api_product_id;
	}

	/**
	 * Get API Product post ID
	 *
	 * @return int
	 */
	public function get_api_product_post_id() {
		$api_product = get_page_by_path( $this->get_api_product_id(), OBJECT, ApiProduct\PostType::KEY );
		return isset( $api_product->ID ) ? $api_product->ID : 0;
	}

	/**
	 * @return string
	 */
	public function get_instance() {
		return $this->instance;
	}

	/**
	 * @param string $instance
	 */
	public function set_instance( $instance ) {
		$this->instance = $instance;
	}

	/**
	 * @return \DateTime
	 */
	public function get_activation_date() {
		return $this->activation_date;
	}

	/**
	 * @param \DateTime $activation_date
	 */
	public function set_activation_date( $activation_date ) {
		$this->activation_date = $activation_date;
	}

	/**
	 * @return \DateTime
	 */
	public function get_activation_active() {
		return $this->activation_active;
	}

	/**
	 * @param \DateTime $activation_active
	 */
	public function set_activation_active( $activation_active ) {
		$this->activation_active = $activation_active;
	}

	/**
	 * Returns URL to deactivate activation
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 *
	 * @return string
	 */
	public function get_deactivate_url( $license ) {
		return esc_url( add_query_arg( array(
			'deactivate_licence' => $this->get_id(),
			'licence_key'        => $license->get_key(),
			'activation_email'   => $license->get_activation_email()
		) ) );
	}
}