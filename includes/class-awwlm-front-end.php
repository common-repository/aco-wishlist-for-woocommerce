<?php

if (!defined('ABSPATH'))
    exit;

class AWWLM_Front_End
{

    private static $_instance = null;

    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;
    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;
    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    function __construct($file = '', $version = '1.0.0') {

        $this->_version = $version;
        $this->_token = AWWLM_TOKEN;

        /**
         * Check if WooCommerce is active
         * */
        if ($this->check_woocommerce_active()) {


            $this->file = $file;

            $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));

            AWWLM_Wishlist::instance();
            AWWLM_save_for_later::instance();
            AWWLM_woocommerce_custom_product_addons::instance();

            add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 15);
            add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);

            add_action( 'init', array($this, 'awwlm_register_shortcodes') );
            add_action( 'init', array($this, 'awwlm_register_buttons') );

            add_action('init', array( $this, 'awwlm_rewrite_rule'), 10, 0);
            add_filter( 'query_vars', array($this, 'awwlm_add_custom_query_var'), 0, 1 );

            add_action( 'woocommerce_before_customer_login_form', array($this, 'awwlm_myaccount_message') );
            add_action('wp_login', array($this, 'awwlm_login_action'), 10, 2);

            add_action( 'wp_ajax_awwlm_add_to_wishlist', array($this, 'awwlm_add_to_wishlist') );
            add_action( 'wp_ajax_nopriv_awwlm_add_to_wishlist', array($this, 'awwlm_add_to_wishlist') );
            add_action( 'wp_ajax_awwlm_remove_wishlist', array($this, 'awwlm_remove_wishlist') );
            add_action( 'wp_ajax_nopriv_awwlm_remove_wishlist', array($this, 'awwlm_remove_wishlist') );
            add_action( 'wp_ajax_awwlm_variation_wishlist', array($this, 'awwlm_variation_wishlist') );
            add_action( 'wp_ajax_nopriv_awwlm_variation_wishlist', array($this, 'awwlm_variation_wishlist') );

            add_action( 'wp_ajax_awwlm_remove_added_wishlist_page', array($this, 'awwlm_remove_added_wishlist_page') );
            add_action( 'wp_ajax_nopriv_awwlm_remove_added_wishlist_page', array($this, 'awwlm_remove_added_wishlist_page') );

            add_action( 'woocommerce_add_to_cart', array( $this, 'awwlm_remove_from_wishlist_after_add_to_cart' ) );

            add_filter( 'woocommerce_post_class', array( $this, 'awwlm_add_products_class_on_loop' ) );

            $awwlm_gs = get_option('awwlm_general_settings');
            if( isset($awwlm_gs['myaccount_link']) && $awwlm_gs['myaccount_link'] == 1){
              add_filter ( 'woocommerce_account_menu_items', array( $this, 'awwlm_account_wishlist_menu') );
              add_filter( 'woocommerce_get_endpoint_url', array( $this, 'awwlm_account_wishlist_endpoint'), 10, 4 );
            }


        }


    }


    public function check_woocommerce_active() {
        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            return true;
        }
        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins['woocommerce/woocommerce.php']))
                return true;
        }
        return false;
    }

    public static function instance($parent) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
        }
        return self::$_instance;
    }

    public function frontend_enqueue_styles($hook = '') {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);

        wp_enqueue_style($this->_token . '-frontend');

        $this->awwlm_enqueue_custom_style();
    }

    public function frontend_enqueue_scripts() {

        // wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend.js', array('jquery'), $this->_version, true);
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend-min.js', array('jquery'), $this->_version, true);
        wp_register_script($this->_token . '-popupjs', esc_url($this->assets_url) . 'plugins/popupjs.js', array('jquery'), $this->_version, true);

        wp_enqueue_script($this->_token . '-popupjs');
        wp_enqueue_script($this->_token . '-frontend');

        $login_msg = '<div class="popuparea">
        <p>'.$this->awwlm_login_message().'</p>
        <a href="'.esc_url(get_permalink( wc_get_page_id( 'myaccount' ) )).'" >'.__('Login', 'aco-wishlist-for-woocommerce').'</a>
        </div>';

        $added_to_cart_message = '<div class="woocommerce-notices-wrapper"><div class="woocommerce-message" role="alert">'.apply_filters( 'awwlm_added_to_cart_message', __( 'Product added to cart successfully', 'aco-wishlist-for-woocommerce' )).'</div></div>';

        $awwlm_gs = get_option('awwlm_general_settings');
        $success_popups = ( isset($awwlm_gs['success_popup']) && $awwlm_gs['success_popup'] == 1) ? 'yes' : false;
        $awwlm_ps = get_option('awwlm_page_settings');
        $remove_product_added_cart = ( isset($awwlm_ps['remove_added_cart']) && $awwlm_ps['remove_added_cart'] == 1 ) ? 'yes' : false;
        $redirect_cart = ( isset($awwlm_ps['redirect_cart']) && $awwlm_ps['redirect_cart'] == 1 ) ? 'yes' : false;

        wp_localize_script($this->_token . '-frontend', 'AWWLMSettings', array(
            'ajaxurl' =>  admin_url('admin-ajax.php'),
            'carturl' =>  wc_get_cart_url(),
            'asseturl' =>  plugin_dir_url( __DIR__ ).'/assets/',
            'multi_wishlist' =>  false,
            'show_popup' => $success_popups,
            'redirect_to_cart' =>  $redirect_cart,
            'remove_from_wishlist_after_add_to_cart' => $remove_product_added_cart,
            'strings' =>  array(
              'login_msg' => $login_msg,
              'added_to_cart_message' => $added_to_cart_message,
            ),
            'hash_key' => $this->awwlm_get_hash_key(),
            'userid' =>  get_current_user_id(),
        ));
    }

    function awwlm_enqueue_custom_style() {
			$custom_css = $this->awwlm_build_custom_css();
			if( $custom_css ) {
				$handle = $this->_token . '-frontend';
				wp_add_inline_style( $handle, $custom_css );
			}
		}

    function awwlm_get_hash_key(){
      return 'awwlm_wishlist_data_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() );
    }


        function awwlm_add_custom_query_var( $vars ){
          $vars[] = 'view';
          return $vars;
        }

        function awwlm_rewrite_rule() {

          $pageID = get_option('awwlm_wishlist_page');
          $slug = get_post_field( 'post_name', $pageID );

          add_rewrite_tag('%view%', '([^&]+)' );

          add_rewrite_rule('^'.$slug.'/([^/]*)/?','index.php?pagename='.$slug.'&view=$matches[1]','top');

          if( wc_get_page_id( 'shop' ) == get_option('awwlm_wishlist_page') ){
            add_rewrite_rule('^'.$slug.'/([^/]*)/?','index.php?post_type=product&view=$matches[1]','top');
          }

          flush_rewrite_rules();

        }


    public function awwlm_register_buttons(){

      $positions = array(
        // 'after_add_to_cart' => array('hook' => 'woocommerce_single_product_summary', 'priority' => 31),
        'after_add_to_cart' => array('hook' => 'woocommerce_after_add_to_cart_form', 'priority' => 10),
        'before_add_to_cart' => array('hook' => 'woocommerce_before_add_to_cart_form', 'priority' => 20),
        //'before_add_to_cart' => array('hook' => 'woocommerce_before_add_to_cart_button', 'priority' => 20),
        'after_thumbnail' => array('hook' => 'woocommerce_product_thumbnails', 'priority' => 21),
        'after_summary' => array('hook' => 'woocommerce_after_single_product_summary', 'priority' => 11),
      );

      $awwlm_bs = get_option('awwlm_button_settings');
      $position = (isset($awwlm_bs['product_position'])) ? $awwlm_bs['product_position'] : '';

      if ( $position == '' ){
        $position = 'after_add_to_cart';
      }
      if ( $position != 'shortcode' && isset( $positions[ $position ] ) ) {
				add_action( $positions[ $position ]['hook'], array( $this, 'awwlm_show_button' ), $positions[ $position ]['priority'] );
			}


      $listing_display = ( isset($awwlm_bs['listing_display']) && $awwlm_bs['listing_display'] == 1) ? $awwlm_bs['listing_display'] : '';
      $ListingPosition = ( isset($awwlm_bs['listing_position']) ) ? $awwlm_bs['listing_position'] : '';

      if( $listing_display == 1 ){

        $ListingPositions = array(
          'after_add_to_cart' => array('hook' => 'woocommerce_after_shop_loop_item', 'priority' => 15),
          'before_add_to_cart' => array('hook' => 'woocommerce_after_shop_loop_item', 'priority' => 7),
          'before_image' => array('hook' => 'woocommerce_before_shop_loop_item', 'priority' => 5),
        );

        if ( $ListingPosition == '' ){
          $ListingPosition = 'after_add_to_cart';
        }

        if ( $ListingPosition != 'shortcode' && isset( $ListingPositions[ $ListingPosition ] ) ) {
          add_action( $ListingPositions[ $ListingPosition ]['hook'], array( $this, 'awwlm_show_button' ), $ListingPositions[ $ListingPosition ]['priority'] );
        }

      }


    }

    public function awwlm_show_button(){
      echo do_shortcode( "[awwlm_add_to_wishlist]" );
    }

    function awwlm_register_shortcodes()
    {
       add_shortcode('awwlm_wishlist', array($this, 'awwlm_shortcode_wishlist_function'));
       add_shortcode('awwlm_add_to_wishlist', array($this, 'awwlm_shortcode_add_to_wishlist_function'));
    }

    function awwlm_shortcode_add_to_wishlist_function( $atts, $content = null ) {

        global $product;

		$current_product = ( isset( $atts['product_id'] ) ) ? wc_get_product( $atts['product_id'] ) : false;
		$current_product = $current_product ? $current_product : $product;
		if ( ! $current_product || ! $current_product instanceof WC_Product ) {
			return '';
		}
		$product = $current_product;

        $productid = $product->get_id();
        $product_id = $this->awwlm_product_id( $productid, 'product', true, 'default' );

        $product_type = $product->get_type();
        $parent_product_id = $product->get_parent_id();
        $data_product_id = $product_id;
        if ( $this->awwlm_is_single() && $product->get_type() == 'variable' ) {
          $data_product_id = $productid;
        }
        //$parent_product_id = $parent_product_id ? $parent_product_id : $product_id;
        $parent_product_id = $parent_product_id ? $parent_product_id : $data_product_id;

        $awwlm_general_settings = get_option('awwlm_general_settings');
        $require_login = (isset($awwlm_general_settings['require_login']) ? $awwlm_general_settings['require_login'] : 'no');
        $redirect_login = (isset($awwlm_general_settings['redirect_login']) ? $awwlm_general_settings['redirect_login'] : 'no');


        $cls_single = ($this->awwlm_is_single() == 1 ? 'wish-detail' : '');
        $behaviour = $this->awwlm_get_wishlist_behaviour();
        $success_popup = $behaviour['success_popup'];
        $success_redirect = $behaviour['success_redirect'];
        /*
        $popup_view_text = $behaviour['popup_view_text'];
        $popup_added_text = $behaviour['popup_added_text'];
        $popup_exists_text = $behaviour['popup_exists_text'];
        */
        $after_added = $behaviour['after_added'];
        $show_in_loop = $behaviour['show_in_loop'];
        $add_to_text = $behaviour['add_to_text'];
        $added_text = $behaviour['added_text'];
        $browse_text = $behaviour['browse_text'];
        $remove_text = $behaviour['remove_text'];
        $exist_text = $behaviour['exist_text'];
        $button_classes = $behaviour['button_classes'];

        $awwlm_bs = get_option('awwlm_button_settings');
        $wishlist_style = ( isset($awwlm_bs['add_wishlist_style']) ) ? $awwlm_bs['add_wishlist_style'] : 'link';

        $icons_list = $this->awwlm_get_icons();
        $icon = $icons_list['icon'];
        $icon_added = $icons_list['icon_added'];

        $template = 'button';
        $exists = $this->awwlm_check_product_in_wishlist_user( $product_id );
        //$exists = ($exists == 'default') ? 1 : 0;


        if($exists != 0 ){
          if($after_added == 'view_wishlist'){
            $template = 'view';
          } else if($after_added == 'remove_wishlist'){
            $template = 'remove';
          }
        }

        global $wp;
        $base_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        $pageID = get_option('awwlm_wishlist_page');
        $wishlist_url = $this->awwlm_get_wishlist_url($pageID);

        $atts = array(
              'base_url' => $base_url,
              'wishlist_url' => $wishlist_url,
              'template' => $template,
              'exists' => $exists,
              'productid' => $productid,
              'product_id' => $data_product_id,
              'product_type' => $product_type,
              'parent_product_id' => $parent_product_id,
              'require_login' => $require_login,
              'redirect_login' => $redirect_login,
              'icon' => $icon,
              'icon_added' => $icon_added,

              'success_popup' => $success_popup,
              'success_redirect' => $success_redirect,

              // 'popup_view_text' => $popup_view_text,
              // 'popup_added_text' => $popup_added_text,
              // 'popup_exists_text' => $popup_exists_text,

              'after_added' => $after_added,
              'show_in_loop' => $show_in_loop,

              'add_to_text' => $add_to_text,
              'added_text' => $added_text,
              'browse_text' => $browse_text,
              'remove_text' => $remove_text,
              'exist_text' => $exist_text,

              'wishlist_style' => $wishlist_style,

				      'button_classes' => $button_classes,
				      'container_classes' => $cls_single,
        );

        $wsettings = new AWWLM_Wishlist();
        $return_string = $wsettings->awwlm_get_template('add-to-wishlist.php', $atts );

        return $return_string;
    }

    function awwlm_get_icons(){

      $add_icon_custom = $added_icon_custom = '';

      $awwlm_bs = get_option('awwlm_button_settings');
      $add_icon = ( isset($awwlm_bs['add_icon']) ) ? $awwlm_bs['add_icon'] : '';
      $add_icon_c = ( isset($awwlm_bs['add_icon_custom']) ) ? $awwlm_bs['add_icon_custom'] : '';

      if($add_icon == 'custom'){
        $add_icon_custom = $add_icon_c;
      }
      if( $add_icon_custom != '' ){
        $icon = '<img class="awwlm-icon" src="'.$add_icon_custom["image"].'" alt="" >';
      } else if( $add_icon != '' && $add_icon != 'none' ) {
        $add_icon = ($add_icon == 'filled') ? 'heart' : 'heart-o';
        $icon = '<i class="awwlm-icon icon-'.$add_icon.'" ></i>';
      } else {
        $icon = '';
      }

      $added_icon = ( isset($awwlm_bs['added_icon']) ) ? $awwlm_bs['added_icon'] : '';
      $added_icon_c = ( isset($awwlm_bs['added_icon_custom']) ) ? $awwlm_bs['added_icon_custom'] : '';

      if($added_icon == 'custom'){
        $added_icon_custom = $added_icon_c;
      }
      if( $added_icon_custom != '' ){
        $icon_added = '<img class="awwlm-icon" src="'.$added_icon_custom["image"].'" alt="" >';
      } else if( $added_icon != '' && $added_icon != 'none' ) {
        $added_icon = ($added_icon == 'filled') ? 'heart' : 'heart-o';
        $icon_added = '<i class="awwlm-icon icon-'.$added_icon.'" ></i>';
      } else {
        $icon_added = '';
      }

      $retArr = array('icon'=>$icon, 'icon_added'=>$icon_added);

      return $retArr;

    }

    function awwlm_check_product_in_wishlist_user($pid){

      $exists = 0;
      if (is_user_logged_in()) {

        $current_user_id = get_current_user_id();
        $user_wishlists = $this->awwlm_get_user_wishlists($current_user_id);
        if( $user_wishlists ){
          foreach($user_wishlists as $user_list){
            $basket = get_post_meta( $user_list, AWWLM_WISHLIST_META_KEY, true);
            if (is_array($basket) && array_key_exists($pid,$basket)){
              // $exists = 1;
              $exists = $user_list;
              break;
            }
            break;
          }
        }


      } else {
        $awwlm_cookie = $this->awwlm_get_hash_key();
        if( isset($_COOKIE[$awwlm_cookie]) ){
            $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
            if (is_array($basket) && array_key_exists($pid,$basket)){
              $exists = 1;
            }
        }

      }

      return $exists;

    }

    function awwlm_check_product_in_wishlist($wishlist, $product){

      $exists = 0;
      $basket = get_post_meta( $wishlist, AWWLM_WISHLIST_META_KEY, true);
      if (is_array($basket) && array_key_exists($product,$basket)){
        $exists = 1;
      }
      return $exists;

    }

  function awwlm_myaccount_message() {
    if ( isset($_GET['wishlist_notice']) && $_GET['wishlist_notice'] == 1 ) {
      ?>
        <div class="woocommerce-info">
          <p><?php echo $this->awwlm_login_message(); ?></p>
        </div>
      <?php
    }
  }

  function awwlm_login_message() {
    return apply_filters( 'awwlm_login_message_text', __( 'You must be login to use Wishlist', 'aco-wishlist-for-woocommerce' ) );
  }


  function awwlm_check_multi_wishlist(){

    $allow_multi_wishlist = false;
    return $allow_multi_wishlist;

  }

  function awwlm_get_wishlist_url($pid){

    // $multi_wishlist = $this->awwlm_check_multi_wishlist();
    // if( $multi_wishlist == false ){
    //   $pageID = get_option('awwlm_wishlist_page');
    //   $pageUrl = get_permalink( $pageID );
    // } else {
    //   $pageUrl = get_permalink( $pid );
    // }

    $pageID = get_option('awwlm_wishlist_page');
    $pageUrl = get_permalink( $pageID );
    if( $pageID != $pid ){
      $slug = get_post_field( 'post_name', $pid );
      $pageUrl = $pageUrl.''.$slug.'/';
    }

    return $pageUrl;
  }

  function awwlm_get_user_wishlists($author_id){

    $user_wishlists = array();
    $args = array(
      'fields' => 'ids',
    	'author'        =>  $author_id,
    	'post_type'	=> AWWLM_POST_TYPE,
    	'posts_per_page' => -1
    );
    $posts = get_posts($args);
    if( count($posts) > 0 ){
      $user_wishlists = $posts;
    }

    return $user_wishlists;

  }


  function awwlm_remove_wishlist(){

    $productID = sanitize_text_field( wp_unslash( $_REQUEST['productID'] ));
    $productID = $this->awwlm_product_id( $productID, 'product', true, 'default' );

    $productOriginal = sanitize_text_field( wp_unslash( $_REQUEST['productOriginal'] ));
    $wishlistID = sanitize_text_field( wp_unslash( $_REQUEST['wishlistID'] ));

    $response = array();

    if (is_user_logged_in()) {
      $basket = get_post_meta( $wishlistID, AWWLM_WISHLIST_META_KEY, true);
      if (is_array($basket) && array_key_exists($productID,$basket)){
        unset($basket[$productID]);
      }
      $added = update_post_meta( $wishlistID, AWWLM_WISHLIST_META_KEY, $basket );
      $response['success'] = ($added != false) ? true : false;
    } else {
      $awwlm_cookie = $this->awwlm_get_hash_key();
      if( isset($_COOKIE[$awwlm_cookie]) ){
          $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
          if (is_array($basket) && array_key_exists($productID,$basket)){
            unset($basket[$productID]);
            $this->awwlm_setcookie($awwlm_cookie, $basket);
            $response['success'] = true;
          }
        }

    }

    $response['message'] = apply_filters( 'awwlm_product_removed_text', __( 'Product successfully removed', 'aco-wishlist-for-woocommerce' ) );

    $awwlm_gs = get_option('awwlm_general_settings');
    $success_popups = ( isset($awwlm_gs['success_popup']) && $awwlm_gs['success_popup'] == 1) ? 'yes' : false;
    $response['success_popup'] = $success_popups;

    $awwlm_bs = get_option('awwlm_button_settings');
    $add_to_text = ( isset($awwlm_bs['add_to_wishlist_text']) ) ? $awwlm_bs['add_to_wishlist_text'] : 'Add to wishlist';

    $btn_class = $this->awwlm_button_class();

    $icons_list = $this->awwlm_get_icons();
    $icon = $icons_list['icon'];
    $icon_added = $icons_list['icon_added'];

    $atts = array(
          'product_id' => $productID,
          'parent_product_id' => $productOriginal,
          'add_to_text' => $add_to_text,
          'icon' => $icon,
          'icon_added' => $icon_added,
          'button_classes' => $btn_class,
        );
    $wsettings = new AWWLM_Wishlist();
    $response['after_remove_link'] = json_encode($wsettings->awwlm_get_template('add-to-wishlist-button.php', $atts ));

    wp_send_json($response);
    die();
  }

  function awwlm_button_class(){

    $awwlm_bs = get_option('awwlm_button_settings');
    $btn_style = ( isset($awwlm_bs['add_wishlist_style']) ) ? $awwlm_bs['add_wishlist_style'] : '';
    switch($btn_style) {
      case "button_default":
        $btn_class = 'button alt';
        break;
      case "button_custom":
        $btn_class = 'button awwlm-btn alt';
        break;
      default:
        $btn_class = '';
    }

    $is_single = $this->awwlm_is_single();

    $awwlm_bs = get_option('awwlm_button_settings');
    $ListingPosition = ( isset($awwlm_bs['listing_position']) ) ? $awwlm_bs['listing_position'] : '';

    if ( $is_single != '1' && $ListingPosition == 'before_image' ){
      $btn_class = str_replace( 'button', '', $btn_class );
      $btn_class = str_replace( 'awwlm-btn', '', $btn_class );
    }

    return $btn_class;

  }

  function awwlm_setcookie($awwlm_cookie, $basket){

    $arr_cookie_options = array (
      'expires' => time() + 60*60*24*30, // 30 days
      'path' => '/',
      //'domain' => ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false,
      //'secure' => false,
      //'httponly' => true,
      'samesite' => 'strict' // None || Lax  || Strict
    );
    //setcookie($awwlm_cookie, json_encode($basket), $arr_cookie_options );

  	if (PHP_VERSION_ID < 70300) {
      setcookie($awwlm_cookie, json_encode($basket), time() + 60*60*24*30, '/' );
    } else {
      setcookie($awwlm_cookie, json_encode($basket), $arr_cookie_options );
    }


  }

  function awwlm_destroycookie($awwlm_cookie){

    $arr_cookie_options = array (
      'expires' => time() - 3600,
      'path' => '/',
      //'domain' => ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false,
      //'secure' => false,
      //'httponly' => true,
      'samesite' => 'strict' // None || Lax  || Strict
    );
    //unset($_COOKIE[$awwlm_cookie]);
    setcookie($awwlm_cookie, '', $arr_cookie_options );

  }


  function awwlm_variation_wishlist(){

    $productID = sanitize_text_field( wp_unslash( $_REQUEST['productID'] ));
    $productID = $this->awwlm_product_id( $productID, 'product', true, 'default' );

    $productOriginal = sanitize_text_field( wp_unslash( $_REQUEST['productOriginal'] ));
    $wishlistID = sanitize_text_field( wp_unslash( $_REQUEST['wishlistID'] ));

    $response = array('exists'=>false);
    //$check = $this->awwlm_check_product_in_wishlist($wishlistID, $productID);
    $check = $this->awwlm_check_product_in_wishlist_user( $productID);
    $wishlistID = ( $wishlistID == 0 ) ? $check : $wishlistID;

    $wishlist_url = $this->awwlm_get_wishlist_url($wishlistID);
    $behaviour = $this->awwlm_get_wishlist_behaviour();
    $atts = array(
          'product_id' => $productID,
          'parent_product_id' => $productOriginal,
          'after_added' => $behaviour['after_added'],
          'wishlist_url' => $wishlist_url,
          'add_to_text' => $behaviour['add_to_text'],
          'added_text' => $behaviour['added_text'],
          'exist_text' => $behaviour['exist_text'],
          'remove_text' => $behaviour['remove_text'],
          'browse_text' => $behaviour['browse_text'],
          'icon' => $behaviour['icon'],
          'icon_added' => $behaviour['icon_added'],
          'button_classes' => $behaviour['button_classes'],
        );

    if( $check != 0 ){
      $response['after_added_link'] = $this->awwlm_get_after_added($atts);
      $response['exists'] = true;
      $response['wishlist_id'] = $check;
    } else {
      $wsettings = new AWWLM_Wishlist();
      $response['after_added_link'] = json_encode($wsettings->awwlm_get_template('add-to-wishlist-button.php', $atts ));
      $response['exists'] = false;
      $response['wishlist_id'] = 0;
    }
    wp_send_json($response);
    die();

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

  function awwlm_add_to_wishlist(){


    $formData = array();
    // parse_str($_REQUEST['frmData'], $formData);
    parse_str($_POST['frmData'], $formData);
    $formData = serialize($formData);
    //error_log(print_r( $formData, true));

    // $formData = sanitize_text_field($_REQUEST['frmData']);
    //error_log(print_r( $formData, true));

    $productID = sanitize_text_field( wp_unslash( $_REQUEST['productID'] ));
    $productID = $this->awwlm_product_id( $productID, 'product', true, 'default' );
    $productOriginal = sanitize_text_field( wp_unslash( $_REQUEST['productOriginal'] ));
    //$productType = sanitize_text_field( wp_unslash( $_REQUEST['productType'] ));
    $wishlistID = sanitize_text_field( wp_unslash( $_REQUEST['wishlistID'] ));

    $multi_wishlist = $this->awwlm_check_multi_wishlist();

    $response = array('success' => false, 'after_added_link' => '', );

          $behaviour = $this->awwlm_get_wishlist_behaviour();

          $atts = array(
                'product_id' => $productID,
                'parent_product_id' => $productOriginal,
                'after_added' => $behaviour['after_added'],
                'wishlist_url' => '',
                'add_to_text' => $behaviour['add_to_text'],
                'added_text' => $behaviour['added_text'],
                'exist_text' => $behaviour['exist_text'],
                'remove_text' => $behaviour['remove_text'],
                'browse_text' => $behaviour['browse_text'],
                'icon' => $behaviour['icon'],
                'icon_added' => $behaviour['icon_added'],
                'button_classes' => $behaviour['button_classes'],
              );

    if (is_user_logged_in()) {

      $current_user_id = get_current_user_id();
      $user_wishlists = $this->awwlm_get_user_wishlists($current_user_id);

      if( $multi_wishlist == false ){
        if( count($user_wishlists) == 0 ){
          $getBack = $this->awwlm_create_wishlist($productID, $productOriginal, $current_user_id, $formData);
          $wish_post_id = $getBack['post_id'];
          $succ = $getBack['success'];
        } else {
          $id_wishlist = ( $wishlistID != 'default' ) ? $wishlistID : $user_wishlists[0];
          $getBack = $this->awwlm_update_wishlist($id_wishlist, $productID, $productOriginal, $current_user_id, $formData);
          $wish_post_id = $id_wishlist;
          $succ = $getBack['success'];
        }
      }



      if( $succ != 0){
          $wish_url = $this->awwlm_get_wishlist_url($wish_post_id);
          $response['success'] = true;
          if($succ == 1 ){
            $response['message'] = $behaviour['added_text'];
          } else if($succ == 2 ){
            $response['message'] = $behaviour['exist_text'];
          }
          $response['wishlist_id'] = $wish_post_id;
          $response['wishlist_url'] = $wish_url;
          $atts['wishlist_url'] = $wish_url;
          $atts['exist_text'] = $behaviour['added_text'];
          $response['after_added_link'] = $this->awwlm_get_after_added($atts);

      } else {
        $response['success'] = false;
        $response['message'] = 'Failed';
      }

      // $response['success_popup'] = ($behaviour['success_popup'] == 1 ) ? true : false;
      // $response['success_redirect'] = $behaviour['success_redirect'];
      // $response['after_added'] = $behaviour['after_added'];

      // if($behaviour['success_popup'] == 1 ){
      //   $response['success_popup'] = true;
      //   $response['success_redirect'] = $behaviour['success_redirect'];
      //   $response['popup_view_text'] = $behaviour['popup_view_text'];
      //   $response['popup_added_text'] = $behaviour['popup_added_text'];
      //   $response['popup_exists_text'] = $behaviour['popup_exists_text'];
      // }
      /*
      $response['after_added'] = $behaviour['after_added'];
      $response['add_to_text'] = $behaviour['add_to_text'];
      $response['added_text'] = $behaviour['added_text'];
      $response['browse_text'] = $behaviour['browse_text'];
      $response['remove_text'] = $behaviour['remove_text'];
      $response['exist_text'] = $behaviour['exist_text'];
      */

    } else {

      $pageID = get_option('awwlm_wishlist_page');
      $wishlist_url = $this->awwlm_get_wishlist_url($pageID);

      $currency = get_option('woocommerce_currency');
      $details = array(
        'original_id' => $productOriginal,
        'quantity' => 1,
        'original_price' => $this->awwlm_product_price($productID),
        'original_currency' => $currency,
        'on_sale' => $this->awwlm_is_on_sale($productID),
        'date_added' => date('j-n-Y'),
        'formData' => $formData,
      );
      $awwlm_cookie = $this->awwlm_get_hash_key();

      if( isset($_COOKIE[$awwlm_cookie]) ){
          $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
          if (is_array($basket) && array_key_exists($productID,$basket)){
            $response['success'] = true;
            $response['message'] = $behaviour['exist_text'];
          } else {
            $basket[$productID] = $details;
            $this->awwlm_setcookie($awwlm_cookie, $basket);
            $response['success'] = true;
            $response['message'] = $behaviour['added_text'];
          }

      } else {

        $values = array( $productID => $details);
        $this->awwlm_setcookie($awwlm_cookie, $values);
        $response['success'] = true;
		    $response['message'] = $behaviour['added_text'];

      }

      $response['wishlist_id'] = 'default';
      $response['wishlist_url'] = $wishlist_url;
      $atts['wishlist_url'] = $wishlist_url;
      $atts['exist_text'] = $behaviour['added_text'];
      $response['after_added_link'] = $this->awwlm_get_after_added($atts);


    }

    $response['success_popup'] = ($behaviour['success_popup'] == 1 ) ? true : false;
    $response['success_redirect'] = $behaviour['success_redirect'];
    $response['after_added'] = $behaviour['after_added'];

    wp_send_json($response);
    //return $response;

    die();
  }

  function awwlm_get_wishlist_behaviour(){
    $behaviour = array();

    $awwlm_gs = get_option('awwlm_general_settings');
    $success_popups = ( isset($awwlm_gs['success_popup']) && $awwlm_gs['success_popup'] == 1) ? $awwlm_gs['success_popup'] : 'no';
    $redirect_wishlists = ( isset($awwlm_gs['redirect_wishlist']) && $awwlm_gs['redirect_wishlist'] == 1) ? 'yes' : 'no';

    $behaviour['success_popup'] = $success_popups;
    $behaviour['success_redirect'] = $redirect_wishlists;

    $awwlm_bs = get_option('awwlm_button_settings');
    $listing_display = ( isset($awwlm_bs['listing_display']) && $awwlm_bs['listing_display'] == 1) ? $awwlm_bs['listing_display'] : '';
    $after_added = ( isset($awwlm_bs['after_added_to_wishlist']) ) ? $awwlm_bs['after_added_to_wishlist'] : 'view_wishlist';
    $add_to_text = ( isset($awwlm_bs['add_to_wishlist_text']) ) ? $awwlm_bs['add_to_wishlist_text'] : 'Add to wishlist';
    $added_text = ( isset($awwlm_bs['added_to_wishlist_text']) ) ? $awwlm_bs['added_to_wishlist_text'] : 'Product added';
    $browse_text = ( isset($awwlm_bs['browse_wishlist_text']) ) ? $awwlm_bs['browse_wishlist_text'] : 'Browse wishlist';
    $remove_text = ( isset($awwlm_bs['remove_wishlist_text']) ) ? $awwlm_bs['remove_wishlist_text'] : 'Remove wishlist';
    $exist_text = ( isset($awwlm_bs['exists_wishlist_text']) ) ? $awwlm_bs['exists_wishlist_text'] : 'Already exists';

    $behaviour['after_added'] = $after_added;
    $behaviour['show_in_loop'] = $listing_display;
    $behaviour['add_to_text'] = $add_to_text;
    $behaviour['added_text'] = $added_text;
    $behaviour['browse_text'] = $browse_text;
    $behaviour['remove_text'] = $remove_text;
    $behaviour['exist_text'] = $exist_text;

    $icons_list = $this->awwlm_get_icons();
    $icon = $icons_list['icon'];
    $icon_added = $icons_list['icon_added'];

    $behaviour['icon'] = $icon;
    $behaviour['icon_added'] = $icon_added;

    $btn_class = $this->awwlm_button_class();

    $behaviour['button_classes'] = $btn_class;

    return $behaviour;
  }

  function awwlm_is_single() {
		return (is_product() && ! in_array( wc_get_loop_prop( 'name' ), array( 'related', 'up-sells' ) ) && ! wc_get_loop_prop( 'is_shortcode' ));
	}

  function awwlm_get_after_added($atts){
    $wsettings = new AWWLM_Wishlist();
    $retz = '';
    switch ($atts['after_added']) {
      case "view_wishlist":
        $retz = json_encode($wsettings->awwlm_get_template('add-to-wishlist-view.php', $atts ));
        break;
      case "remove_wishlist":
        $retz = json_encode($wsettings->awwlm_get_template('add-to-wishlist-remove.php', $atts ));
        break;
      default:
        // add_wishlist
        $retz = json_encode($wsettings->awwlm_get_template('add-to-wishlist-button.php', $atts ));
    }
    return $retz;
  }

  function awwlm_create_wishlist($productID, $productOriginal, $current_user_id, $formData){

    /* $currency = get_woocommerce_currency(); */
    $currency = get_option('woocommerce_currency');

    $values = array( $productID => array(
      'original_id' => $productOriginal,
      'quantity' => 1,
      'original_price' => $this->awwlm_product_price($productID),
      'original_currency' => $currency,
      'on_sale' => $this->awwlm_is_on_sale($productID),
      'date_added' => date('j-n-Y'),
      'formData' => $formData,
    ));
    $added = false; $ret = array('post_id'=>'', 'success'=> 0);
    $token = $this->awwlm_generate_token();
    $post_id = wp_insert_post(
        array(
        'post_title'     => $token,
        'post_type'      => AWWLM_POST_TYPE,
        'post_author'    => $current_user_id,
        'post_status'    => 'publish',
        )
    );
    wp_set_object_terms( $post_id, 'public', AWWLM_WISHLIST_TYPE );
    if($post_id){
      $ret['post_id'] = $post_id;
      $added = add_post_meta( $post_id, AWWLM_WISHLIST_META_KEY, $values, true );
      $def = add_post_meta( $post_id, 'awwlm_is_default', 'yes', true );

      $awwlm_ps = get_option('awwlm_page_settings');
      $title = ( isset($awwlm_ps['def_wishlist_name']) ) ? $awwlm_ps['def_wishlist_name'] : __('My wishlist', 'aco-wishlist-for-woocommerce');
      $my_post = array(
          'ID'           => $post_id,
          'post_title'   => $title,
      );
      wp_update_post( $my_post );

    }
    $ret['success'] = ($added != false) ? 1 : 0;

    return $ret;
  }

  function awwlm_update_wishlist($wishlist, $product, $original, $user, $formData){

    $ret = array('post_id'=>'', 'success'=> 0 );
    $check = $this->awwlm_check_product_in_wishlist($wishlist, $product);
    if( $check == 1 ){
      $ret['post_id'] = $wishlist;
      $ret['success'] = 2;
    } else {

        $basket = get_post_meta( $wishlist, AWWLM_WISHLIST_META_KEY, true);
          /* $currency = get_woocommerce_currency(); */
          $currency = get_option('woocommerce_currency');

          $values = array(
            'original_id' => $original,
            'quantity' => 1,
            'original_price' => $this->awwlm_product_price($product),
            'original_currency' => $currency,
            'on_sale' => $this->awwlm_is_on_sale($product),
            'date_added' => date('j-n-Y'),
            'formData' => $formData,
          );

          $basket[$product] = $values;
          $added = update_post_meta( $wishlist, AWWLM_WISHLIST_META_KEY, $basket );
          $ret['post_id'] = $wishlist;
          $ret['success'] = ($added != false) ? 1 : 0;

    }


    return $ret;

  }

  function awwlm_product_price($id){
    $product = wc_get_product( $id );
    return $product->get_price();
  }

  function awwlm_is_on_sale($id){
    $product = wc_get_product( $id );
    return ( ($product->is_on_sale()) ? 'yes' : 'no' );
  }

  function awwlm_generate_token() {
      $length = 12;
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      // $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, $charactersLength - 1)];
      }
      return $randomString;
  }


  function awwlm_shortcode_wishlist_function( $atts, $content = null ) {

      $multi_wishlist = $this->awwlm_check_multi_wishlist();
      $wishlist_url = '';

      $default_menus = array();
      $endpoints = wc_get_account_menu_items();
      foreach ( $endpoints as $endpoint => $title ) {
        $default_menus[] = $endpoint;
	    }


      if( get_query_var( 'view' ) !== '' && !in_array(get_query_var( 'view' ), $default_menus)  ){

        $slug = get_query_var( 'view' );
        $post = get_page_by_path($slug, '', AWWLM_POST_TYPE);

        if ($post) {
          $wishlistID = $post->ID;
          $basket = get_post_meta( $wishlistID, AWWLM_WISHLIST_META_KEY, true);
          $wishlist_url = $this->awwlm_get_wishlist_url($wishlistID);
          $owner = $post->post_author;
        } else {
          $wishlistID = '';
          $basket = array();
          $wishlist_url = '';
          $owner = '';
        }

      } else {

        $wishlistID = '';
        $basket = array();
        if (is_user_logged_in()) {
          $current_user_id = get_current_user_id();
          $user_wishlists = $this->awwlm_get_user_wishlists($current_user_id);

          $owner = $current_user_id;
          if( $user_wishlists ){
            foreach($user_wishlists as $user_list){
              $basket = get_post_meta( $user_list, AWWLM_WISHLIST_META_KEY, true);
              $wishlistID = $user_list;
              $wishlist_url = $this->awwlm_get_wishlist_url($wishlistID);
			        break;
            }
          }
        } else {
          $awwlm_cookie = $this->awwlm_get_hash_key();
          if( isset($_COOKIE[$awwlm_cookie]) ){
            $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
            $wishlistID = 'default';

          }
          $wishlist_url = $this->awwlm_get_wishlist_url(get_option('awwlm_wishlist_page'));
          $owner = '';

              // error_log( print_r( @json_decode(@$basket), true));
        }

      }

        //var_dump( $basket );

        $has_permission = ( get_current_user_id() == $owner ) ? true : false;

        $count = sizeof($basket);
        $template = (!empty($basket)) ? 'view' : 'empty';

        $awwlm_general_settings = get_option('awwlm_general_settings');
        $require_login = (isset($awwlm_general_settings['require_login']) ? $awwlm_general_settings['require_login'] : 'no');
        $login_message = $this->awwlm_login_message();

        $awwlm_ps = get_option('awwlm_page_settings');
        $default_wishlist_title = ( isset($awwlm_ps['def_wishlist_name']) ) ? $awwlm_ps['def_wishlist_name'] : __('My wishlist', 'aco-wishlist-for-woocommerce');
        $add_to_cart_text = ( isset($awwlm_ps['def_add_cart_text']) ) ? $awwlm_ps['def_add_cart_text'] : __('Add to cart', 'aco-wishlist-for-woocommerce');
        $remove_product_added_cart = ( isset($awwlm_ps['remove_added_cart']) ) ? $awwlm_ps['remove_added_cart'] : 'no';
        $redirect_cart = ( isset($awwlm_ps['redirect_cart']) ) ? $awwlm_ps['redirect_cart'] : 'no';
        $show_price = ( isset($awwlm_ps['wt_price']) ) ? $awwlm_ps['wt_price'] : '';
        $show_stock = ( isset($awwlm_ps['wt_stock']) ) ? $awwlm_ps['wt_stock'] : '';
        $show_date_added = ( isset($awwlm_ps['wt_date']) ) ? $awwlm_ps['wt_date'] : '';
        $show_add_to_cart = ( isset($awwlm_ps['wt_add_cart']) ) ? $awwlm_ps['wt_add_cart'] : '';
        $show_remove = ( isset($awwlm_ps['wt_remove']) ) ? $awwlm_ps['wt_remove'] : '';
        $show_quantity = ( isset($awwlm_ps['wt_qty']) ) ? $awwlm_ps['wt_qty'] : '';
        $show_move_wishlist = ( isset($awwlm_ps['wt_move_wishlist']) ) ? $awwlm_ps['wt_move_wishlist'] : '';
        $share_wishlist = ( isset($awwlm_ps['share_wishlist']) ) ? $awwlm_ps['share_wishlist'] : '';
        $share_facebook = ( isset($awwlm_ps['share_fb']) ) ? $awwlm_ps['share_fb'] : '';
        $share_twitter = ( isset($awwlm_ps['share_tw']) ) ? $awwlm_ps['share_tw'] : '';
        $share_pinterest = ( isset($awwlm_ps['share_pin']) ) ? $awwlm_ps['share_pin'] : '';
        $share_email = ( isset($awwlm_ps['share_em']) ) ? $awwlm_ps['share_em'] : '';
        $share_whatsapp = ( isset($awwlm_ps['share_wp']) ) ? $awwlm_ps['share_wp'] : '';
        $share_url = ( isset($awwlm_ps['share_url']) ) ? $awwlm_ps['share_url'] : '';
        $share_title = ( isset($awwlm_ps['share_title']) ) ? $awwlm_ps['share_title'] : '';
        $share_text = ( isset($awwlm_ps['share_text']) ) ? $awwlm_ps['share_text'] : '';
        $share_link_url = ( isset($awwlm_ps['share_img_url']) ) ? $awwlm_ps['share_img_url'] : '';


        $atts = array(
          'has_permission' => $has_permission,
          'wishlist_url' => $wishlist_url,
          'count' => $count,
          'wishlist' => $wishlistID,
          'wishlist_items' => $basket,
          'template' => $template,
          'require_login' => $require_login,
          'login_message' => $login_message,
          'multi_wishlist' => $multi_wishlist,

          'default_wishlist_title' => $default_wishlist_title,
          'add_to_cart_text' => $add_to_cart_text,
          'remove_product_added_cart' => $remove_product_added_cart,
          'redirect_cart' => $redirect_cart,
          'show_price' => $show_price,
          'show_stock' => $show_stock,
          'show_date_added' => $show_date_added,
          'show_add_to_cart' => $show_add_to_cart,
          'show_remove' => $show_remove,
          'show_quantity' => $show_quantity,
          'show_move_wishlist' => $show_move_wishlist,

          'share_wishlist' => $share_wishlist,
          'share_link' => $wishlist_url,
          'share_facebook' => $share_facebook,
          'share_twitter' => $share_twitter,
          'share_pinterest' => $share_pinterest,
          'share_email' => $share_email,
          'share_whatsapp' => $share_whatsapp,
          'share_url' => $share_url,
          'share_title' => $share_title,
          'share_text' => $share_text,
          'share_link_url' => $share_link_url,


        );

        $this->awwlm_alter_add_to_cart_button();

        $wsettings = new AWWLM_Wishlist();
        $return_string = $wsettings->awwlm_get_template('wishlist.php', $atts );

        $this->awwlm_restore_add_to_cart_button();

        return $return_string;

      }

      function awwlm_alter_add_to_cart_button(){
        add_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'awwlm_alter_add_to_cart_args' ) );
        add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'awwlm_alter_add_to_cart_text' ), 10, 2 );
        add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'awwlm_alter_add_to_cart_url' ), 10, 2 );
      }

      function awwlm_restore_add_to_cart_button(){

        remove_filter( 'woocommerce_loop_add_to_cart_args', array( $this, 'awwlm_alter_add_to_cart_args' ) );
        remove_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'awwlm_alter_add_to_cart_text' ) );
        remove_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'awwlm_alter_add_to_cart_url' ) );
      }

      function awwlm_alter_add_to_cart_text($text, $product){

        // $url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );

        $awwlm_ps = get_option('awwlm_page_settings');
        $add_to_cart_text = ( isset($awwlm_ps['def_add_cart_text']) && $awwlm_ps['def_add_cart_text'] != '' ) ? $awwlm_ps['def_add_cart_text'] : __('Add to cart', 'aco-wishlist-for-woocommerce');
        $stock_status = $product->get_stock_status();
        $label = ( $product->is_type( 'variable' ) || $stock_status == 'outofstock' ) ? $text : $add_to_cart_text;
        return $label;

      }

      function awwlm_alter_add_to_cart_args($args){

        $custom_class = array( 'add_to_cart', 'awwlm_add_to_cart_button', 'mr16');
        $classes = isset( $args['class'] ) ? explode( ' ', $args['class'] ) : array();
        $classes = array_unique(array_merge($classes, $custom_class));

        $args['class'] = implode( ' ', $classes );

        return $args;

      }

      function awwlm_alter_add_to_cart_url( $url, $product ){

        $awwlm_ps = get_option('awwlm_page_settings');
        $redirect_cart = ( isset($awwlm_ps['redirect_cart']) ) ? $awwlm_ps['redirect_cart'] : '';
        if( $redirect_cart == 1 ){

          if( $product->is_type( array( 'simple', 'variation' ) ) ){
  					$url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );
  				}

        }
        return $url;

      }

      function awwlm_remove_from_wishlist_after_add_to_cart(){

        $awwlm_ps = get_option('awwlm_page_settings');
        $remove_product_added_cart = ( isset($awwlm_ps['remove_added_cart']) ) ? $awwlm_ps['remove_added_cart'] : '';
        $product = $wishlist = '';

        if ( $remove_product_added_cart == 1 ) {
          if ( isset( $_REQUEST['remove_from_wishlist_after_add_to_cart'] ) ) {
    				$product = intval( sanitize_text_field( $_REQUEST['remove_from_wishlist_after_add_to_cart'] ) );
    			}
          if ( isset( $_REQUEST['wishlist_id'] ) ) {
            $wishlist = sanitize_text_field( wp_unslash( $_REQUEST['wishlist_id'] ) );
          }

          if($product && $wishlist){
            $this->awwlm_remove_wishlist_added($product, $wishlist);
          }

  			}

      }

      function awwlm_remove_wishlist_added($productID, $wishlistID){

        if (is_user_logged_in()) {
          $basket = get_post_meta( $wishlistID, AWWLM_WISHLIST_META_KEY, true);
          if (is_array($basket) && array_key_exists($productID,$basket)){
            unset($basket[$productID]);
          }
          update_post_meta( $wishlistID, AWWLM_WISHLIST_META_KEY, $basket );
        } else {
          $awwlm_cookie = $this->awwlm_get_hash_key();
          if( isset($_COOKIE[$awwlm_cookie]) ){
              $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
              if (is_array($basket) && array_key_exists($productID,$basket)){
                unset($basket[$productID]);
                $this->awwlm_setcookie($awwlm_cookie, $basket);
              }
            }
        }

      }

      function awwlm_remove_added_wishlist_page(){

        $product = sanitize_text_field( wp_unslash( $_REQUEST['product'] ));
        $wishlist = sanitize_text_field( wp_unslash( $_REQUEST['wishlist'] ));
        $response = array('message' => '', );

        if($product && $wishlist){
          $this->awwlm_remove_wishlist_added($product, $wishlist);
        }

        $wishlist_size = $this->awwlm_remove_wishlist_size($wishlist);
        $msg = ($wishlist_size > 0) ? '' : apply_filters( 'awwlm_no_product_wishlist_text', __( 'Your Wishlist is currently empty', 'aco-wishlist-for-woocommerce' ) );
        $response['message'] = $msg;
        wp_send_json($response);
        die();

      }

      function awwlm_remove_wishlist_size($wishlist){
        $size = 0;
        if( $wishlist != 'default'){
          $basket = get_post_meta( $wishlist, AWWLM_WISHLIST_META_KEY, true);
          $size = count($basket);
        } else {
          $awwlm_cookie = $this->awwlm_get_hash_key();
          if( isset($_COOKIE[$awwlm_cookie]) ){
            $basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);
            $size = count($basket);
          }
        }
        return $size;
      }

      function awwlm_login_action( $user_login, $user ) {

        $user = $user->ID;
        $multi_wishlist = $this->awwlm_check_multi_wishlist();
        if( $multi_wishlist == false ){
        $awwlm_cookie = $this->awwlm_get_hash_key();
        if( isset($_COOKIE[$awwlm_cookie]) ){
          $cookie_basket = json_decode(sanitize_text_field(wp_unslash( $_COOKIE[$awwlm_cookie] )), true);

          $user_wishlists = $this->awwlm_get_user_wishlists($user);

          if( $user_wishlists ){
            foreach($user_wishlists as $user_list){
              $basket = get_post_meta( $user_list, AWWLM_WISHLIST_META_KEY, true);

              $newbasket = $basket+$cookie_basket;
              $added = update_post_meta( $user_list, AWWLM_WISHLIST_META_KEY, $newbasket );
              if($added == true){
                $this->awwlm_destroycookie($awwlm_cookie);
              }
              break;
            }
          }

        }
        }


      }


      function awwlm_add_products_class_on_loop( $classes ){

        if( $this->awwlm_is_single() ){
          return $classes;
        }

        $awwlm_bs = get_option('awwlm_button_settings');
        $listing_display = ( isset($awwlm_bs['listing_display']) && $awwlm_bs['listing_display'] == 1) ? $awwlm_bs['listing_display'] : '';
        if( $listing_display != 1 ){
          return $classes;
        }

        $ListingPosition = ( isset($awwlm_bs['listing_position']) ) ? $awwlm_bs['listing_position'] : '';
        if ( $ListingPosition == 'shortcode' ){
          return $classes;
        }

        $classes[] = "awwlm_wrap awwlm-add-to-wishlist-$ListingPosition";

			  return $classes;

      }

      function awwlm_build_custom_css(){

        $custom_css = '';
        $awwlm_bs = get_option('awwlm_button_settings');
        $btn_style = ( isset($awwlm_bs['add_wishlist_style']) ) ? $awwlm_bs['add_wishlist_style'] : '';
        if($btn_style == 'button_custom') {
          $normal_style = $hover_style = array();

          $bgc = ( isset($awwlm_bs['wishlist_button_bg_Hex']) ) ? $awwlm_bs['wishlist_button_bg_Hex'] : '';
          $btc = ( isset($awwlm_bs['wishlist_button_txt_Hex']) ) ? $awwlm_bs['wishlist_button_txt_Hex'] : '';
          $btb = ( isset($awwlm_bs['wishlist_button_br_Hex']) ) ? $awwlm_bs['wishlist_button_br_Hex'] : '';

          $bgcH = ( isset($awwlm_bs['wishlist_button_bgH_Hex']) ) ? $awwlm_bs['wishlist_button_bgH_Hex'] : '';
          $btcH = ( isset($awwlm_bs['wishlist_button_txtH_Hex']) ) ? $awwlm_bs['wishlist_button_txtH_Hex'] : '';
          $btbH = ( isset($awwlm_bs['wishlist_button_brH_Hex']) ) ? $awwlm_bs['wishlist_button_brH_Hex'] : '';

          $btnR = ( isset($awwlm_bs['wishlist_button_radius']) ) ? $awwlm_bs['wishlist_button_radius'] : '';

          if( $bgc ){
            $normal_style[] = 'background-color:'.$bgc;
          }
          if( $btc ){
            $normal_style[] = 'color:'.$btc;
          }
          if( $btb ){
            $normal_style[] = 'border: 1px solid '.$btb;
            $normal_style[] = 'border-color:'.$btb;
          }
          if( $btnR ){
            $normal_style[] = 'border-radius:'.$btnR.'px';
          }
          if($normal_style){
            $custom_css .= '.woocommerce .awwlm-add-button .alt.awwlm-btn, .wish-detail .awwlm-add-button a {'.implode(";",$normal_style).'}';
          }

          if( $bgcH ){
            $hover_style[] = 'background-color:'.$bgcH;
          }
          if( $btcH ){
            $hover_style[] = 'color:'.$btcH;
          }
          if( $btbH ){
            $hover_style[] = 'border: border: 1px solid '.$btbH;
            $hover_style[] = 'border-color:'.$btbH;
          }
          if($hover_style){
            $custom_css .= '.woocommerce .awwlm-add-button .alt.awwlm-btn:hover, .wish-detail .awwlm-add-button a:hover {'.implode(";",$hover_style).'}';
          }
        }

        $custom_css .= ( isset($awwlm_bs['custom_css']) ) ? $awwlm_bs['custom_css'] : '';

        return $custom_css;


      }



          function awwlm_account_wishlist_menu( $menu_links ){

          	$new = array( 'awwlmwishlistmaurl' => __('Wishlist', 'aco-wishlist-for-woocommerce') );
          	$menu_links = array_slice( $menu_links, 0, 1, true )
          	+ $new
          	+ array_slice( $menu_links, 1, NULL, true );

          	return $menu_links;

          }

          function awwlm_account_wishlist_endpoint( $url, $endpoint, $value, $permalink ){

          	if( $endpoint === 'awwlmwishlistmaurl' ) {

              $pageID = get_option('awwlm_wishlist_page');
              $url = get_permalink( $pageID );

          	}
          	return $url;

          }


}
