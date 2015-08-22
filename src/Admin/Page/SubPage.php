<?php

namespace Never5\LicenseWP\Admin\Page;

abstract class SubPage extends Page {

	/** @var string */
	private $parent_slug;

	/**
	 * __construct
	 *
	 * @param string $parent_slug
	 * @param string $title
	 */
	public function __construct( $parent_slug, $title ) {
		$this->parent_slug = $parent_slug;
		parent::__construct( $title );
	}

	/**
	 * Setup page
	 *
	 * Overwrites the Page's setup method because we need to call add_submenu_page
	 */
	public function setup() {
		add_action( 'admin_menu', function () {
			$hook = add_submenu_page( $this->parent_slug, $this->get_title(), $this->get_title(), 'manage_options', $this->get_slug(), array(
				$this,
				'output'
			) );

			// allow for to enqueue page specific styles & scripts
			add_action( 'admin_print_styles-' . $hook, array( $this, 'page_enqueue' ) );
		}, 9 );


	}

}