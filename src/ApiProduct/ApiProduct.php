<?php

namespace Never5\LicenseWP\ApiProduct;

/**
 * Class ApiProduct
 * @package Never5\LicenseWP\ApiProduct
 */
class ApiProduct {

	/** @var int */
	private $id = 0;

	/** @var string */
	private $name = '';

	/** @var string */
	private $slug = '';

	/** @var string */
	private $version = '';

	/** @var string */
	private $date = '';

	/** @var string */
	private $package = '';

	/** @var string */
	private $uri = '';

	/** @var string */
	private $author = '';

	/** @var string */
	private $author_uri = '';

	/** @var string */
	private $requires_at_least = '';

	/** @var string */
	private $tested_up_to = '';

	/** @var string */
	private $requires_php = '';

	/** @var string The release for sites that can't run the current one */
	private $legacy_version = '';

	/** @var string */
	private $legacy_package = '';

	/** @var string */
	private $legacy_requires_at_least = '';

	/** @var string */
	private $legacy_requires_php = '';

	/** @var string */
	private $legacy_tested_up_to = '';

	/** @var string */
	private $description = '';

	/** @var string */
	private $changelog = '';

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
	public function get_name() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * @param string $slug
	 */
	public function set_slug( $slug ) {
		$this->slug = $slug;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @param string $version
	 */
	public function set_version( $version ) {
		$this->version = $version;
	}

	/**
	 * @return string
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * @param string $date
	 */
	public function set_date( $date ) {
		$this->date = $date;
	}

	/**
	 * @return string
	 */
	public function get_package() {
		return $this->package;
	}

	/**
	 * @param string $package
	 */
	public function set_package( $package ) {
		$this->package = $package;
	}

	/**
	 * @return string
	 */
	public function get_uri() {
		return $this->uri;
	}

	/**
	 * @param string $uri
	 */
	public function set_uri( $uri ) {
		$this->uri = $uri;
	}

	/**
	 * @return string
	 */
	public function get_author() {
		return $this->author;
	}

	/**
	 * @param string $author
	 */
	public function set_author( $author ) {
		$this->author = $author;
	}

	/**
	 * @return string
	 */
	public function get_author_uri() {
		return $this->author_uri;
	}

	/**
	 * @param string $author_uri
	 */
	public function set_author_uri( $author_uri ) {
		$this->author_uri = $author_uri;
	}

	/**
	 * @return string
	 */
	public function get_requires_at_least() {
		return $this->requires_at_least;
	}

	/**
	 * @param string $requires_at_least
	 */
	public function set_requires_at_least( $requires_at_least ) {
		$this->requires_at_least = $requires_at_least;
	}

	/**
	 * @return string
	 */
	public function get_tested_up_to() {
		return $this->tested_up_to;
	}

	/**
	 * @param string $tested_up_to
	 */
	public function set_tested_up_to( $tested_up_to ) {
		$this->tested_up_to = $tested_up_to;
	}

	/**
	 * @return string
	 */
	public function get_requires_php() {
		return $this->requires_php;
	}

	/**
	 * @param string $requires_php
	 */
	public function set_requires_php( $requires_php ) {
		$this->requires_php = $requires_php;
	}

	/**
	 * @return string
	 */
	public function get_legacy_version() {
		return $this->legacy_version;
	}

	/**
	 * @param string $legacy_version
	 */
	public function set_legacy_version( $legacy_version ) {
		$this->legacy_version = $legacy_version;
	}

	/**
	 * @return string
	 */
	public function get_legacy_package() {
		return $this->legacy_package;
	}

	/**
	 * @param string $legacy_package
	 */
	public function set_legacy_package( $legacy_package ) {
		$this->legacy_package = $legacy_package;
	}

	/**
	 * @return string
	 */
	public function get_legacy_requires_at_least() {
		return $this->legacy_requires_at_least;
	}

	/**
	 * @param string $legacy_requires_at_least
	 */
	public function set_legacy_requires_at_least( $legacy_requires_at_least ) {
		$this->legacy_requires_at_least = $legacy_requires_at_least;
	}

	/**
	 * @return string
	 */
	public function get_legacy_requires_php() {
		return $this->legacy_requires_php;
	}

	/**
	 * @param string $legacy_requires_php
	 */
	public function set_legacy_requires_php( $legacy_requires_php ) {
		$this->legacy_requires_php = $legacy_requires_php;
	}

	/**
	 * @return string
	 */
	public function get_legacy_tested_up_to() {
		return $this->legacy_tested_up_to;
	}

	/**
	 * @param string $legacy_tested_up_to
	 */
	public function set_legacy_tested_up_to( $legacy_tested_up_to ) {
		$this->legacy_tested_up_to = $legacy_tested_up_to;
	}

	/**
	 * The release a site can run: the current one, else the legacy one, else none.
	 *
	 * An empty version of the site or an empty requirement counts as met, so sites that don't tell their versions get
	 * the current release, as before.
	 *
	 * @param string $wp_version  The WordPress version of the site.
	 * @param string $php_version The PHP version of the site.
	 *
	 * @return array|null Keys version, requires, requires_php, tested and legacy (bool); null when there is none.
	 */
	public function get_release( $wp_version = '', $php_version = '' ) {
		$releases = array(
			array(
				'version'      => $this->get_version(),
				'requires'     => $this->get_requires_at_least(),
				'requires_php' => $this->get_requires_php(),
				'tested'       => $this->get_tested_up_to(),
				'legacy'       => false,
			),
		);

		if ( '' !== $this->get_legacy_version() && '' !== $this->get_legacy_package() ) {
			$releases[] = array(
				'version'      => $this->get_legacy_version(),
				'requires'     => $this->get_legacy_requires_at_least(),
				'requires_php' => $this->get_legacy_requires_php(),
				'tested'       => $this->get_legacy_tested_up_to(),
				'legacy'       => true,
			);
		}

		foreach ( $releases as $release ) {
			$wp_ok  = '' === (string) $wp_version || '' === (string) $release['requires'] || version_compare( $wp_version, $release['requires'], '>=' );
			$php_ok = '' === (string) $php_version || '' === (string) $release['requires_php'] || version_compare( $php_version, $release['requires_php'], '>=' );

			if ( $wp_ok && $php_ok ) {
				return $release;
			}
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function get_changelog() {
		return $this->changelog;
	}

	/**
	 * @param string $changelog
	 */
	public function set_changelog( $changelog ) {
		$this->changelog = $changelog;
	}

	/**
	 * Get API product download URL
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param bool $legacy Link to the legacy release instead of the current one.
	 *
	 * @return string
	 */
	public function get_download_url( $license, $legacy = false ) {
		$args = array(
			'download_api_product' => $this->get_id(),
			'license_key'          => rawurlencode( $license->get_key() ),
			'activation_email'     => rawurlencode( $license->get_activation_email() )
		);

		if ( $legacy ) {
			$args['release'] = 'legacy';
		}

		return add_query_arg( $args, home_url( '/' ) );
	}
}