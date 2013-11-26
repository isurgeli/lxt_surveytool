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
	protected $shortcodes = ['lxt_dosurvey' => 'lxt_dosurvey', 'lxt_surveyret' => 'lxt_surveyret', 'lxt_survey_qust' => 'lxt_survey_qust', 'lxt_survey_submit' => 'lxt_survey_submit'];

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
		foreach ($this->shortcodes as $shortcode => $fun) {
			add_shortcode( $shortcode, array( $this, $fun) );
		}
	}

	public function lxt_surveyret($attr) {
		if (!$attr || !($title = $attr['title'])) return '';
		$key = $attr['key'];

		$questions = $this->get_survey_questions( $title , $key );
		foreach ($questions as $question) {
			preg_match_all ('/(\w+)="(\w+)"/', $question, $pat_array);
			for ($i = 0; $i < count($pat_array[0]); $i++) {
			    $attr[$pat_array[1][i]] = $pat_array[2][i];
			}


		}

	}

	public function get_survey_questions( $title, $key ) {
		$loop = $this->plugin->get_pub_obj()->get_post_loop( $title );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			$content .= get_the_content();

			if ( isset ( $key ) ) 
				preg_match_all ('/\['.array_keys($this->shortcodes)[2].'[^\]]+\]/', $content, $pat_array);
			else
				preg_match_all ('/\['.array_keys($this->shortcodes)[2].'[^\]]+key="'.$key.'"[^\]]+\]/', $content, $pat_array);

			return $pat_array[0];
		}

		return [];
	}

	public function lxt_survey_qust($attr) {
		if (!$attr || !($key = $attr['key'])) 
			return '';

		$title = $attr['title'];

		if (!($type = $attr['type'])) 
			$type="text";

		if (!($lcalss = $attr['label-class'])) 
			$lcalss=$this->slug . '_qust_title';

		if (!($iclass = $attr['input-class'])) 
			$iclass=$this->slug.'_qust';
		else
			$iclass .= ' ' . $this->slug.'_qust';

		if (($type == 'radio' || $type == 'checkbox') && !($answerstr = $attr['answer'])) 
			return __('Error, no option answer.', $this->slug);

		$output = '<label class="' . $lcalss . '" >' . $title . '</label>';
		if ($type != 'radio' && $type != 'checkbox') {
			$output .= '<input class="' . $iclass . '" type="' . $type . '" name="' . $key . '" />';
		}else{
			$answers = explode(";", $answerstr);
			foreach ($answers as $answer){
				$output .= '<label class="' . $iclass . '" ><input class="' . $iclass . '" ';
				if ($answers[0] == $answer && $type == 'radio')
					$output .= 'checked ';
			   	$output .= 'type="' . $type . '" name="' . $key . '" value="' . $answer . '" />' . $answer . '</label>';
			}
		}

		return $output;
	}

	public function lxt_survey_submit($attr) {
		if (!is_array($attr) || !($title = $attr['title'])) 
			$title= __('Submit' , $this->slug);

		if (!is_array($attr) || !($class = $attr['class'])) 
			$class=$this->slug . '_submit';
		else
			$class .= ' ' . $this->slug . '_submit';

		$output = '<button type="button" class="' . $class . '">'. $title . '</button>';

		return $output;
	}

	public function lxt_dosurvey($attr) {
		if (!$attr || !($title = $attr['title'])) return '';

		return $this->plugin->get_pub_obj()->get_survey_container( $title, 'shortcode' );
	}
}
