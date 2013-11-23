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
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_surveytool_post {
	protected $version;
	protected $plugin_slug;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;	
		add_action( 'init', array( $this, 'add_plugin_posttype' ) );
		add_action( 'the_posts', array( $this, 'post_has_plugin_shortcode') );
	}

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

	function post_has_plugin_shortcode($posts) {
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
			$load = new lxt_surveytool_load($this->plugin_slug, $this->version);
			$load->common_enqueue_styles();
		}
		return $posts;
	}
}

