<?php

if (!defined('ABSPATH'))
    exit;

class AWWLM_Api
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
        add_action('rest_api_init', function () {

            register_rest_route('awwlm/v1', '/awwlm_general_settings/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awwlm_general_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awwlm/v1', '/awwlm_general_settings/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awwlm_general_settings'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awwlm/v1', '/awwlm_add_to_wishlist/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awwlm_add_to_wishlist'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awwlm/v1', '/awwlm_add_to_wishlist/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awwlm_add_to_wishlist'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awwlm/v1', '/awwlm_wishlist_page/', array(
                'methods' => 'POST',
                'callback' => array($this, 'awwlm_wishlist_page'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awwlm/v1', '/awwlm_wishlist_page/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'awwlm_wishlist_page'),
                'permission_callback' => array($this, 'get_permission')
            ));

            register_rest_route('awwlm/v1', '/save_for_later_page/', array(
                'methods' => 'POST',
                'callback' => array($this, 'save_for_later_page'),
                'permission_callback' => array($this, 'get_permission')
            ));
            register_rest_route('awwlm/v1', '/save_for_later_page/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => array($this, 'save_for_later_page'),
                'permission_callback' => array($this, 'get_permission')
            ));


        });
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

    /**
     * @param $data
     * @return WP_REST_Response
     * @throws Exception
     */

        function awwlm_general_settings($data){

          if( ! $data['id'] ) {

              $data = $data->get_params();

              $awwlm_general_settings = array(
                'require_login' => isset($data['awwlm_require_login']) ? $data['awwlm_require_login'] : 0,
                'redirect_login' => isset($data['awwlm_redirect_login']) ? $data['awwlm_redirect_login'] : 0,
                'myaccount_link' => isset($data['awwlm_add_myaccount_link']) ? $data['awwlm_add_myaccount_link'] : 0,
                'success_popup' => isset($data['awwlm_success_popup']) ? $data['awwlm_success_popup'] : 0,
                'redirect_wishlist' => isset($data['awwlm_popup_redirect_wishlist']) ? $data['awwlm_popup_redirect_wishlist'] : 0,
              );

              if ( false === get_option('awwlm_general_settings') ){
                    add_option('awwlm_general_settings', $awwlm_general_settings, '', 'yes');
              }  else {
                    update_option('awwlm_general_settings', $awwlm_general_settings);
              }

          }

            $result['awwlm_general_settings'] = get_option('awwlm_general_settings') ? get_option('awwlm_general_settings') : '';

            return new WP_REST_Response($result, 200);
        }


        function awwlm_add_to_wishlist($data){

          if( ! $data['id'] ) {
              $data = $data->get_params();

              $awwlm_button_settings = array(
                'listing_display' => isset($data['awwlm_listing_display']) ? $data['awwlm_listing_display'] : 0,
                'listing_position' => isset($data['awwlm_listing_position']) ? $data['awwlm_listing_position'] : '',
                'product_position' => isset($data['awwlm_product_position']) ? $data['awwlm_product_position'] : '',
                'after_added_to_wishlist' => isset($data['awwlm_after_added_to_wishlist']) ? $data['awwlm_after_added_to_wishlist'] : '',
                'add_to_wishlist_text' => isset($data['awwlm_add_to_wishlist_text']) ? $data['awwlm_add_to_wishlist_text'] : '',
                'added_to_wishlist_text' => isset($data['awwlm_added_to_wishlist_text']) ? $data['awwlm_added_to_wishlist_text'] : '',
                'browse_wishlist_text' => isset($data['awwlm_browse_wishlist_text']) ? $data['awwlm_browse_wishlist_text'] : '',
                'remove_wishlist_text' => isset($data['awwlm_remove_wishlist_text']) ? $data['awwlm_remove_wishlist_text'] : '',
                'exists_wishlist_text' => isset($data['awwlm_already_exists_wishlist_text']) ? $data['awwlm_already_exists_wishlist_text'] : '',
                'add_wishlist_style' => isset($data['awwlm_add_wishlist_style']) ? $data['awwlm_add_wishlist_style'] : '',
                'wishlist_button_bg' => isset($data['awwlm_wishlist_button_bg']) ? $data['awwlm_wishlist_button_bg'] : '',
                'wishlist_button_bg_Hex' => isset($data['awwlm_wishlist_button_bg_Hex']) ? $data['awwlm_wishlist_button_bg_Hex'] : '',
                'wishlist_button_txt' => isset($data['awwlm_wishlist_button_txt']) ? $data['awwlm_wishlist_button_txt'] : '',
                'wishlist_button_txt_Hex' => isset($data['awwlm_wishlist_button_txt_Hex']) ? $data['awwlm_wishlist_button_txt_Hex'] : '',
                'wishlist_button_br' => isset($data['awwlm_wishlist_button_br']) ? $data['awwlm_wishlist_button_br'] : '',
                'wishlist_button_br_Hex' => isset($data['awwlm_wishlist_button_br_Hex']) ? $data['awwlm_wishlist_button_br_Hex'] : '',
                'wishlist_button_bgH' => isset($data['awwlm_wishlist_button_bgH']) ? $data['awwlm_wishlist_button_bgH'] : '',
                'wishlist_button_bgH_Hex' => isset($data['awwlm_wishlist_button_bgH_Hex']) ? $data['awwlm_wishlist_button_bgH_Hex'] : '',
                'wishlist_button_txtH' => isset($data['awwlm_wishlist_button_txtH']) ? $data['awwlm_wishlist_button_txtH'] : '',
                'wishlist_button_txtH_Hex' => isset($data['awwlm_wishlist_button_txtH_Hex']) ? $data['awwlm_wishlist_button_txtH_Hex'] : '',
                'wishlist_button_brH' => isset($data['awwlm_wishlist_button_brH']) ? $data['awwlm_wishlist_button_brH'] : '',
                'wishlist_button_brH_Hex' => isset($data['awwlm_wishlist_button_brH_Hex']) ? $data['awwlm_wishlist_button_brH_Hex'] : '',
                'wishlist_button_radius' => isset($data['awwlm_wishlist_button_radius']) ? $data['awwlm_wishlist_button_radius'] : '',
                'add_icon' => isset($data['awwlm_add_icon']) ? $data['awwlm_add_icon'] : '',
                'added_icon' => isset($data['awwlm_added_icon']) ? $data['awwlm_added_icon'] : '',
                'add_icon_custom' => isset($data['awwlm_add_icon_custom']) ? $data['awwlm_add_icon_custom'] : '',
                'added_icon_custom' => isset($data['awwlm_added_icon_custom']) ? $data['awwlm_added_icon_custom'] : '',
                'custom_css' => isset($data['awwlm_custom_css']) ? $data['awwlm_custom_css'] : '',
              );

              if ( false === get_option('awwlm_button_settings') ){
                    add_option('awwlm_button_settings', $awwlm_button_settings, '', 'yes');
              }  else {
                    update_option('awwlm_button_settings', $awwlm_button_settings);
              }



          }

            $result['awwlm_button_settings'] = get_option('awwlm_button_settings') ? get_option('awwlm_button_settings') : '';

            return new WP_REST_Response($result, 200);
        }



        function awwlm_wishlist_page($data){

          if( ! $data['id'] ) {
              $data = $data->get_params();

              $awwlm_page_settings = array(
                'def_wishlist_name' => isset($data['awwlm_def_wishlist_name']) ? $data['awwlm_def_wishlist_name'] : 0,
                'def_add_cart_text' => isset($data['awwlm_def_add_cart_text']) ? $data['awwlm_def_add_cart_text'] : 0,
                'remove_added_cart' => isset($data['awwlm_remove_product_added_cart']) ? $data['awwlm_remove_product_added_cart'] : 0,
                'redirect_cart' => isset($data['awwlm_redirect_cart']) ? $data['awwlm_redirect_cart'] : 0,
                'wt_price' => isset($data['awwlm_wt_price']) ? $data['awwlm_wt_price'] : 0,
                'wt_stock' => isset($data['awwlm_wt_stock']) ? $data['awwlm_wt_stock'] : 0,
                'wt_date' => isset($data['awwlm_wt_date']) ? $data['awwlm_wt_date'] : 0,
                'wt_add_cart' => isset($data['awwlm_wt_add_cart']) ? $data['awwlm_wt_add_cart'] : 0,
                'wt_remove' => isset($data['awwlm_wt_remove']) ? $data['awwlm_wt_remove'] : 0,
                'share_wishlist' => isset($data['awwlm_share_wishlist']) ? $data['awwlm_share_wishlist'] : 0,
                'share_fb' => isset($data['awwlm_share_fb']) ? $data['awwlm_share_fb'] : '',
                'share_tw' => isset($data['awwlm_share_tw']) ? $data['awwlm_share_tw'] : '',
                'share_pin' => isset($data['awwlm_share_pin']) ? $data['awwlm_share_pin'] : '',
                'share_em' => isset($data['awwlm_share_em']) ? $data['awwlm_share_em'] : '',
                'share_wp' => isset($data['awwlm_share_wp']) ? $data['awwlm_share_wp'] : '',
                'share_url' => isset($data['awwlm_share_url']) ? $data['awwlm_share_url'] : '',
                'share_title' => isset($data['awwlm_share_title']) ? $data['awwlm_share_title'] : '',
                'share_text' => isset($data['awwlm_share_text']) ? $data['awwlm_share_text'] : '',
                'share_img_url' => isset($data['awwlm_share_img_url']) ? $data['awwlm_share_img_url'] : '',
              );
              $awwlm_wishlist_page = $data['awwlm_wishlist_page'] ? $data['awwlm_wishlist_page'] : '';


              if ( false === get_option('awwlm_page_settings') ){
                    add_option('awwlm_page_settings', $awwlm_page_settings, '', 'yes');
              }  else {
                    update_option('awwlm_page_settings', $awwlm_page_settings);
              }

              if ( false === get_option('awwlm_wishlist_page') ){
                    add_option('awwlm_wishlist_page', $awwlm_wishlist_page, '', 'yes');
              }  else {
                    update_option('awwlm_wishlist_page', $awwlm_wishlist_page);
              }

          }

            $result['awwlm_page_settings'] = get_option('awwlm_page_settings') ? get_option('awwlm_page_settings') : '';
            $result['awwlm_wishlist_page'] = get_option('awwlm_wishlist_page') ? get_option('awwlm_wishlist_page') : '';

            return new WP_REST_Response($result, 200);
        }


      function save_for_later_page($data){

          if( ! $data['id'] ) {
              $data = $data->get_params();

              $awwlm_save_for_later_settings = array(
                'enable' => isset($data['awwlm_save_for_later_enable']) ? $data['awwlm_save_for_later_enable'] : 0,
                'empty' => isset($data['awwlm_save_later_empty']) ? $data['awwlm_save_later_empty'] : 0,
                'hide_table' => isset($data['awwlm_save_later_hide']) ? $data['awwlm_save_later_hide'] : 0,
                'btn_text' => isset($data['awwlm_save_for_later_text']) ? $data['awwlm_save_for_later_text'] : '',
                'heading' => isset($data['awwlm_save_for_later_heading']) ? $data['awwlm_save_for_later_heading'] : '',
                'empty_msg' => isset($data['awwlm_save_later_empty_msg']) ? $data['awwlm_save_later_empty_msg'] : '',
              );

              if ( false === get_option('awwlm_save_for_later_settings') ){
                    add_option('awwlm_save_for_later_settings', $awwlm_save_for_later_settings, '', 'yes');
              }  else {
                    update_option('awwlm_save_for_later_settings', $awwlm_save_for_later_settings);
              }

          }

            $result['awwlm_save_for_later_settings'] = get_option('awwlm_save_for_later_settings') ? get_option('awwlm_save_for_later_settings') : '';


        return new WP_REST_Response($result, 200);
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
