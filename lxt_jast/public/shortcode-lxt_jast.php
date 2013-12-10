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
			'type' => null
		), $attr ) );

		return $this->plugin->get_pub_obj()->get_survey_chart_frame($title, $key, $type, $width, $high);
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
		
		if ($type == 'textarea') {
			$output .= '<textarea rows="3" class="' . $iclass . '" name="' . $key . '" />';
		}else if ($type != 'radio' && $type != 'checkbox') {
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
		$output = "";
		if ( !is_user_logged_in() ) {
			$output .= '<label class="' . $tclass . '" >' . $title . '</label>';
			$output .= '<input class="' . $iclass . '" type="email" name="' . $this->slug . '_the_email"></input>';
		}

		return $output;
	}


	public function lxt_dosurvey($attr) {
		if (!$attr || !($title = $attr['title'])) return '';

		return $this->plugin->get_pub_obj()->get_survey_container( $title, 'shortcode' );
	}
}
