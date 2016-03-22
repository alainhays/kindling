<?php
/**
 * Kindling Type Base Class.
 *
 * @package WordPress
 * @subpackage Kindling
 */

/**
 * This is a base for custom post type classes so they can all take advantage of the same logic.
 *
 * You should do your best not to overwrite any method and to use the
 * custom_[some_property] properties for configuring the base class methods which have parameters.
 * Feel free to over write any of the parameters you see fit to make the sub-classes
 * more versatile.
 *
 * @package      WordPress
 * @subpackage   Kindling
 *
 * @author       Matchbox Design Group <info@matchboxdesigngroup.com>
 *
 * @example lib/post-types/type-stub.php
 */
abstract class MDG_Type_Base {
	/**
	 * Slug for post type.
	 *
	 * @var  string
	 */
	public $post_type;

	/**
	 * Title of post type.
	 *
	 * @var  string
	 */
	public $post_type_title;

	/**
	 * Singular title.
	 *
	 * @var  string
	 */
	public $post_type_single;

	/**
	 * Arguments to be used when registering the post type's taxonomy.
	 *
	 * @var  array
	 */
	private $_taxonomy_args;


	/**
	 * Arguments to be used when registering the post type.
	 *
	 * @var  array
	 */
	private $_post_type_args;

	/**
	 * What the post type supports.
	 *
	 * @var  array
	 */
	private $_post_type_supports;

	/**
	 * The post types custom labels used in register_post_type().
	 *
	 * @var  array
	 */
	public $custom_post_type_labels;

	/**
	 * Used to disable the addition of the featured image column.
	 *
	 * @var  boolean
	 */
	public $disable_image_column;

	/**
	 * Custom post type arguments used in register_post_type().
	 *
	 * @var  array
	 */
	public $custom_post_type_args;

	/**
	 * The taxonomy "name" used in register_taxonomy().
	 *
	 * @var  array
	 */
	public $taxonomy_name;

	/**
	 * Custom taxonomy labels used in register_taxonomy().
	 *
	 * @var  array
	 */
	public $custom_taxonomy_labels;

	/**
	 * Custom taxonomy arguments used in register_taxonomy().
	 *
	 * @var  array
	 */
	public $custom_taxonomy_args;

	/**
	 * Custom post type supports array used in register_post_type().
	 *
	 * @var  array
	 */
	public $custom_post_type_supports;

	/**
	 * Disable/Enable Categories per post type.
	 *
	 * @var  boolean
	 */
	public $disable_post_type_taxonomy;

	/**
	 * Disable/Enable thumbnail post table column.
	 *
	 * @var  boolean
	 */
	public $disable_thumbnail_column;

	/**
	 * Class constructor, takes care of all the setup needed.
	 *
	 * @param string $post_type        The post type id.
	 * @param string $post_type_title  The post type plural title.
	 * @param string $post_type_single The post type singular title.
	 */
	public function __construct( $post_type, $post_type_title, $post_type_single ) {
		$this->post_type = $post_type;
		$this->post_type_title = $post_type_title;
		$this->post_type_single = $post_type_single;

		$this->set_parameters();
		$this->type_base_add_actions();
	} // __construct()

	/**
	 * Sets a default value for a variable.
	 *
	 * @param   mixed $check_value    The value to check if it is set.
	 * @param   mixed $default_value  The default value to be used if $check_value has not already been set.
	 *
	 * @return  mixed                   The $check_value if it is set and the $default_value if not.
	 */
	public function set_default( $check_value, $default_value ) {
		return ( isset( $check_value ) ) ? $check_value : $default_value;
	} // set_default()

