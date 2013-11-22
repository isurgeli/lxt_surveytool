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
 * Plugin class. This class used to do the common start work for a plugin.
 */
class lxt_surveytool {

	const VERSION = '1.0.0';

	protected $plugin_slug = 'lxt_surveytool';

	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		require_once( plugin_dir_path( __FILE__ ) . 'ajax-lxt_surveytool.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'load-lxt_surveytool.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'post-lxt_surveytool.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'shortcode-lxt_surveytool.php' );

		new lxt_surveytool_ajax($this->plugin_slug, self::VERSION);
		new lxt_surveytool_load($this->plugin_slug, self::VERSION);
		new lxt_surveytool_post($this->plugin_slug, self::VERSION);
		new lxt_surveytool_shortcode($this->plugin_slug, self::VERSION);
	}

	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
		If ( version_compare( get_bloginfo( 'version' ), '3.7', '<' ) ) {
			deactivate_plugins( 'lxt_surveytool' ); // Deactivate our plugin
		}

		global $wpdb;

		$table_name = $wpdb->prefix . "lxt_surveytool_surveys";
      
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			user VARCHAR(50) DEFAULT '' NOT NULL,
			result text NOT NULL,
			email VARCHAR(50) DEFAULT '' NOT NULL,
			UNIQUE KEY id (id)
		);";


		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( "lxt_survey_db_version", self::VERSION );
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . 'languages/' );

	}
}
