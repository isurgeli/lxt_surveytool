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
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_jast_post {
	protected $meta = null;
	protected $ver;
	protected $slug;
	protected $plugin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();

		add_action( 'init', array( $this, 'add_plugin_posttype' ) );
		if (! is_admin()) {
			add_action( 'the_posts', array( $this, 'post_has_shortcode') );
		}

		$this->meta = ['class'	=> __('Popup window class', $this->slug), 
					'visibility'	=> __('Survey visibility', $this->slug), 
					'linktext'		=> __('Popup link text', $this->slug),
					'linkclass'		=> __('Popup link class', $this->slug),
					'closeclass'	=> __('Popup close button class', $this->slug),
					'perpage'		=> __('Survey results per page', $this->slug)];
	}

	public function get_post_meta() {
		return $this->meta;
	}

	public function add_plugin_posttype() {
		$survey_args = array(
			'public' => true,
			'query_var' => $this->slug,
			'show_in_nav_menus' => false,
			'rewrite' => array(
				'slug' => $this->slug,
				'with_front' => false),
	        'supports' => array(
		        'title',
			    'editor'
			),
			'labels' => array(
				'name' => __('Surveys', $this->slug),
		        'singular_name' => __('Survey', $this->slug),
				'add_new' => __('Add New Survey', $this->slug),
				'add_new_item' => __('Add New Survey', $this->slug),
				'edit_item' => __('Edit Survey', $this->slug),
				'new_item' => __('New Survey', $this->slug),
				'view_item' => __('View Survey', $this->slug),
				'search_items' => __('Search Surveys', $this->slug),
				'not_found' => __('No Surveys Found', $this->slug),
				'not_found_in_trash' => __('No Surveys Found In Trash', $this->slug)
			)
		);
 
		register_post_type( $this->slug, $survey_args );
	}

	function post_has_shortcode( $posts ) {
		if ( empty($posts) )
			return $posts;

		$shortcodes = $this->plugin->get_shortcodes();
		foreach ($posts as $post) {
			foreach ($shortcodes as $shortcode) {
				preg_match ('/\['.$shortcode.'[\s\]]/', $post->post_content, $pat_array);

				if ( is_array($pat_array) && !empty($pat_array) && count($pat_array[0]) > 0 ) {
					do_action($this->slug . '_post_has_shortcode', $shortcode);
					break;
				}
			}
		}

		return $posts;
	}
}