	/**
	 * Sets all of the classes parameters
	 *
	 * @todo Make this cleaner
	 */
	private function set_parameters() {
		// Taxonomy Properties.
		$this->_taxonomy_args         = $this->set_default( $this->_taxonomy_args, array() );
		$this->custom_taxonomy_labels = $this->set_default( $this->custom_taxonomy_labels, array() );
		$this->custom_taxonomy_args   = $this->set_default( $this->custom_taxonomy_args, array() );
		$this->taxonomy_name          = $this->set_default( $this->taxonomy_name, "{$this->post_type}-categories" );
		$this->set_taxonomy_args( $this->custom_taxonomy_args );
		$this->disable_post_type_taxonomy = $this->set_default( $this->disable_post_type_taxonomy, false );

		// Post Type.
		$this->_post_type_args           = $this->set_default( $this->_post_type_args, array() );
		$this->custom_post_type_args     = $this->set_default( $this->custom_post_type_args, array() );
		$this->custom_post_type_labels   = $this->set_default( $this->custom_post_type_labels, array() );
		$this->custom_post_type_supports = $this->set_default( $this->custom_post_type_supports, array() );
		$this->set_post_type_supports( $this->custom_post_type_supports );
		$this->set_post_type_args( $this->custom_post_type_args );

		// General.
		$this->disable_image_column = $this->set_default( $this->disable_image_column, false );
	} // set_parameters()

	/**
	 * Actions that need to be set for this base class only using add_action()
	 * sub-classes will need to set there own actions without overriding this method.
	 */
	private function type_base_add_actions() {
		// Create post type.
		add_action( 'init', array( &$this, 'register_post_type' ) );

		// Featured image column action.
		$this->add_image_column_action();
	} // type_base_add_actions()

	/**
	 * Checks if the current post type is the correct post type.
	 *
	 * @param  string  $post_type   The post type name to check against.
	 * @param  boolean $admin_only  Optional, if it should only check in the admin, default tue.
	 *
	 * @return boolean If the post type is correct.
	 */
	public function is_current_post_type( $post_type = null, $admin_only = true ) {
		global $post;
		$post_type = ( is_null( $post_type ) ) ? $this->post_type : $post_type;

		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( isset( $screen ) and $post_type == $screen->post_type ) {
				return true;
			} // if()
		} // if()

		if ( ! $admin_only and isset( $post ) and $post->post_type == $post_type ) {
			return true;
		} // if()

