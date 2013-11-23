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
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_surveytool_menu_Admin {
	protected $version;
	protected $plugin_slug;
	protected $load_admin;

	public function __construct($slug, $ver, $load) {
		$this->plugin_slug = $slug;
		$this->version = $ver;	
		$this->load_admin = $load;

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
		
		$this->load_admin->set_screen_suffix($screen_hook_suffix);
	}

	public function display_survey_result_page() {
		include_once( 'views/admin.php' );
	}
}

