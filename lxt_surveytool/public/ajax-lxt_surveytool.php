<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_surveytool
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
			
		add_action( 'wp_ajax_nopriv_'.$this->plugin_slug.'_savesurvey', array( $this, 'ajax_save_survey') );
		add_action( 'wp_ajax_'.$this->plugin_slug.'_savesurvey', array( $this, 'ajax_save_survey') );	
	}

	public function ajax_save_survey() {
		global $wpdb;
		global $current_user;

		$table_name = $wpdb->prefix . $this->plugin_slug. '_surveys';
		$result = str_replace('\\', '', esc_sql ($_POST["result"]));
		$user = '';
		if (!is_user_logged_in()) {
			$email = esc_sql ($_POST["email"]);
		}else{
			get_currentuserinfo();
			$email = $current_user->user_email;
			$user = $current_user->user_login;
		}
		
		$wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'user' =>  $user, 'result' => $result, email => $email ) );
		echo __("Thank you for your participate", $this->plugin_slug);
		die();
	}
	public function add_plugin_shortcode() {
		add_shortcode( 'lxt_dosurvey', array( $this, 'lxt_dosurvey_shortcode') );
		add_shortcode( 'lxt_survey_ques', array( $this, 'lxt_survey_ques_shortcode') );
	}
}

