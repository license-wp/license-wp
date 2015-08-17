<?php
namespace Never5\LicenseWP;

class File {

	/** @var String */
	private $file;

	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Return plugin file
	 *
	 * @return String
	 */
	public function plugin_file() {
		return $this->file;
	}

	/**
	 * Return plugin path
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( $this->file ) );
	}

	/**
	 * Return plugin url
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function plugin_url( $path = '' ) {
		return plugins_url( $path, $this->file );
	}

}