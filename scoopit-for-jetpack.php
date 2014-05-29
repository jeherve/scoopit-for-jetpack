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

// Build button
class Share_Scoopit extends Sharing_Source {
	var $shortname = 'scoopit';
	public function __construct( $id, array $settings ) {
		parent::__construct( $id, $settings );
		$this->smart = 'official' == $this->button_style;
		$this->icon = 'icon' == $this->button_style;
		$this->button_style = 'icon-text';
	}

	public function get_name() {
		return __( 'Scoop.it', 'jp_scoopit_share' );
	}

	// If we use the non-official button, add custom styles to the head
	public function display_header() {
		if( ! $this->smart ) {
?>
<style type="text/css">
	.sd-social-icon-text li.share-scoopit a.sd-button > span {
		background: url('<?php echo plugins_url( 'scoopit.png', __FILE__ ); ?>') no-repeat;
		padding-left: 20px;
	}

	.sd-social-icon .sd-content ul li[class*='share-'].share-scoopit a.sd-button {
		background: #6cab36 url('<?php echo plugins_url( 'scoopit-white.png', __FILE__ ); ?>') no-repeat;
		color: #fff !important;
		width: 16px;
		height: 16px;
		top: 16px;
	}
</style>
<?php
		}
	}

	// If we use the official button, load the scoopit library in the footer
	public function display_footer() {
		if ( $this->smart ) {
?>
<script type="text/javascript" src="//www.scoop.it/button/scit.js"></script>
<?php
		}
	}

	public function get_display( $post ) {
		if ( $this->smart ) {
			return '<a href="http://www.scoop.it" class="scoopit-button" scit-position="horizontal" scit-url="' . get_permalink( $post->ID ) . '" >Scoop.it</a>';
		} else if ( $this->icon ) {
			return '<a target="_blank" rel="nofollow" class="share-scoopit sd-button share-icon" href="https://www.scoop.it/bookmarklet?url='. get_permalink( $post->ID ) .'"><span></span></a>';
		} else {
			return '<a target="_blank" rel="nofollow" class="share-scoopit sd-button share-icon" href="https://www.scoop.it/bookmarklet?url='. get_permalink( $post->ID ) .'"><span>Scoop.it</span></a>';
		}
	}

	// Add the Scoopit Button to the list of services in Sharedaddy
	public function inject_service ( array $services ) {
		if ( ! array_key_exists( 'scoopit', $services ) ) {
			$services['scoopit'] = 'Share_Scoopit';
		}
		return $services;
	}
}

} // End check for Sharing_Source

// And boom.
Scoopit_Button::get_instance();
