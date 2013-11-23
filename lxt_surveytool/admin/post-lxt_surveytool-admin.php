<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_survey_Admin
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_surveytool_post_Admin {
	protected $version;
	protected $plugin_slug;

	public function __construct($slug, $ver) {
		$this->plugin_slug = $slug;
		$this->version = $ver;	
		add_action( 'add_meta_boxes', array ( $this, 'add_post_type_metabox') );
		add_action( 'save_post', array ( $this, $this->plugin_slug.'_save_meta') );
	}

	public function add_post_type_metabox() {
		//create a custom meta box
		add_meta_box( 'lxt_meta_box', 
			__('Survey property'), 
			array( $this, $this->plugin_slug.'_posttype_metabox' ), 
			$this->plugin_slug,
			'normal', 
			'default' );
	}

	public function lxt_surveytool_posttype_metabox( $post ) {
 
		//retrieve the metadata values if they exist
		$lxt_st_width = get_post_meta( $post->ID, '_lxt_st_width', true );
		$lxt_st_height = get_post_meta( $post->ID, '_lxt_st_height', true );
		$lxt_st_visibility = get_post_meta( $post->ID, '_lxt_st_visibility', true );
 
		//_e( 'Please set the survey property', $this->plugin_slug );
		?>
		<p>
		<?php echo esc_attr__( 'Width:', $this->plugin_slug); ?> <input type="text" name="lxt_st_width" value="	<?php echo esc_attr( $lxt_st_width ); ?> "/>
		<?php echo esc_attr__( 'Height:', $this->plugin_slug); ?> <input type="text" name="lxt_st_height" value="	<?php echo esc_attr( $lxt_st_height ); ?> "/>
		<?php echo esc_attr__( 'Visibility:', $this->plugin_slug); ?>
		<select name="lxt_st_visibility">
			<option value="<?php echo esc_attr__( 'All', $this->plugin_slug); ?>" <?php selected( $lxt_st_visibility, esc_attr__( 'All', $this->plugin_slug) ); ?>>
				<?php echo esc_attr__( 'All', $this->plugin_slug); ?>
			</option>
			<option value="<?php echo esc_attr__( 'Login user', $this->plugin_slug); ?>" <?php selected( $lxt_st_visibility, esc_attr__( 'Login user', $this->plugin_slug) ); ?>>
				<?php echo esc_attr__( 'Login user', $this->plugin_slug); ?>
			</option>
		</select>
		</p>
    <?php
 
	}

	public function lxt_surveytool_save_meta( $post_id ) {
		//verify the metadata is set
		if ( isset( $_POST['lxt_st_width'] ) ) {
			//save the metadata
			update_post_meta( $post_id, '_lxt_st_width', strip_tags( $_POST['lxt_st_width'] ) );
			update_post_meta( $post_id, '_lxt_st_height', strip_tags( $_POST['lxt_st_height'] ) );
			update_post_meta( $post_id, '_lxt_st_visibility',strip_tags( $_POST['lxt_st_visibility'] ) );
		}
	}
}

