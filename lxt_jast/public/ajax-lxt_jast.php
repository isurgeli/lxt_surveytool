<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_jast
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the ajax work for public plugin work.
 */

class lxt_jast_ajax {
	protected $ver;
	protected $slug;
	protected $plugin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();
			
		add_action( 'wp_ajax_nopriv_'.$this->slug.'_savesurvey', array( $this, 'ajax_save_survey') );
		add_action( 'wp_ajax_'.$this->slug.'_savesurvey', array( $this, 'ajax_save_survey') );

		add_action( 'wp_ajax_nopriv_'.$this->slug.'_loadsurvey', array( $this, 'ajax_load_survey') );
		add_action( 'wp_ajax_'.$this->slug.'_loadsurvey', array( $this, 'ajax_load_survey') );	
	}

	public function ajax_load_survey() {
		$postid = $_POST["id"];
	
		echo $this->plugin->get_pub_obj()->get_survey_content( $postid );
		die();
	}

	public function ajax_save_survey() {
		global $wpdb;
		global $current_user;

		$table_name = $wpdb->prefix . $this->slug. '_surveys';
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
		echo __("Thank you for your participate", $this->slug);
		die();
	}
}

