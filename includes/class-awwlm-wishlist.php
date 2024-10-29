<?php

if (!defined('ABSPATH'))
    exit;

class AWWLM_Wishlist
{

    /**
     * @var    object
     * @access  private
     * @since    1.0.0
     */
    private static $_instance = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;
    private $_active = false;

    public function __construct()
    {

      // add_action('init', array($this, 'awwlm_register_wishlist_page'));
      add_action( 'init', array($this, 'awwlm_register_post_types') );
      add_action( 'init', array($this, 'awwlm_register_wishlist_terms') );

    }

    /**
     *
     * Ensures only one instance of AWDP is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @see WordPress_Plugin_Template()
     * @return Main AWDP instance
     */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    public function is_active()
    {
        return $this->_active !== false;
    }


    public function awwlm_register_wishlist_page()
    {
      $check_page_exist = get_page_by_title('Wishlist', 'OBJECT', 'page');
      if(empty($check_page_exist)) {
          $page_id = wp_insert_post(
              array(
              'post_title'     => 'Wishlist',
              'post_content'   => '[awwlm_wishlist]',
              'post_type'      => 'page',
              'post_author'    => 1,
              'post_status'    => 'publish',
              )
          );
         update_option('awwlm_wishlist_page', $page_id);
         // return $page_id;
      }

    }

    function awwlm_register_post_types()
    {
      $post_type = AWWLM_POST_TYPE;
      $labels = array(
          'name' => __('Wishlist', 'aco-wishlist-for-woocommerce'),
          'singular_name' => __('Wishlist', 'aco-wishlist-for-woocommerce'),
          'name_admin_bar' => 'AWWLM_Form',
          'add_new' => _x('Add New Wishlist', $post_type, 'aco-wishlist-for-woocommerce'),
          'add_new_item' => sprintf(__('Add New %s', 'aco-wishlist-for-woocommerce'), 'Form'),
          'edit_item' => sprintf(__('Edit %s', 'aco-wishlist-for-woocommerce'), 'Form'),
          'new_item' => sprintf(__('New %s', 'aco-wishlist-for-woocommerce'), 'Form'),
          'all_items' => sprintf(__('Wishlists', 'aco-wishlist-for-woocommerce'), 'Form'),
          'view_item' => sprintf(__('View %s', 'aco-wishlist-for-woocommerce'), 'Form'),
          'search_items' => sprintf(__('Search %s', 'aco-wishlist-for-woocommerce'), 'Form'),
          'not_found' => sprintf(__('No %s Found', 'aco-wishlist-for-woocommerce'), 'Form'),
          'not_found_in_trash' => sprintf(__('No %s Found In Trash', 'aco-wishlist-for-woocommerce'), 'Form'),
          'parent_item_colon' => sprintf(__('Parent %s'), 'Form'),
          'menu_name' => 'Custom Wishlist Options'
      );
      $args = array(
          'labels' => apply_filters($post_type . '_labels', $labels),
          'description' => '',
          'public' => false,
          'publicly_queryable' => true,
          'exclude_from_search' => true,
          'show_ui' => false,
          // 'show_ui' => true,
          'show_in_nav_menus' => false,
          'query_var' => false,
          'can_export' => true,
          // 'rewrite' => false,
          //'rewrite' => array('slug' => 'wishlist/view'),
          'capability_type' => 'post',
          'has_archive' => false,
          'rest_base' => $post_type,
          'hierarchical' => false,
          'show_in_rest' => false,
          'rest_controller_class' => 'WP_REST_Posts_Controller',
          'supports' => array('title', 'author'),
          'menu_position' => 5,
          'menu_icon' => 'dashicons-admin-post',
          'taxonomies' => array()
      );
      register_post_type($post_type, apply_filters($post_type . '_register_args', $args, $post_type));

      register_taxonomy(
          AWWLM_WISHLIST_TYPE, $post_type, array(
          'label' => __('Type'),
          'rewrite' => array('slug' => 'wishlist-type'),
          'hierarchical' => true,
          'show_in_nav_menus' => false,
          //'publicly_queryable' => false,
          //'show_admin_column' => true
          )
      );

    }

