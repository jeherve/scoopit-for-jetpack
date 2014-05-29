<?php
/*
 * Plugin Name: Scoop.it for Jetpack
 * Plugin URI: http://wordpress.org/plugins/scoopit-for-jetpack/
 * Description: Add a Scoop.it button to the Jetpack Sharing module
 * Author: Jeremy Herve
 * Version: 1.2
 * Author URI: http://jeremyherve.com
 * License: GPL2+
 * Text Domain: jp_scoopit_share
 */

class Scoopit_Button {
	private static $instance;

	static function get_instance() {
		if ( ! self::$instance )
			self::$instance = new Scoopit_Button;

		return self::$instance;
	}

	private function __construct() {
		// Check if Jetpack and the sharing module is active
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'sharedaddy' ) ) {
			add_action( 'plugins_loaded', array( $this, 'setup' ) );
		} else {
			add_action( 'admin_notices',  array( $this, 'install_jetpack' ) );
		}
	}

	public function setup() {
		add_filter( 'sharing_services', array( $this, 'inject_service' ) );
	}

	// Add the Scoopit Button to the list of services in Sharedaddy
	public function inject_service ( $services ) {
		include_once 'class.scoopit-for-jetpack.php';
		if ( class_exists( 'Share_Scoopit' ) ) {
			$services['scoopit'] = 'Share_Scoopit';
		}
		return $services;
	}

	// Prompt to install Jetpack
	public function install_jetpack() {
		echo '<div class="error"><p>';
		printf(__( 'To use the Scoop.it for Jetpack plugin, you\'ll need to install and activate <a href="%1$s">Jetpack</a> first, and <a href="%2$s">activate the Sharing module</a>.'),
		'plugin-install.php?tab=search&s=jetpack&plugin-search-input=Search+Plugins',
		'admin.php?page=jetpack_modules',
		'jp_scoopit_share'
		);
		echo '</p></div>';
	}

}
// And boom.
Scoopit_Button::get_instance();
