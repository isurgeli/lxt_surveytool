<?php
/**
 * Plugin Name.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package Plugin_Name
 * @author  Your Name <email@example.com>
 */
class lxt_surveytool {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "plugin-name" to the name your your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'lxt_surveytool';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'init', array( $this, 'add_plugin_posttype' ) );
		add_action( 'init', array( $this, 'add_plugin_shortcode' ) );
		add_action( 'the_posts', array( $this, 'has_plugin_shortcode') );
		add_action( 'wp_ajax_nopriv_lxt_surveytool_savesurvey', array( 'lxt_surveytool', 'ajax_save_survey') );
		add_action( 'wp_ajax_lxt_surveytool_savesurvey', array( 'lxt_surveytool', 'ajax_save_survey') );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 *@return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
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
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
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
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
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
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
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
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
		If ( version_compare( get_bloginfo( 'version' ), '3.7', '<' ) ) {
			deactivate_plugins( 'lxt_surveytool' ); // Deactivate our plugin
	    }
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . 'languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		//wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
		//wp_enqueue_style( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', array(), '1.10.3' );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		//wp_enqueue_script( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array( 'jquery' ), '1.10.3' );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_posttype() {
		$survey_args = array(
			'public' => true,
			'query_var' => $this->plugin_slug,
			'show_in_nav_menus' => false,
			'rewrite' => array(
				'slug' => $this->plugin_slug,
				'with_front' => false),
	        'supports' => array(
		        'title',
			    'editor'
			),
			'labels' => array(
				'name' => __('Surveys', $this->plugin_slug),
		        'singular_name' => __('Survey', $this->plugin_slug),
				'add_new' => __('Add New Survey', $this->plugin_slug),
				'add_new_item' => __('Add New Survey', $this->plugin_slug),
				'edit_item' => __('Edit Survey', $this->plugin_slug),
				'new_item' => __('New Survey', $this->plugin_slug),
				'view_item' => __('View Survey', $this->plugin_slug),
				'search_items' => __('Search Surveys', $this->plugin_slug),
				'not_found' => __('No Surveys Found', $this->plugin_slug),
				'not_found_in_trash' => __('No Surveys Found In Trash', $this->plugin_slug)
			)
		);
 
		register_post_type( $this->plugin_slug, $survey_args );
	}

	public function ajax_save_survey() {
		echo "I'm here";
		die();
	}

	public function add_plugin_shortcode() {
		add_shortcode( 'lxt_dosurvey', array('lxt_surveytool', 'lxt_dosurvey_shortcode') );
		add_shortcode( 'lxt_survey_ques', array('lxt_surveytool', 'lxt_survey_ques_shortcode') );
	}

	public function lxt_survey_ques_shortcode($attr) {
		if (!$attr || !($key = $attr['key'])) return '';
		$title = $attr['title'];
		if (!($type = $attr['type'])) $type="text";
		if (($type == 'radio' || $type == 'checkbox') && !($answerstr = $attr['answer'])) return __('No option answer.');

		$output = '<p>'.$title.'</p>';
		if ($type != 'radio' && $type != 'checkbox') {
			$output .= '<p><input class="lxt_surveytool_field" type="'.$type.'" name="'.$key.'"></p>';
		}else{
			$answers = explode(";", $answerstr);
			$output .= '<p>';
			foreach ($answers as $answer){
				$output .= '<input class="lxt_surveytool_field" type="'.$type.'" name="'.$key.'" value="'.$answer.'">'.$answer.'<br/>';
			}
			$output .= '</p>';
		}

		return $output;
	}

	public function title_filter($where, &$wp_query)
    {
		global $wpdb;
        if ( $search_term = $wp_query->get( 'search_prod_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $search_term ) ) . '%\'';
        }
        return $where;
    }

	public function lxt_dosurvey_shortcode($attr) {
		if (!$attr || !($title = $attr['title'])) return '';
		$args = array(
			'post_type' => lxt_surveytool::get_instance()->plugin_slug,
			'orderby' => 'title',
			'post_status' => 'publish',
			'order' => 'ASC',
			'posts_per_page' => -1,
			'search_prod_title' => $title
		);
		add_filter( 'posts_where', array('lxt_surveytool', 'title_filter'), 10, 2 );
		$loop = new WP_Query($args);
		remove_filter( 'posts_where', 'title_filter', 10, 2 );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			$ajaxurl = admin_url().'admin-ajax.php';
			$output = '<div ajaxurl="'.$ajaxurl.'" class="lxt_survey_dialog" id="lxt_survey_dialog'.get_the_ID().'" title="'.get_the_title('', '', false).'">';
			$output .= do_shortcode(get_the_content());
			$output .= '</div>';
			$output .= '<a href="#" class="lxt_survey_dialog_opener" dialog="lxt_survey_dialog'.get_the_ID().'">'.get_the_title('', '', false).'</a>';
		}
		else {
			$output = '';
		}
 
		return $output;
	}

	function has_plugin_shortcode($posts) {
		if ( empty($posts) )
			return $posts;

		$found = false;

		foreach ($posts as $post) {
			if ( stripos($post->post_content, '[lxt_dosurvey') ) {
				$found = true;
				break;
			}
		}

		if ($found){
			wp_enqueue_style( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css', array(), '1.10.3' );
			wp_enqueue_script( 'jquery ui', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array( 'jquery' ), '1.10.3' );
			wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );	
			wp_localize_script( $this->plugin_slug . '-plugin-script', 'lxt_surveytool_L10n', array(
				'submit' => __( 'Submit', $this->plugin_slug ),
		        'cancel' => __( 'Cancel', $this->plugin_slug ),
			));
		}
		return $posts;
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
