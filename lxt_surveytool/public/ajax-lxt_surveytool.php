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
 * Plugin class. This class used to do the ajax work for public plugin work.
 */

class lxt_surveytool_ajax {
	protected $version;
	protected $plugin_slug;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;
			
		add_action( 'wp_ajax_nopriv_lxt_surveytool_savesurvey', array( $this, 'ajax_save_survey') );
		add_action( 'wp_ajax_lxt_surveytool_savesurvey', array( $this, 'ajax_save_survey') );	
	}

	public function ajax_save_survey() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'lxt_surveytool_surveys';
		$result = str_replace('\"', '', $_POST["result"]);
		$wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'user' => '', 'result' => $result, email => '' ) );
		echo __("Thank you for your participate", $this->plugin_slug);
		die();
	}
}
