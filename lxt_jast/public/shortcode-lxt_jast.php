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
	protected $shortcodes = ['lxt_jast_survey', 'lxt_jast_result', 'lxt_jast_qust', 'lxt_jast_submit', 'lxt_jast_email'];

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

	public function lxt_jast_result($attr) {
		$attr = shortcode_atts( array(
			'title' => null,
			'name' => null,
			'width' => '300px',
			'high' => '300px',
			'type' => null
		), $attr );

		return $this->plugin->get_pub_obj()->get_survey_chart_frame($attr);
	}

	public function lxt_jast_qust($attr, $content) {
		$attr = shortcode_atts( array(
			'name' => null,
			'type' => 'text',
			'class' => $this->slug.'_qust',
			'option' => null,
			'content' => $this->get_label_content( $content )
		), $attr );

		if ($attr['name'] == null) 
			return __('Error,a question must have a name', $this->slug);

		if (($attr['type'] == 'radio' || $attr['type'] == 'checkbox') && !$attr['option']) 
			return __('Error, no option answer.', $this->slug);

		$this->plugin->get_pub_obj()->add_class( $attr['class'], $this->slug . '_qust'); 

	
		if ($attr['type'] == 'textarea')
			$temp = '<div class="{$class}">{$content}<textarea name="{$name}" /></div>';
		else if ($attr['type'] == 'radio' || $attr['type'] == 'checkbox') {
			$attr['options'] = explode(";", $attr['option']);
			$temp = '<div class="{$class}">{$content}{foreach $options as $item}<lable><input type="{$type}" name="{$name}" />{$item}</label>{/foreach}</div>';
		}
		else
			$temp = '<div class="{$class}">{$content}<input type="{$type}" name="{$name}" /></div>';
		
		$output = lxt_public_lib::smarty_template_array($temp, $attr);

		return $output;
	}

	public function lxt_jast_email($attr, $content) {
		$attr = shortcode_atts( array(
			'class' => $this->slug . '_qust',
			'name' => $this->slug . '_the_email',
			'content' => $this->get_label_content( $content )
		), $attr );

		$this->plugin->get_pub_obj()->add_class( $attr['class'], $this->slug . '_qust'); 

		$output = '';

		if ( !is_user_logged_in() ) {
			$temp = '<div class="{$class}">{$content}<input type="email" name="{$name}" /></div>';
			$output = lxt_public_lib::smarty_template_array($temp, $attr);
		}

		return $output;
	}

	private function get_label_content( $content ) {
		if ( $content != strip_tags($content) )
			return $content;
		else
			return '<header>' . $content . '</header>';
	}

	public function lxt_jast_submit($attr) {
		$attr = shortcode_atts( array(
			'value' => __('Finish' , $this->slug),
			'class' => $this->slug . '_submit',
		), $attr );

		$this->plugin->get_pub_obj()->add_class( $attr['class'], $this->slug . '_submit'); 

		$temp = '<button type="button" class="{$class}">{$value}</button>';
		$output = lxt_public_lib::smarty_template_array($temp, $attr);

		return $output;
	}


	public function lxt_jast_survey($attr) {
		if (!$attr || !($title = $attr['title'])) return '';

		return $this->plugin->get_pub_obj()->get_survey_container( $title, 'shortcode' );
	}
}
