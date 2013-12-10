<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   lxt_jast
 * @author    Li xintao <isurgeli@gmail.com>
 * @license   GPL-2.0+
 * @link      http://isurge.worpress.com
 * @copyright 2013 Li xintao
 */
class lxt_jast_view_result {
	protected static $instance = null;
	protected $ver;
	protected $slug;
	protected $plugin;
	protected $admin;

	public function __construct() {
		self::$instance = $this;

		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->admin = lxt_jast_plugin_Admin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			new self;
		}

		return self::$instance;
	}

	public function get_i18n_string($str) {
		return __($str, $this->slug);
	}

	public function get_survey_select() {
		$output = '<select id="' . $this->slug . '_survey_title" >';
		$output .= '<option value=""></option>';	
		$loop = $this->plugin->get_pub_obj()->get_post_loop(null);
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$output .=  '<option value="'.get_the_title().'">'.get_the_title().'</option>';
			}
		}
		$output .= '</select>';
		wp_reset_postdata();

		return $output;
	}
}
?>

<div class="wrap">

<?php screen_icon(); ?>
<input type="hidden" id="lxt_jast_screen_id" value="<?php $screen=get_current_screen(); echo $screen->id; ?>" />
<header>
<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
<div class="lxt_jast_select_survey"><span class="lxt_jast_select_action">
<?php echo lxt_jast_view_result::get_instance()->get_i18n_string('Please select the survey you like to view: '); ?>
<?php echo lxt_jast_view_result::get_instance()->get_survey_select(); ?>
</span></div>
</header>
<h3 class="nav-tab-wrapper">
<a href="javascript:void(0)" id="lxt_jast_image_tab" class="nav-tab nav-tab-active"><?php echo lxt_jast_view_result::get_instance()->get_i18n_string( 'Chart' ); ?></a>
<a href="javascript:void(0)" id="lxt_jast_text_tab" class="nav-tab"><?php echo lxt_jast_view_result::get_instance()->get_i18n_string( 'Text' ); ?></a>
</h3>
<div id="lxt_jast_result_content"/>
</div>

<?php
include( ABSPATH . 'wp-admin/admin-footer.php' );
