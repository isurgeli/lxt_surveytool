<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_survey_Admin
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the load script and css work for public plugin work.
 */

class lxt_surveytool_load_Admin {
	protected $version;
	protected $plugin_slug;
	protected $screen_hook_suffix;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;

		// Load admin style sheet and JavaScript.
		//add_action( 'admin_enqueue_scripts', array( $this, 'common_enqueue_admin_styles' ) );
		//add_action( 'admin_enqueue_scripts', array( $this, 'common_enqueue_admin_scripts' ) );
	}

	public function common_enqueue_admin_styles() {

		if ( ! isset( $this->screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), $this->version );
		}

	}

	public function common_enqueue_admin_scripts() {

		if ( ! isset( $this->screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->screen_hook_suffix == $screen->id ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}
	}

	public function set_screen_suffix($screen) {
		$this->screen_hook_suffix = $screen;
	}
}
