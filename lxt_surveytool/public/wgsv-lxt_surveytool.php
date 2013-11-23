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
 * Plugin class. This class used to do the widget work for public plugin work.
 * Survey link widget.
 */
class lxt_surveytool_wgsv extends WP_Widget {
	protected $version;
	protected $plugin_slug;

	public static function init() {
		add_action( 'widgets_init', array ( 'lxt_surveytool_wgsv', 'register_widget' ));
	}
	
	public function __construct() {
		$surveytool = lxt_surveytool::get_instance();
		$this->plugin_slug = $surveytool->get_plugin_slug();
		$this->version = lxt_surveytool::VERSION;	

		parent::__construct(
			$this->plugin_slug.'wgsv', // Base ID
			__('Survey panel', $this->plugin_slug), // Name
			array( 'description' => __( 'A widget can contain survey links.', $this->plugin_slug ), ) // Args
		);
	}

	public function register_widget() {
		register_widget( 'lxt_surveytool_wgsv' );
	}
 
    //build the widget settings form
    public function form($instance) {
        $defaults = array( 'title' => '' );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
		echo '<p>'._e('Survey:', $this->plugin_slug).'<select name="'.$this->get_field_name( 'title' ).'" >';
		$args = array(
			'post_type' => $this->plugin_slug,
			'orderby' => 'title',
			'post_status' => 'publish',
			'order' => 'ASC',
			'posts_per_page' => -1
		);
		$loop = new WP_Query($args);
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) {
				$loop->the_post();
				echo '<option value="'.get_the_title().'" '.selected( $title, get_the_title() ).' >'.get_the_title().'</option>';
			}
		}
        echo '</select></p>';
    }
 
    //save the widget settings
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
 
        return $instance;
    }
 
    //display the widget
    function widget($args, $instance) {
        extract($args);

		$title = apply_filters( 'widget_title', __('Surveys panel', $this->plugin_slug) );
        $surveytitle = $instance['title'];

		$scObj = new lxt_surveytool_shortcode($this->plugin_slug, $this->version);
		
		$output = $scObj->getSurveyOutput($surveytitle, 'widget' );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo $output;
		echo $after_widget;
    }
}
