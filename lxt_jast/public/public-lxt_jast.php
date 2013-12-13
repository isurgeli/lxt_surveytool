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
				'post_id' => get_the_ID(),
				'loader' => plugins_url( '../assets/ajax-loader.gif', __FILE__ )
			), $attr );

			if ( !is_user_logged_in() && $attr['visibility'] != __('All', $this->slug))
				return '';

			$this->add_class( $attr['linkclass'], $this->slug . '_popup_open'); 
			$this->add_class( $attr['closeclass'], $this->slug . '_popup_close'); 

			$temp = '<div class="{$class}" id="{$slug}_popup_{$nonce}" ><span class="{$closeclass}"><span>X</span></span>'; 
			$temp .= '<div id="{$slug}_popup_container_{$nonce}"></div></div>';
			$temp .= '<a href="javascript:void(0)" class="{$linkclass}" target="{$nonce}" postid="{$post_id}">{$linktext}</a>';

			$output = lxt_public_lib::smarty_template_array($temp, $attr); 
		}
		else {
			$output = __('Can not find survey', $this->slug) . ' [' . $title . ']' ;
		}
		wp_reset_postdata(); 
		return $output;
	}

	public function get_survey_content( $postid ) {
?>
		<h1><?php echo get_post_field('post_title', $postid) ?></h1>
<?php
		$rmwpautop = get_post_meta($postid, $this->slug . '_md_wpautop', true);

	    // Remove the filter
		remove_filter('the_content', 'wpautop');
	    if ('false' === $rmwpautop) {
		} else {
			add_filter('the_content', 'wpautop');
	    }
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

	public function get_survey_chart_frame($attr) {		
		extract($attr);

		if ($title == null) 
			return __('Must indicate the survey title', $this->slug);

		$post_data = $this->get_survey_questions( $title , $name );

		if ( ! array_key_exists('id', $post_data) )
			return __('Can not find survey', $this->slug) . ' [' . $title . '-' . $name . ']';


		$post_id = $post_data['id'];

		$output = '';
		for ($j = 0; $j < count($post_data['attr']); $j++) {
			$attr = $post_data['attr'][$j];
			$content = $post_data['content'][$j];

			preg_match_all ('/\s+([^"]+)="([^"]+)"/', $attr, $pat_array);
			$qust_attr = [];
			for ($i = 0; $i < count($pat_array[0]); $i++) {
				$qust_attr[$pat_array[1][$i]] = $pat_array[2][$i];
			}

			if ( ( strtolower( $qust_attr['type'] ) != 'radio' && strtolower( $qust_attr['type'] ) != 'checkbox' )
				|| !isset($qust_attr['option']) ) {
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


			//$cur_name = str_replace( '.', '-', $qust_attr['name'] );

			$cur_name = $qust_attr['name'];
			$qust_title = strip_tags($content);
			$nounce = wp_create_nonce( get_the_id() );
			$output .= <<<OUT
<div type="{$this->slug}_$qust_type" class="{$this->slug}_result_img" id="{$this->slug}_retimg_{$post_id}_{$cur_name}_$nounce" title="$qust_title" ></div>
OUT;
		}
		
		return $output;
	}

	public function get_survey_admin_result_frame($title, $type) {
		if ($title == null) return '';

		$post_data = $this->get_survey_questions( $title , null );

		$post_id = $post_data['id'];
?>
	<div class="lxt_jast_select_qust"><span class="lxt_jast_select_action"><?php _e('Please select the question: ', $this->slug); ?>
	<select id="<?php echo $this->slug; ?>_survey_qust"><option value=""></option>
<?php
		for ($j = 0; $j < count($post_data['attr']); $j++) {
			$attr = $post_data['attr'][$j];
			$content = $post_data['content'][$j];

			preg_match_all ('/\s+([^"]+)="([^"]+)"/', $attr, $pat_array);
			$qust_attr = [];
			for ($i = 0; $i < count($pat_array[0]); $i++) {
				$qust_attr[$pat_array[1][$i]] = $pat_array[2][$i];
			}

			if ( $type == 'text' &&	( strtolower( $qust_attr['type'] ) != 'radio' && strtolower( $qust_attr['type'] ) != 'checkbox' ) ) {
?>
	<option value="<?php echo $qust_attr['name']; ?>"><?php echo strip_tags($content); ?></option>
<?php
			} else if ( $type == 'img' &&	( strtolower( $qust_attr['type'] ) == 'radio' || strtolower( $qust_attr['type'] ) == 'checkbox' ) ) {
				if ( strtolower( $qust_attr['type'] ) == 'checkbox' )
					$img_type = $this->slug . '_bar';
				else
					$img_type = $this->slug . '_pie';
?>
	<option value='{"name":"<?php echo $qust_attr['name']; ?>","type":"<?php echo $img_type; ?>"}'><?php echo strip_tags($content); ?></option>
<?php
			}
		}
		if ( $type == 'text' ) {
?>
	</select></span></div><div class="<?php echo $this->slug; ?>_result_table" id="<?php echo $this->slug; ?>_rettable_<?php echo $post_id; ?>" />
<?php
		} else if ( $type == 'img' ) {
?>
	</select></span></div><div class="<?php echo $this->slug; ?>_result_img" id="<?php echo $this->slug; ?>_retimg_<?php echo $post_id; ?>" title="" />
<?php
		}
	}

	public function get_survey_questions( $title, $name ) {
		$loop = $this->plugin->get_pub_obj()->get_post_loop( $title );
		$ret = [];
		if ( $loop->have_posts() ) {
			$loop->the_post();
			$content = get_the_content();
			$post_id = get_the_id();

			$pat_array = null;
			$stag = $this->plugin->get_shortcodes()[2];
			if ( !isset ( $name ) ) 
				preg_match_all ('/\['.$stag.'([^\]]+)\](.*)\[\/'.$stag.'\]/', $content, $pat_array);
			else
				preg_match_all ('/\['.$stag.'([^\]]+name="'.$name.'"[^\]]+)\](.*)\[\/'.$stag.'\]/', $content, $pat_array);


			if (count($pat_array[0])>0)
				$ret = ['id' => $post_id, 'attr' => $pat_array[1], 'content' => $pat_array[2]];
		}
		wp_reset_postdata();
		return $ret;
	}

	public function localize_script_const($handle) {
		if (! self::$is_local) {
			wp_localize_script( $handle, 'lxt_jast_local_const', array(
				'slug' => $this->slug,
				'ver' => $this->ver,
				'ajaxurl' => admin_url().'admin-ajax.php' . '?XDEBUG_SESSION_START=1',
//				'ajaxurl' => admin_url().'admin-ajax.php',
				'choiceLabel' => __("Selected"),
				'pubjsurl' => plugins_url( 'assets/js/', __FILE__ )
			));
			self::$is_local = true;
		}
	}
}

