<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_jast_Admin
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_jast_menu_Admin {
	protected $ver;
	protected $slug;
	protected $plugin;
	protected $admin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->admin = lxt_jast_plugin_Admin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
	}

	public function add_plugin_admin_menu() {

		$screen_hook_suffix = add_submenu_page( 
			'edit.php?post_type='.$this->plugin_slug, 
			__( 'View survey results', $this->plugin_slug ),
			__( 'Survey results', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug.'_results',
			array( $this, 'display_survey_result_page' )
		);
		
		do_action('lxt_set_admin_screen_id', $screen_hook_suffix);
	}

	public function display_survey_result_page() {
		include_once( 'views/admin.php' );
	}
}

