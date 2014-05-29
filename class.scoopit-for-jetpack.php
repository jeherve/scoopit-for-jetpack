<?php

if ( class_exists( 'Share_Twitter' ) && ! class_exists( 'Share_Scoopit' ) ) :

class Share_Scoopit extends Share_Twitter {
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

}

endif; // class_exists
