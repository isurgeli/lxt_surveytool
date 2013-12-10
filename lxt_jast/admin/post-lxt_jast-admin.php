<?php
/**
 * Just another survey tool.
 *
 * @package   lxt_jast_Admin
 * @author    isurgeli@gmail.com
 * @license   GPL-2.0+
 * @link      http://isurge.wordpress.com
 * @copyright 2013 Li xintao
 */

/**
 * Plugin class. This class used to do the post work for public plugin work.
 */
class lxt_jast_post_Admin {
	protected $ver;
	protected $slug;
	protected $plugin;
	protected $admin;

	public function __construct() {
		$this->plugin = lxt_jast_plugin::get_instance(); 
		$this->admin = lxt_jast_plugin_Admin::get_instance(); 
		$this->slug = $this->plugin->get_slug();
		$this->ver = $this->plugin->get_ver();

		add_action( 'add_meta_boxes', array ( $this, 'add_post_type_metabox') );
		add_action( 'save_post', array ( $this, $this->slug.'_save_meta') );
	}

	public function add_post_type_metabox() {
		//create a custom meta box
		add_meta_box( 'lxt_meta_box', 
			__('Survey property'), 
			array( $this, $this->slug.'_posttype_metabox' ), 
			$this->slug,
			'normal', 
			'default' );
	}

	public function lxt_jast_posttype_metabox( $post ) {
 
		//retrieve the metadata values if they exist
		$style = get_post_meta( $post->ID, $this->slug . '_md_style', true );
		$visibility = get_post_meta( $post->ID, $this->slug . '_md_visibility', true );		
		$link = get_post_meta( $post->ID, $this->slug . '_md_link', true );
		$per_page = get_post_meta( $post->ID, $this->slug . '_md_perpage', true );
 
		//_e( 'Please set the survey property', $this->slug );
		?>
		<p>
		<?php echo esc_attr__( 'Visibility:', $this->slug); ?>
		<select name="<?php echo $this->slug ?>_md_field_visibility">
			<option value="<?php echo esc_attr__( 'All', $this->slug); ?>" <?php selected( $visibility, esc_attr__( 'All', $this->slug) ); ?>>
				<?php echo esc_attr__( 'All', $this->slug); ?>
			</option>
			<option value="<?php echo esc_attr__( 'Login user', $this->slug); ?>" <?php selected( $visibility, esc_attr__( 'Login user', $this->slug) ); ?>>
				<?php echo esc_attr__( 'Login user', $this->slug); ?>
			</option>
		</select>
		<?php echo esc_attr__( 'Link:', $this->slug); ?> <input type="text" name="<?php echo $this->slug ?>_md_field_link" value="<?php echo esc_attr( $link ); ?>"/>
		<?php echo esc_attr__( 'Result per page:', $this->slug); ?> <input type="text" name="<?php echo $this->slug ?>_md_perpage" value="<?php echo esc_attr( $per_page ); ?>"/>
		<?php echo esc_attr__( 'Style:', $this->slug); ?> <input type="text" size="100" name="<?php echo $this->slug ?>_md_field_style" value="<?php echo esc_attr( $style ); ?>"/>
		</p>
    <?php
 
	}

	public function lxt_jast_save_meta( $post_id ) {
		//verify the metadata is set
		if ( isset( $_POST[$this->slug.'_md_field_visibility'] ) ) {
			//save the metadata
			update_post_meta( $post_id, $this->slug . '_md_style', strip_tags( $_POST[$this->slug.'_md_field_style'] ) );
			update_post_meta( $post_id, $this->slug . '_md_visibility',strip_tags( $_POST[$this->slug.'_md_field_visibility'] ) );
			update_post_meta( $post_id, $this->slug . '_md_link',strip_tags( $_POST[$this->slug.'_md_field_link'] ) );
			update_post_meta( $post_id, $this->slug . '_md_perpage',strip_tags( $_POST[$this->slug.'_md_perpage'] ) );
		}
	}
}

