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
 * Plugin class. This class used to do the shortcode parse work for public plugin work.
 */
class lxt_jast_shortcode {
	protected $shortcodes = ['lxt_dosurvey', 'lxt_surveyret', 'lxt_survey_qust', 'lxt_survey_submit', 'lxt_survey_email'];

	protected $ver;
	protected $slug;
	protected $plugin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();

		add_action( 'init', array( $this, 'add_plugin_shortcode' ) );
	}

	public function get_shortcodes() {
		return $this->shortcodes;
	}

	public function add_plugin_shortcode() {
		foreach ($this->shortcodes as $shortcode) {
			add_shortcode( $shortcode, array( $this, $shortcode) );
		}
	}

	public function lxt_surveyret($attr) {
		extract( shortcode_atts( array(
			'title' => null,
			'key' => null,
			'width' => '300px',
			'high' => '300px',
			'type' => 'pie'
		), $attr ) );
		
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

			$cur_key = $qust_attr['key'];
		
			/*if (!isset ( $key ) ) {
				if (!($lcalss = $qust_attr['label-class'])) 
					$lcalss=$this->slug . '_qust_title';
				$qust_title = $qust_attr['title'];
				$output .= '<label class="' . $lcalss . '" >' . $qust_title . '</label>';
			}*/
			$qust_title = $qust_attr['title'];
			$output.= '<div type="' .$this->slug . '_' .$type . '" class="'. $this->slug .'_result_img" id="' . $this->slug . '_retimg_' . $post_id . '_' . $cur_key . '_' 
				. wp_create_nonce( get_the_id() ) . '" style="height:' . $high .';width:' . $width .'; ">' . $qust_title . '</div>';
		}
		
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
				preg_match_all ('/\['.$this->shortcodes[2].'[^\]]+\]/', $content, $pat_array);
			else
				preg_match_all ('/\['.$this->shortcodes[2].'[^\]]+key="'.$key.'"[^\]]+\]/', $content, $pat_array);


			$ret = ['id' => $post_id, 'qust' => $pat_array[0]];
		}else{
			$ret = [];
		}
		wp_reset_postdata();
		return $ret;
	}

	public function lxt_survey_qust($attr) {
		extract( shortcode_atts( array(
			'key' => null,
			'title' => null,
			'type' => 'text',
			'lcalss' => $this->slug . '_qust_title',
			'iclass' => $this->slug.'_qust'
		), $attr ) );

		if ($key == null) 
			return '';

		if ($iclass != $iclass=$this->slug.'_qust') 
			$iclass .= ' ' . $this->slug.'_qust';

		if (($type == 'radio' || $type == 'checkbox') && !($answerstr = $attr['answer'])) 
			return __('Error, no option answer.', $this->slug);

		$output = '<label class="' . $lcalss . '" >' . $title . '</label>';
		if ($type != 'radio' && $type != 'checkbox') {
			$output .= '<input class="' . $iclass . '" type="' . $type . '" name="' . $key . '" />';
		}else{
			$answers = explode(";", $answerstr);
			foreach ($answers as $answer) {
				$output .= '<label class="' . $iclass . '" ><input class="' . $iclass . '" ';
				if ($answers[0] == $answer && $type == 'radio')
					$output .= 'checked ';
			   	$output .= 'type="' . $type . '" name="' . $key . '" value="' . $answer . '" />' . $answer . '</label>';
			}
		}

		return $output;
	}

	public function lxt_survey_submit($attr) {
		extract( shortcode_atts( array(
			'title' => __('Submit' , $this->slug),
			'class' => $this->slug . '_submit'
		), $attr ) );

		if ($class != $this->slug . '_submit') 
			$class .= ' ' . $this->slug . '_submit';

		$output = '<button type="button" class="' . $class . '">'. $title . '</button>';

		return $output;
	}

	public function lxt_survey_email($attr) {
		extract( shortcode_atts( array(
			'title' => __('Please supply your email:' , $this->slug),
			'tclass' => $this->slug . '_qust_title',
			'iclass' => $iclass=$this->slug . '_qust'
		), $attr ) );

		if ( !is_user_logged_in() ) {
			$output .= '<label class="' . $tclass . '" >' . $title . '</label>';
			$output .= '<input class="' . $iclass . '" type="email" name="the_email"></input>';
		}

		return $output;
	}


	public function lxt_dosurvey($attr) {
		if (!$attr || !($title = $attr['title'])) return '';

		return $this->plugin->get_pub_obj()->get_survey_container( $title, 'shortcode' );
	}
}