    function awwlm_register_wishlist_terms()
    {
        $termArray = array('Public', 'Private', 'Shared');
        foreach($termArray as $term){
          if( !term_exists( $term, AWWLM_WISHLIST_TYPE ) ) {
            wp_insert_term( $term, AWWLM_WISHLIST_TYPE );
          }
        }
    }

    function awwlm_register_default_settings(){

      if ( false === get_option('awwlm_general_settings') ){
        $awwlm_general_settings = array(
          'success_popup' => 1,
        );
        add_option('awwlm_general_settings', $awwlm_general_settings, '', 'yes');
      }
      if ( false === get_option('awwlm_button_settings') ){
        $awwlm_button_settings = array(
          'listing_display' => 1,
          'add_icon' => 'heart',
          'added_icon' => 'filled',
        );
        add_option('awwlm_button_settings', $awwlm_button_settings, '', 'yes');
      }
      if ( false === get_option('awwlm_page_settings') ){
        $awwlm_page_settings = array(
          'wt_remove' => 1,
        );
        add_option('awwlm_page_settings', $awwlm_page_settings, '', 'yes');
      }



    }

    /* For templates */

    function awwlm_locate_template( $template_name, $template_path = '', $default_path = '' ) {

      // Set variable to search in the templates folder of theme.
      if ( ! $template_path ) :
        $template_path = 'AWWLM/';
      endif;

      // Set default plugin templates path.
      if ( ! $default_path ) :
        $default_path = AWWLM_PLUGIN_PATH . '/templates/'; // Path to the template folder
      endif;

      // Search template file in theme folder.
      $template = locate_template( array(
        $template_path . $template_name,
        $template_name
      ) );

      // Get plugins template file.
      if ( ! $template ) :
        $template = $default_path . $template_name;
      endif;

      return apply_filters( 'awwlm_locate_template', $template, $template_name, $template_path, $default_path );

    }

    function awwlm_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

      if ( is_array( $args ) && isset( $args ) ) :
        $atts = $args;
        extract( $args );
      endif;

      $template_file = $this->awwlm_locate_template( $template_name, $tempate_path, $default_path );

      if ( ! file_exists( $template_file ) ) :
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
        return;
      endif;

      ob_start();
      include( $template_file );
      return ob_get_clean();

    }

    /* For templates */


    function awwlm_get_formatted_date( $date ){

      $date_format = get_option( 'date_format' );
      $formatted = date($date_format, strtotime($date));
      return $formatted;

    }

    function awwlm_product_id( $id, $type = 'page', $return_original = true, $lang = null ){

        if ( 'default' === $lang ) {
          if ( defined('ICL_SITEPRESS_VERSION') ) {
            global $sitepress;
            $lang = $sitepress->get_default_language();
          } elseif( function_exists( 'pll_default_language' ) ) {
            $lang = pll_default_language( 'locale' );
          } else {
            $lang = null;
          }
        }

        $id = apply_filters( 'wpml_object_id', $id, $type, $return_original, $lang );

        return $id;

    }

    function awwlm_wpml_object_id( $element_id, $element_type = 'post', $return_original_if_missing = false, $ulanguage_code = null ) {
        if ( function_exists( 'wpml_object_id_filter' ) ) {
            return wpml_object_id_filter( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        } elseif ( function_exists( 'icl_object_id' ) ) {
            return icl_object_id( $element_id, $element_type, $return_original_if_missing, $ulanguage_code );
        } else {
            return $element_id;
        }

    }

    function awwlm_get_product_id( $productID ) {
			$a = $this->awwlm_wpml_object_id( $productID, 'product', true );
			return $a;
		}

    function awwlm_get_product( $productID ) {
    //  if ( empty( $this->product ) ) {
        $product = wc_get_product( $this->awwlm_get_product_id( $productID ) );

        if ( $product ) {
          $this->product = $product;
        }
      //}

      return $this->product;
    }


    /**
     * Permission Callback
     **/
    public function get_permission()
    {
        if (current_user_can('administrator') || current_user_can('manage_woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

}