		return false;
	} // is_current_post_type()

	/**
	 * Column filter for featured image.
	 */
	private function add_image_column_action() {
		if ( $this->disable_image_column ) {
			return;
		} // if()

		switch ( $this->post_type ) {
			case 'post':
				$manage_filter = 'manage_posts_columns';
				$custom_column = 'manage_posts_custom_column';
				break;
			case 'page':
				$manage_filter = 'manage_pages_columns';
				$custom_column = 'manage_pages_custom_column';
				break;
			default:
				$manage_filter = "manage_{$this->post_type}_posts_columns";
				$custom_column = "manage_{$this->post_type}_posts_custom_column";
				break;
		} // switch()

		add_filter( $manage_filter, array( &$this, 'add_thumbnail_column' ), 5 );
		add_action( $custom_column, array( &$this, 'display_thumbnail_column' ), 5, 2 );
	} // add_image_column_action()

	/**
	 * Adds the thumbnail image column.
	 *
	 * @param array $cols Current post table columns.
	 *
	 * @return array $cols The current columns with thumbnail column added.
	 */
	function add_thumbnail_column( $cols ) {
		// Make sure the image column has not been disabled.
		if ( $this->disable_image_column ) {
			return $cols;
		} // if()

		// Get the current post type.
		$post_type = ( isset( $_GET['post_type'] ) ) ? $_GET['post_type'] : '';
		if ( '' === $post_type ) {
			return $cols;
		} // if()

		// Make sure this is the correct post type.
		if ( ! $this->is_current_post_type( $post_type ) ) {
			return $cols;
		} // if()

		// Make sure the post supports thumbnails.
		if ( ! post_type_supports( $post_type, 'thumbnail' ) ) {
			return $cols;
		} // if()

		// Get the post type object.
		$post_type_obj = get_post_type_object( $post_type );
		if ( is_null( $post_type_obj ) ) {
			return $cols;
		} // if()

		// Set the column.
		$featured_image_label  = ( isset( $post_type_obj->labels->featured_image ) ) ? $post_type_obj->labels->featured_image : 'Featured Image';
		$cols['mdg_post_thumb'] = __( $featured_image_label );

		return $cols;
	} // add_thumbnail_column()

	/**
	 * Grab featured-thumbnail size post thumbnail and display it.
	 *
	 * @param array   $col  Current post table columns.
	 * @param integer $id   The current post ID..
	 */
	function display_thumbnail_column( $col, $id ) {
		global $mdg_thumbnail_column_image_ids;

		// Check if we should display this image.
		$post_type         = get_post_type( $id );
		$column_image_ids  = ( isset( $mdg_thumbnail_column_image_ids ) ) ? $mdg_thumbnail_column_image_ids : array();
		$already_displayed = in_array( $id, $column_image_ids );
		$correct_column    = ( 'mdg_post_thumb' === $col );
		$correct_post_type = $this->is_current_post_type( $post_type );

		if ( $correct_column and $correct_post_type and ! $already_displayed ) {
			echo get_the_post_thumbnail( $id, 'admin-list-thumb' );
			$column_image_ids[] = $id;
		} // if()

		$mdg_thumbnail_column_image_ids = $column_image_ids;
	} // display_thumbnail_column()

	/**
	 * Sets the Post Type support array
	 *
	 * @link https://codex.wordpress.org/Function_Reference/post_type_supports
	 *
	 * @param array $custom_post_type_supports  What the current post type should support.
	 */
	public function set_post_type_supports( $custom_post_type_supports ) {
		$default_post_type_supports = array(
			'title',
			'editor',
			'post-thumbnails',
			'custom-fields',
			'page-attributes',
			'author',
			'thumbnail',
			'excerpt',
			'trackbacks',
			'comments',
			'revisions',
			'post-formats',
		);

		if ( ! empty( $custom_post_type_supports ) ) {
			$this->_post_type_supports = $custom_post_type_supports;
		} else {
			$this->_post_type_supports = $default_post_type_supports;
		} // if()
	} // set_post_type_supports()

	/**
	 * Registers the post type and a custom taxonomy for the post type..
	 */
	public function register_post_type() {
		// Make sure the post type info is set - none of this will work without it!
		if ( is_null( $this->post_type ) or is_null( $this->post_type_title ) or is_null( $this->post_type_single ) ) {
			return false; }

		// Register post type.
		register_post_type( $this->post_type, $this->_post_type_args );

		// Register taxonomy for post type.
		if ( ! $this->disable_post_type_taxonomy ) {
			register_taxonomy(
				$this->taxonomy_name,
				array( $this->post_type ),
				$this->_taxonomy_args
			);
		} // if()
	} // register_post_type()

	/**
	 * Sets the arguments used for registering the post type with register_post_type()
	 *
	 * @param  array $custom_post_type_args Optional. Anything acceptable in the $args parameter for register_post_type() http://codex.wordpress.org/Function_Reference/register_post_type.
	 */
	public function set_post_type_args( $custom_post_type_args = array() ) {
		$lowercase_post_type_title  = strtolower( $this->post_type_title );
		$lowercase_post_type_single = strtolower( $this->post_type_single );
		$default_post_type_labels   = array(
			'name'                  => __( $this->post_type_title ),
			'singular_name'         => __( $this->post_type_single ),
			'add_new'               => __( "Add New {$this->post_type_single}" ),
			'add_new_item'          => __( "Add New {$this->post_type_single}" ),
			'edit_item'             => __( "Edit {$this->post_type_single}" ),
			'new_item'              => __( "New {$this->post_type_single}" ),
			'all_items'             => __( "All {$this->post_type_title}" ),
			'view_item'             => __( "View {$this->post_type_single}" ),
			'search_items'          => __( "Search {$this->post_type_title}" ),
			'not_found'             => __( "No {$lowercase_post_type_title} found" ),
			'not_found_in_trash'    => __( "No {$lowercase_post_type_title} found in Trash" ),
			'parent_item_colon'     => __( '' ),
			'menu_name'             => __( $this->post_type_title ),
			'featured_image'        => __( "{$this->post_type_single} Image" ),
			'set_featured_image'    => __( "Set {$lowercase_post_type_single} image" ),
			'remove_featured_image' => __( "Remove {$lowercase_post_type_single} image" ),
			'use_featured_image'    => __( "Use as {$lowercase_post_type_single} image" ),
		);

		$labels = array_merge( $default_post_type_labels, $this->custom_post_type_labels );
		$default_post_type_args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->post_type, 'with_front' => false ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => true,
			'menu_position'      => 5,
			'can_export'         => true,
			'supports'           => $this->_post_type_supports,
			'menu_icon'          => 'dashicons-edit',
		);

		$this->_post_type_args = array_merge( $default_post_type_args, $custom_post_type_args );
	} // set_post_type_args()

	/**
	 * Sets the taxonomy args when registering a taxonomy using register_taxonomy()
	 *
	 * @param array $custom_taxonomy_args Optional. Anything acceptable in the $args parameter for register_taxonomy() http://codex.wordpress.org/Function_Reference/register_taxonomy.
	 */
	public function set_taxonomy_args( $custom_taxonomy_args = array() ) {
		// Taxonomy labels.
		$default_labels = array(
			'name'                       => _x( "{$this->post_type_single} Categories", 'taxonomy general name' ),
			'singular_name'              => _x( "{$this->post_type_single} Category", 'taxonomy singular name' ),
			'search_items'               => __( "Search {$this->post_type_single} Categories" ),
			'all_items'                  => __( "All {$this->post_type_single} Categories" ),
			'parent_item'                => __( "Parent {$this->post_type_single} Category" ),
			'parent_item_colon'          => __( "Parent {$this->post_type_single} Category:" ),
			'edit_item'                  => __( "Edit {$this->post_type_single} Category" ),
			'update_item'                => __( "Update {$this->post_type_single} Category" ),
			'add_new_item'               => __( "Add New {$this->post_type_single} Category" ),
			'new_item_name'              => __( "New {$this->post_type_single} Category Name" ),
			'menu_name'                  => __( "{$this->post_type_single} Categories" ),
			'view_item'                  => __( "View {$this->post_type_single} Category" ),
			'popular_items'              => __( "Popular {$this->post_type_single} Categories" ),
			'separate_items_with_commas' => __( "Separate {$this->post_type_single} Categories with commas" ),
			'add_or_remove_items'        => __( "Add or remove  {$this->post_type_single} Categories" ),
			'choose_from_most_used'      => __( "Choose from the most used {$this->post_type_single} Categories" ),
			'not_found'                  => __( "No  {$this->post_type_single} Categories found." ),
		);
		$labels = array_merge( $default_labels, $this->custom_taxonomy_labels );

		// Register taxonomy.
		$default_taxonomy_args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_ui'           => true,
			'show_tagcloud'     => true,
			'show_admin_column' => true,
			'query_var'         => $this->taxonomy_name,
			'rewrite'           => array(
				'slug'         => $this->post_type,
				'with_front'   => false,
				'hierarchical' => true,
			),
		);

		$this->_taxonomy_args = array_merge( $default_taxonomy_args, $custom_taxonomy_args );
	} // set_taxonomy_args()

	/**
	 * Retrieves the current post types posts.
	 *
	 * @param  array   $custom_query_args  Optional. Any arguments accepted by the WP_Query class http://codex.wordpress.org/Class_Reference/WP_Query.
	 * @param  boolean $query_object       Optional. If true it will return the WP_Query object instead of posts.
	 *
	 * @return array                          Retrieved post objects/Query object.
	 */
	public function get_posts( $custom_query_args = array(), $query_object = false ) {
		$default_query_args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'order'          => 'DESC',
			'orderby'        => 'date',
		);
		$query_args = array_merge( $default_query_args, $custom_query_args );
		$query      = new WP_Query( $query_args );
		$posts      = $query->get_posts();

		if ( $query_object ) {
			return $query;
		} // if()

		return $posts;
	} // get_posts()
} // END Class MDG_Type_Base()
