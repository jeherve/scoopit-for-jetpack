<?php
/*
 * Plugin Name: Scoop.it for Jetpack
 * Plugin URI: http://wordpress.org/plugins/scoopit-for-jetpack/
 * Description: Add a Scoop.it button to the Jetpack Sharing module
 * Author: Jeremy Herve
 * Version: 1.0
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
		}
	}

	public function setup() {
        add_filter( 'sharing_services', array( 'Share_Scoopit', 'inject_service' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ) );
    }

	// Add Javascript in the footer
	public function enqueue_script() {
		wp_enqueue_script( 'scoopit-js', ( is_ssl() ? 'https:' : 'http:' ) . '//www.scoop.it/button/scit.js', false, null, true );
	}
}

// Include Jetpack's sharing class, Sharing_Source
$share_plugin = wp_get_active_and_valid_plugins();
if ( is_multisite() ) {
	$share_plugin = array_unique( array_merge( $share_plugin, wp_get_active_network_plugins() ) );
}
$share_plugin = preg_grep( '/\/jetpack\.php$/i', $share_plugin );

if ( empty( $share_plugin ) ) {

	add_action( 'admin_notices', 'jp_scoopit_install_jetpack' );

	// Prompt to install Jetpack
	function jp_scoopit_install_jetpack() {
		echo '<div class="error"><p>';
		printf(__( 'To use the Scoop.it for Jetpack plugin, you\'ll need to install and activate <a href="%1$s">Jetpack</a> first, and <a href="%2$s">activate the Sharing module</a>.'),
		'plugin-install.php?tab=search&s=jetpack&plugin-search-input=Search+Plugins',
		'admin.php?page=jetpack_modules',
		'jp_scoopit_share'
		);
		echo '</p></div>';
	}

} elseif ( ! class_exists( 'Sharing_Source' ) ) {
	include_once( preg_replace( '/jetpack\.php$/i', 'modules/sharedaddy/sharing-sources.php', reset( $share_plugin ) ) );
}

// Build button
class Share_Scoopit extends Sharing_Source {
	var $shortname = 'scoopit';
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
	}

	public function get_name() {
		return __( 'Scoop.it', 'scoopit' );
	}

	public function get_display( $post ) {
		return '<a href="http://www.scoop.it" class="scoopit-button" scit-position="horizontal" scit-url="' . get_permalink( $post->ID ) . '" >Scoop.it</a>';
	}

	// Add the Scoopit Button to the list of services in Sharedaddy
	public function inject_service ( array $services ) {
		if ( ! array_key_exists( 'scoopit', $services ) ) {
			$services['scoopit'] = 'Share_Scoopit';
		}
		return $services;
	}
}

// And boom.
Scoopit_Button::get_instance();
