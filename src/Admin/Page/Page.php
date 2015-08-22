<?php

namespace Never5\LicenseWP\Admin\Page;

/**
 * Class Page
 * @package Never5\LicenseWP\Admin\Pages
 */
abstract class Page {

	/** @var string */
	private $title;

	/** @var string */
	private $slug;

	/** @var null */
	private $pos;

	/**
	 * __construct
	 *
	 * @param string $title
	 * @param null $pos
	 */
	public function __construct( $title, $pos = null ) {
		$this->title = $title;
		$this->slug  = 'license_wp_' . str_ireplace( ' ', '_', strtolower( trim( $title ) ) );
		$this->pos = $pos;
	}

	/**
	 * Setup page
	 */
	public function setup() {
		$hook = add_action( 'admin_menu', function () {
			add_menu_page( $this->get_title(), $this->get_title(), 'manage_options', $this->get_slug(), array(
				$this,
				'output'
			), null, $this->pos );
		}, 9 );

		// allow for to enqueue page specific styles & scripts
		add_action( 'admin_print_styles-'. $hook, array( $this, 'page_enqueue' ) );
	}

	/**
	 * Get the title
	 *
	 * @return string
	 */
	protected function get_title() {
		return $this->title;
	}

	/**
	 * Get the slug
	 *
	 * @return string
	 */
	protected function get_slug() {
		return $this->slug;
	}

	/**
	 * Method to enqueue page specific styles & scripts
	 */
	public function page_enqueue() {}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	abstract public function output();

}