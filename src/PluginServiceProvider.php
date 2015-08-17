<?php

namespace Never5\LicenseWP;

use Never5\LicenseWP\License;

class PluginServiceProvider implements Pimple\ServiceProviderInterface {

	/**
	 * Registers services on the given container.
	 *
	 * This method should only be used to configure services and parameters.
	 * It should not get services.
	 *
	 * @param Pimple\Container $container An Container instance
	 */
	public function register( Pimple\Container $container ) {

		// license repository
		$container['license_repository'] = function() {
			return new License\WordPressRepository();
		};

		// license factory
		$container['license_factory'] = function () use($container) {
			return new License\Factory( $container['license_repository'] );
		};

		// license manager
		$container['license_manager'] = function () {
			return new License\Manager();
		};

	}

}