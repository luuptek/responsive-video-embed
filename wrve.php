<?php
/**
 * Plugin Name: Responsive video embed
 * Plugin URI: http://twitter.com/lepileppanen
 * Description: Embed videos to your content responsively.
 * Version: 0.3
 * Author: Timo Leppänen
 * Author URI: https://wordpress.org/support/profile/lepileppanen
 * License: GPLv2
 */

/**
  * Security Note:
  * Consider blocking direct access to your plugin PHP files by adding the following line at the top of each of them,
  * or be sure to refrain from executing sensitive standalone PHP code before calling any WordPress functions.
  */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
  * Class to build the whole plugin
  */
class Rve {

	protected static $instance = null;

 	function __construct()
 	{
		add_action( 'init', [ $this, 'initializeHooks' ] );
 	}

	public static function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
	}

	/**
	 * Create hooks here
	 */
	public function initializeHooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'getStyles' ] );
		add_shortcode( 'rve', [ $this, 'embedShortcode' ] );
		add_action( 'admin_head', [ $this, 'registerTinyMCEButtons' ] );
		add_filter('embed_oembed_html', [ $this, 'registerEmbedHtml' ], 99, 4);
	}

	/**
	 * Style register_setting
	 */
	public function getStyles() {
		wp_enqueue_style( 'wrve-css', plugins_url( 'css/rve.min.css', __FILE__ ) );
	}

	/**
	 * Create the actual shortcode
	 */
	public function embedShortcode( $atts, $content = null ) {
		$src = isset( $atts['src'] ) ? $atts['src']: '';
		$ratio = isset( $atts['ratio'] ) && ( $atts['ratio'] == '16by9' || $atts['ratio'] == '4by3' ) ? $atts['ratio']: '16by9';
		$markUp = '';

		$markUp = <<<EOT
		<div class="embed-responsive embed-responsive-${ratio}">
			<iframe class="embed-responsive-item" src="${src}" allowfullscreen></iframe>
		</div>
EOT;

		return $markUp;
	}

	/**
	 * Register TinyMCE buttons
	 */
	public function registerTinyMCEButtons() {
		// check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
			return;
    }

    if ( get_user_option('rich_editing') == 'true') {
        add_filter( 'mce_external_plugins', [ $this, 'setRveButtonJs' ] );
        add_filter( 'mce_buttons', [ $this, 'registerButtons' ] );
    }
	}

	public function setRveButtonJs() {
		$plugin_array['rve_button'] = plugins_url( 'js/rve-button.min.js', __FILE__ );
    return $plugin_array;
	}

	public function registerButtons($buttons) {
		array_push($buttons, "rve_button");
		return $buttons;
	}

	/**
	 * Function to wrap video into embed-container
	 */
	 function registerEmbedHtml($html, $url, $attr, $post_id) {
		 return '<div class="embed-responsive embed-responsive-16by9">' . $html . '</div>';
	 }

 }

Rve::getInstance();
