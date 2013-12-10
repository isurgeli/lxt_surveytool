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
			$output .= '<a href="javascript:void(0)" class="' . $this->slug . '_popup_open" target="'. $nonce . '" postid="'.get_the_ID().'">' . $link . '</a>';
		}
		else {
			$output = '';
		}
		wp_reset_postdata(); 
		return $output;
	}

	public function get_survey_content( $postid ) {
		if (!($titleclass = get_post_meta( $postid, $this->slug . '_md_titleclass', true )))
			$titleclass = $this->slug . '_survey_title';

		$output = '<header class="' . $titleclass . '" >' . get_post_field('post_title', $postid) . '</header>';
		$output .= apply_filters('the_content', get_post_field('post_content', $postid) );
		
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

	public function get_survey_chart_frame($title, $key, $type, $width, $high) {		
		if ($title == null) return '';

		$post_data = $this->get_survey_questions( $title , $key );

		$post_id = $post_data['id'];
		$questions = $post_data['qust'];
		$output = '';
		foreach ($questions as $question) {
			preg_match_all ('/\s+([^"]+)="([^"]+)"/', $question, $pat_array);
			$qust_attr = [];
			for ($i = 0; $i < count($pat_array[0]); $i++) {
				$qust_attr[$pat_array[1][$i]] = $pat_array[2][$i];
			}

			if ( ( strtolower( $qust_attr['type'] ) != 'radio' && strtolower( $qust_attr['type'] ) != 'checkbox' )
				|| !isset($qust_attr['answer']) ) {
				continue;
			}

			if ( isset( $type ) ) {
				$qust_type = $type;
			}else{
				if ($qust_attr['type'] == 'radio')
					$qust_type = 'pie';
				else
					$qust_type = 'bar';
			}


			$cur_key = $qust_attr['key'];
		
			$qust_title = $qust_attr['title'];
			$output.= '<div type="' .$this->slug . '_' .$qust_type . '" class="'. $this->slug .'_result_img" id="' . $this->slug . '_retimg_' . $post_id . '_' . $cur_key . '_' 
				. wp_create_nonce( get_the_id() ) . '" style="height:' . $high .';width:' . $width .'; " title="' . $qust_title . '" ></div>';
		}
		
		return $output;
	}

	public function get_survey_text_frame($title) {
		if ($title == null) return '';

		$post_data = $this->get_survey_questions( $title , null );

		$post_id = $post_data['id'];
		$questions = $post_data['qust'];
		$output = '<div class="lxt_jast_select_qust"><span class="lxt_jast_select_action">';
		$output .= __('Please select the question: ', $this->slug); 
		$output .= '<select id="' . $this->slug . '_survey_qust" >';
		$output .= '<option value=""></option>';
	
		foreach ($questions as $question) {
			preg_match_all ('/\s+([^"]+)="([^"]+)"/', $question, $pat_array);
			$qust_attr = [];
			for ($i = 0; $i < count($pat_array[0]); $i++) {
				$qust_attr[$pat_array[1][$i]] = $pat_array[2][$i];
			}

			if ( ( strtolower( $qust_attr['type'] ) != 'radio' && strtolower( $qust_attr['type'] ) != 'checkbox' ) ) {
				$output .=  '<option value="'.$qust_attr['key'].'">'.$qust_attr['title'].'</option>';
			}
		}
		$output .= '</select>';
		$output .= '</span></div>';
		$output .= '<div class="lxt_jast_result_table" id="' . $this->slug . '_rettable_' . $post_id . '" />';
		return $output;
	}

	public function get_survey_questions( $title, $key ) {
		$loop = $this->plugin->get_pub_obj()->get_post_loop( $title );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			$content = get_the_content();
			$post_id = get_the_id();

			$pat_array = null;
			if ( !isset ( $key ) ) 
				preg_match_all ('/\['.$this->plugin->get_shortcodes()[2].'[^\]]+\]/', $content, $pat_array);
			else
				preg_match_all ('/\['.$this->plugin->get_shortcodes()[2].'[^\]]+key="'.$key.'"[^\]]+\]/', $content, $pat_array);


			$ret = ['id' => $post_id, 'qust' => $pat_array[0]];
		}else{
			$ret = [];
		}
		wp_reset_postdata();
		return $ret;
	}
}

