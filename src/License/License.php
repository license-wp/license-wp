<?php

namespace Never5\LicenseWP\License;

class License {

	private $key = '';

	private $order_id = 0;

	private $user_id = 0;

	private $activation_email = '';

	private $product_id = 0;

	private $activation_limit = 0;

	private $date_created;

	private $date_expires;

	/**
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * @param string $key
	 */
	public function set_key( $key ) {
		$this->key = $key;
	}

	/**
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * @param int $order_id
	 */
	public function set_order_id( $order_id ) {
		$this->order_id = $order_id;
	}

	/**
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * @return string
	 */
	public function get_activation_email() {
		return $this->activation_email;
	}

	/**
	 * @param string $activation_email
	 */
	public function set_activation_email( $activation_email ) {
		$this->activation_email = $activation_email;
	}

	/**
	 * @return int
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * @param int $product_id
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * @return int
	 */
	public function get_activation_limit() {
		return $this->activation_limit;
	}

	/**
	 * @param int $activation_limit
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->activation_limit = $activation_limit;
	}

	/**
	 * @return \DateTime
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * @param \DateTime $date_created
	 */
	public function set_date_created( $date_created ) {
		$this->date_created = $date_created;
	}

	/**
	 * @return \DateTime
	 */
	public function get_date_expires() {
		return $this->date_expires;
	}

	/**
	 * @param \DateTime $date_expires
	 */
	public function set_date_expires( $date_expires ) {
		$this->date_expires = $date_expires;
	}

}