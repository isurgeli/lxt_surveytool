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
 * Plugin class. This class used to do the shortcode parse work for public plugin work.
 */
class lxt_surveytool_shortcode {
	protected $version;
	protected $plugin_slug;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;	

		add_action( 'init', array( $this, 'add_plugin_shortcode' ) );
	}

	public function add_plugin_shortcode() {
		add_shortcode( 'lxt_dosurvey', array( $this, 'lxt_dosurvey_shortcode') );
		add_shortcode( 'lxt_survey_ques', array( $this, 'lxt_survey_ques_shortcode') );
	}

	public function lxt_survey_ques_shortcode($attr) {
		if (!$attr || !($key = $attr['key'])) 
			return '';
		$title = $attr['title'];
		if (!($type = $attr['type'])) 
			$type="text";
		if (($type == 'radio' || $type == 'checkbox') && !($answerstr = $attr['answer'])) 
			return __('No option answer.');

		$output = '<p>'.$title.'</p>';
		if ($type != 'radio' && $type != 'checkbox') {
			$output .= '<p><input class="'.$this->plugin_slug.'_field" type="'.$type.'" name="'.$key.'"></p>';
		}else{
			$answers = explode(";", $answerstr);
			$output .= '<p>';
			foreach ($answers as $answer){
				$output .= '<input class="'.$this->plugin_slug.'_field" ';
				if ($answers[0] == $answer && $type == 'radio')
					$output .= 'checked ';
			   	$output .= 'type="'.$type.'" name="'.$key.'" value="'.$answer.'">'.$answer.'<br/>';
			}
			$output .= '</p>';
		}

		return $output;
	}

	public function lxt_dosurvey_shortcode($attr) {
		if (!$attr || !($title = $attr['title'])) return '';

		return $this->getSurveyOutput( $title, 'shortcode' );
	}

	public function getSurveyOutput( $title, $context ) {
		$args = array(
			'post_type' => $this->plugin_slug,
			'orderby' => 'title',
			'post_status' => 'publish',
			'order' => 'ASC',
			'posts_per_page' => -1,
			'search_prod_title' => $title
		);
		require_once( plugin_dir_path( __FILE__ ).'../lib/lxt_public_lib.php' );
		add_filter( 'posts_where', array('lxt_public_lib', 'post_query_title_filter'), 10, 2 );
		$loop = new WP_Query($args);
		remove_filter( 'posts_where', array('lxt_public_lib', 'post_query_title_filter'), 10, 2 );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			if (!($lxt_st_width = get_post_meta( get_the_ID(), '_lxt_st_width', true )))
				$lxt_st_width = 300;
			if (!($lxt_st_height = get_post_meta( get_the_ID(), '_lxt_st_height', true )))
				$lxt_st_height = 350;
			if (!($lxt_st_visibility = get_post_meta( get_the_ID(), '_lxt_st_visibility', true )))
				$lxt_st_visibility = __('All', $this->plugin_slug);

			if ( !is_user_logged_in() && $lxt_st_visibility != __('All', $this->plugin_slug))
				return '';

			$nonce = wp_create_nonce($context);

			$ajaxurl = admin_url().'admin-ajax.php';
			$output = '<div w="'.$lxt_st_width.'" h="'.$lxt_st_height.'" ajaxurl="'.$ajaxurl.'" class="lxt_survey_dialog" id="lxt_survey_dialog'.$nonce.'" title="'.get_the_title('', '', false).'">';
			$output .= do_shortcode( get_the_content() );
			if ( !is_user_logged_in() ) {
				$output .= '<p>'.__('Email').'</p>';
				$output .= '<p><input class="'.$this->plugin_slug.'_field" type="email" name="the_email"></p>';
			}
			$output .= '</div>';
			$output .= '<a href="#" class="lxt_survey_dialog_opener" dialog="lxt_survey_dialog'.$nonce.'">'.get_the_title('', '', false).'</a>';
		}
		else {
			$output = '';
		}
 
		return $output;
	}

}
