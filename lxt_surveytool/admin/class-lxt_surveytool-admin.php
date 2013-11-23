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
 * Plugin class. This class used do the admin init work
 */
class lxt_surveytool_Admin {

	protected static $instance = null;
	protected $plugin_slug = null;
	protected $version = null;

	private function __construct() {

		$plugin = lxt_surveytool::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->version = lxt_surveytool::VERSION;

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		require_once( plugin_dir_path( __FILE__ ) . 'ajax-'.$this->plugin_slug.'-admin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'load-'.$this->plugin_slug.'-admin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'post-'.$this->plugin_slug.'-admin.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'menu-'.$this->plugin_slug.'-admin.php' );

		$load_admin = new lxt_surveytool_load_Admin($this->plugin_slug, $this->version);
		new lxt_surveytool_menu_Admin($this->plugin_slug, $this->version, $load_admin);
		new lxt_surveytool_ajax_Admin($this->plugin_slug, $this->version);
		new lxt_surveytool_post_Admin($this->plugin_slug, $this->version);
		
	}

	public static function get_instance() {

		/*
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url('edit.php?post_type='.$this->plugin_slug.'&page=' . $this->plugin_slug.'_results' ) . '">' . __( 'Results', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}
}
