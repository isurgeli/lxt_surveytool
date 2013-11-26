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
 * Plugin class. This class used to do the common start work for a plugin.
 */
class lxt_jast_pub {
	protected $ver;
	protected $slug;
	protected $plugin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();
	}

	public function get_survey_container( $title, $context) {
		$loop = $this->get_post_loop( $title );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			if (!($style = get_post_meta( get_the_ID(), $this->slug . '_md_style', true )))
				$style = '';
			if (!($visibility = get_post_meta( get_the_ID(), $this->slug . '_md_visibility', true )))
				$visibility = __('All', $this->slug);
			if (!($link = get_post_meta( get_the_ID(), $this->slug . '_md_link', true )))
				$link = get_the_title();

			if ( !is_user_logged_in() && $visibility != __('All', $this->slug))
				return '';

			$nonce = wp_create_nonce($context);

			$output = '<div style="' . $style . '" class="'. $this->slug . '_popup" id="'. $this->slug . '_popup_' . $nonce . '" >';
			$output .= '<span class="' . $this->slug . '_popup_close"><span>X</span></span>';
			//$output .= 'If you can\'t get it up use<br><span class="lxt_logo">bPopup</span>';
			$output .= '<div id="' . $this->slug . '_popup_container_' . $nonce . '"></div>';
			$output .= '</div>';
			$output .= '<a href="#" class="' . $this->slug . '_popup_open" target="'. $nonce . '" postid="'.get_the_ID().'">' . $link . '</a>';
		}
		else {
			$output = '';
		}
 
		return $output;
	}

	public function get_survey_content( $postid ) {
		if (!($mailtitle = get_post_meta( get_the_ID(), $this->slug . '_md_mailtitle', true )))
			$mailtitle = __('Please supply your email:', $this->slug);

		if (!($mailtitleclass = get_post_meta( get_the_ID(), $this->slug . '_md_mailtitleclass', true )))
			$mailtitleclass = $this->slug . '_qust_title';

		if (!($mailinputclass = get_post_meta( get_the_ID(), $this->slug . '_md_mailinputclass', true )))
			$mailinputclass = $this->slug.'_qust';

		if (!($titleclass = get_post_meta( get_the_ID(), $this->slug . '_md_titleclass', true )))
			$titleclass = $this->slug . '_survey_title';

		$output = '<header class="' . $titleclass . '" >' . get_post_field('post_title', $postid) . '</header>';
		$output .= apply_filters('the_content', get_post_field('post_content', $postid) );
		if ( !is_user_logged_in() ) {
			$output .= '<label class="' . mailtitleclass . '" >'.mailtitle.'</label>';
			$output .= '<input class="' . mailinputclass . '" type="email" name="the_email"></input>';
		}
		return $output;
	}

	function get_post_loop( $title ) {
		$args = array(
			'post_type' => $this->slug,
			'orderby' => 'title',
			'post_status' => 'publish',
			'order' => 'ASC',
			'posts_per_page' => -1,
			'search_prod_title' => $title
		);
		if ( isset( $title) ) {
			require_once( plugin_dir_path( __FILE__ ).'../lib/lxt_public_lib.php' );
			add_filter( 'posts_where', array('lxt_public_lib', 'post_query_title_filter'), 10, 2 );
		}
		$loop = new WP_Query($args);

		if ( isset( $title) ) {
			remove_filter( 'posts_where', array('lxt_public_lib', 'post_query_title_filter'), 10, 2 );
		}

		return $loop;
	}
}

