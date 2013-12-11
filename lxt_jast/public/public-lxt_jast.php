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
	protected static $is_local;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();
	}

	public function get_survey_meta_data($post_id) {
		$attr = [];
		$survey_meta = $this->plugin->get_survey_meta();

		foreach($survey_meta as $key => $value) {
			$v = get_post_meta( $post_id, $this->slug . '_md_' . $key, true );
			if ( $v ) 
				$attr[$key] = $v;
		}

		return $attr;
	}

	public function add_class( &$str, $class ) {
		if ( strpos( $str, $class) )
			$str = $str . ' ' . $class;
	}

	public function get_survey_container( $title, $context) {
		$loop = $this->get_post_loop( $title );

		if ( $loop->have_posts() ) {
			$loop->the_post();
			$attr = $this->get_survey_meta_data( get_the_ID() );

			$attr = shortcode_atts( array(
				'visibility' => __('All', $this->slug),
				'class' => $this->slug . '_popup',
				'linktext' => get_the_title(),
				'linkclass' => $this->slug . '_popup_open',
				'closeclass' => $this->slug . '_popup_close',
				'nonce' =>  wp_create_nonce($context),
				'slug' => $this->slug,
				'post_id' => get_the_ID()
			), $attr );

			if ( !is_user_logged_in() && $attr['visibility'] != __('All', $this->slug))
				return '';

			$this->add_class( $attr['linkclass'], $this->slug . '_popup_open'); 
			$this->add_class( $attr['closeclass'], $this->slug . '_popup_close'); 

			$temp = '<div class="{$class}" id="{$slug}_popup_{$nonce}" ><span class="{$closeclass}"><span>X</span></span></div>'; 
			//$temp .= '<div id="{$slug}_popup_container_{$nonce}"></div></div>';
			$temp .= '<a href="javascript:void(0)" class="{$linkclass}" target="{$nonce}" postid="{$post_id}">{$linktext}</a>';

			$output = lxt_public_lib::smarty_template_array($temp, $attr); 
		}
		else {
			$output = '';
		}
		wp_reset_postdata(); 
		return $output;
	}

	public function get_survey_content( $postid ) {
?>
		<header><?php get_post_field('post_title', $postid) ?></header>
<?php
		echo apply_filters('the_content', get_post_field('post_content', $postid) );
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

	public function localize_script_const($handle) {
		if (! self::$is_local) {
			wp_localize_script( $handle, 'lxt_jast_local_const', array(
				'slug' => $this->slug,
				'ver' => $this->ver,
//				'ajaxurl' => admin_url().'admin-ajax.php' . '?XDEBUG_SESSION_START=1',
				'ajaxurl' => admin_url().'admin-ajax.php',
				'choiceLabel' => __("'s Choice"),
				'pubjsurl' => plugins_url( 'assets/js/', __FILE__ )
			));
			self::$is_local = true;
		}
	}
}

