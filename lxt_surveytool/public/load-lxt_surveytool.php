<?php
/**
 * Just another survey tool.
 *
 * @package   Just another survey tool
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the load script and css work for public plugin work.
 */

class lxt_surveytool_load {
	protected $version;
	protected $plugin_slug;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;

		//add_action( 'wp_enqueue_scripts', array( $this, 'common_enqueue_styles' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'common_enqueue_scripts' ) );	
	}

	public function common_enqueue_styles() {
		wp_enqueue_style( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', array(), '1.10.3' );
		wp_enqueue_script( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array( 'jquery' ), '1.10.3' );
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), $this->version );
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), $this->version );	
		wp_localize_script( $this->plugin_slug . '-plugin-script', $this->plugin_slug.'_L10n', array(
			'submit' => __( 'Submit', $this->plugin_slug ),
		    'cancel' => __( 'Cancel', $this->plugin_slug ),
		));	
	}
}
