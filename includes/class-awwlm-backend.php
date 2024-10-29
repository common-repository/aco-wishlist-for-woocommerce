<?php
if (!defined('ABSPATH'))
    exit;

class AWWLM_Backend
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

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_dir;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $script_suffix;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
    */
    public $assets_url;
    public $hook_suffix = array();

    /**
     * Constructor function.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function __construct( $file = '', $version = '1.0.0' )
    {
        $this->_version = $version;
        $this->_token = AWWLM_TOKEN;
        $this->file = $file;
        $this->dir = dirname( $this->file );
        $this->assets_dir = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
        $this->script_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		    if ($this->check_woocommerce_active()) {

        //reg activation hook
        register_activation_hook( $this->file, array( $this, 'install' ) );
        //reg admin menu
        add_action( 'admin_menu', array( $this, 'register_root_page' ) );
        //enqueue scripts & styles
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
        $plugin = plugin_basename($this->file);
        //add action links to link to link list display on the plugins page
        add_filter( "plugin_action_links_$plugin", array( $this, 'add_settings_link' ) );

        // deactivation form
        add_action('admin_footer', array($this, 'awwlm_deactivation_form'));

        }
    }

    /**
     *
     *
     * Ensures only one instance of AWWLM is loaded or can be loaded.
     *
     * @return Main AWWLM instance
     * @see WordPress_Plugin_Template()
     * @since 1.0.0
     * @static
    */
    public static function instance($file = '', $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

    /**
     * Creating admin pages
     */
    public function register_root_page()
    {

        $this->hook_suffix[] = add_menu_page( __('Wishlist For WooCommerce', 'aco-wishlist-for-woocommerce'), __('Wishlist', 'aco-wishlist-for-woocommerce'), 'manage_woocommerce', AWWLM_TOKEN.'_admin_ui', array($this, 'admin_ui'), esc_url($this->assets_url) . '/images/icon.png', 25);
        // $this->hook_suffix[] = add_submenu_page( AWWLM_TOKEN.'_admin_ui', __('Settings', 'aco-wishlist-for-woocommerce'), __('Settings', 'aco-wishlist-for-woocommerce'), 'manage_woocommerce', AWWLM_TOKEN.'_settings_ui', array($this, 'admin_ui_settings'));

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

    /**
     * Calling view function for admin page components
    */
    public function admin_ui()
    {
        AWWLM_Backend::view('admin-root', []);
    }

    /**
     * Adding new link(Configure) in plugin listing page section
    */
    public function add_settings_link($links)
    {
        $settings = '<a href="' . admin_url( 'admin.php?page='.AWWLM_TOKEN.'_admin_ui#/' ) . '">' . __( 'Settings', 'aco-wishlist-for-woocommerce' ) . '</a>';
        array_push( $links, $settings );
        return $links;
    }

    /**
     * Including View templates
    */
    static function view( $view, $data = array() )
    {
        //extract( $data );
        include( plugin_dir_path(__FILE__) . 'views/' . $view . '.php' );
    }

    /**
     * Load admin CSS.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function admin_enqueue_styles($hook = '')
    {

      $currentScreen = get_current_screen();
      $screenID = $currentScreen->id; //
      if (strpos($screenID, 'awwlm_') !== false) {

        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/backend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');

      }

    }

    /**
     * Load admin Javascript.
     * @access  public
     * @return  void
     * @since   1.0.0
    */
    public function admin_enqueue_scripts($hook = '')
    {
        if (!isset($this->hook_suffix) || empty($this->hook_suffix)) {
            return;
        }
        wp_enqueue_media();

        // Page List
		$WpPageList = get_pages(
			array('exclude' => array(wc_get_page_id( 'myaccount' )) )
		);
        $WpPageList = array_map(function ($v) {
            return ['id' => $v->ID, 'name' => $v->post_title];
        }, $WpPageList);


        $screen = get_current_screen();

        wp_enqueue_script('jquery');
        // deactivation form js
        if ( $screen->id == 'plugins' ) {
            wp_enqueue_script($this->_token . '-deactivation-message', esc_url($this->assets_url).'js/message.js', array());
        }

        if ( in_array( $screen->id, $this->hook_suffix ) ) {
            if ( !wp_script_is( 'wp-i18n', 'registered' ) ) {
                wp_register_script( 'wp-i18n', esc_url( $this->assets_url ) . 'js/i18n.min.js', array('jquery'), $this->_version, true );
            }
            wp_enqueue_script( $this->_token . '-backend', esc_url( $this->assets_url ) . 'js/backend.js', array('wp-i18n'), $this->_version, true );
            wp_localize_script( $this->_token . '-backend', 'awwlm_object', array(
                    'api_nonce' => wp_create_nonce('wp_rest'),
                    'root' => rest_url('awwlm/v1/'),
                    'text_domain' => 'aco-wishlist-for-woocommerce',
                    'assets_url' => $this->assets_url,
                    'pageList' => (array)$WpPageList,
                )
            );

           wp_set_script_translations($this->_token . '-backend', 'aco-wishlist-for-woocommerce' );
           
        }
    }




          /**
           * Deactivation form
          */
          public function awwlm_deactivation_form() {
            $currentScreen = get_current_screen();
            $screenID = $currentScreen->id;
            if ( $screenID == 'plugins' ) {
                $view = '<div id="awwlm-survey-form-wrap"><div id="awwlm-survey-form">
                <p>If you have a moment, please let us know why you are deactivating this plugin. All submissions are anonymous and we only use this feedback for improving our plugin.</p>
                <form method="POST">
                    <input name="Plugin" type="hidden" placeholder="Plugin" value="'.AWWLM_TOKEN.'" required>
                    <input name="Version" type="hidden" placeholder="Version" value="'.AWWLM_VERSION.'" required>
                    <input name="Date" type="hidden" placeholder="Date" value="'.date("m/d/Y").'" required>
                    <input name="Website" type="hidden" placeholder="Website" value="'.get_site_url().'" required>
                    <input name="Title" type="hidden" placeholder="Title" value="'.get_bloginfo( 'name' ).'" required>
                    <input type="radio" id="'.$this->_token.'-temporarily" name="Reason" value="I\'m only deactivating temporarily">
                <label for="'.$this->_token.'-temporarily">I\'m only deactivating temporarily</label><br>
                <input type="radio" id="'.$this->_token.'-notneeded" name="Reason" value="I no longer need the plugin">
                <label for="'.$this->_token.'-notneeded">I no longer need the plugin</label><br>
                <input type="radio" id="'.$this->_token.'-short" name="Reason" value="I only needed the plugin for a short period">
                <label for="'.$this->_token.'-short">I only needed the plugin for a short period</label><br>
                <input type="radio" id="'.$this->_token.'-better" name="Reason" value="I found a better plugin">
                <label for="'.$this->_token.'-better">I found a better plugin</label><br>
                <input type="radio" id="'.$this->_token.'-upgrade" name="Reason" value="Upgrading to PRO version">
                <label for="'.$this->_token.'-upgrade">Upgrading to PRO version</label><br>
                <input type="radio" id="'.$this->_token.'-requirement" name="Reason" value="Plugin doesn\'t meets my requirement">
                <label for="'.$this->_token.'-requirement">Plugin doesn\'t meets my requirement</label><br>
                <input type="radio" id="'.$this->_token.'-broke" name="Reason" value="Plugin broke my site">
                <label for="'.$this->_token.'-broke">Plugin broke my site</label><br>
                <input type="radio" id="'.$this->_token.'-stopped" name="Reason" value="Plugin suddenly stopped working">
                <label for="'.$this->_token.'-stopped">Plugin suddenly stopped working</label><br>
                <input type="radio" id="'.$this->_token.'-bug" name="Reason" value="I found a bug">
                <label for="'.$this->_token.'-bug">I found a bug</label><br>
                <input type="radio" id="'.$this->_token.'-other" name="Reason" value="Other">
                <label for="'.$this->_token.'-other">Other</label><br>
                    <p id="awwlm-error"></p>
                    <div class="awwlm-comments" style="display:none;">
                        <textarea type="text" name="Comments" placeholder="Please specify" rows="2"></textarea>
                        <p>For support queries <a href="https://support.acowebs.com/portal/en/newticket?departmentId=361181000000006907&layoutId=361181000000074011" target="_blank">Submit Ticket</a></p>
                    </div>
                    <button type="submit" class="awwlm_button" id="awwlm_deactivate">Submit & Deactivate</button>
                    <a href="#" class="awwlm_button" id="awwlm_cancel">Cancel</a>
                    <a href="#" class="awwlm_button" id="awwlm_skip">Skip & Deactivate</a>
                </form></div></div>';
                echo $view;
            } ?>
            <style>
                #awwlm-survey-form-wrap{ display: none;position: absolute;top: 0px;bottom: 0px;left: 0px;right: 0px;z-index: 10000;background: rgb(0 0 0 / 63%); } #awwlm-survey-form{ display:none;margin-top: 15px;position: fixed;text-align: left;width: 40%;max-width: 600px;z-index: 100;top: 50%;left: 50%;transform: translate(-50%, -50%);background: rgba(255,255,255,1);padding: 35px;border-radius: 6px;border: 2px solid #fff;font-size: 14px;line-height: 24px;outline: none;}#awwlm-survey-form p{font-size: 14px;line-height: 24px;padding-bottom:20px;margin: 0;} #awwlm-survey-form .awwlm_button { margin: 25px 5px 10px 0px; height: 42px;border-radius: 6px;background-color: #1eb5ff;border: none;padding: 0 36px;color: #fff;outline: none;cursor: pointer;font-size: 15px;font-weight: 600;letter-spacing: 0.1px;color: #ffffff;margin-left: 0 !important;position: relative;display: inline-block;text-decoration: none;line-height: 42px;} #awwlm-survey-form .awwlm_button#awwlm_deactivate{background: #fff;border: solid 1px rgba(88,115,149,0.5);color: #a3b2c5;}  #awwlm-survey-form .awwlm_button[disabled] { cursor: no-drop; } #awwlm-survey-form .awwlm_button#awwlm_skip{background: #fff;border: none;color: #a3b2c5;padding: 0px 15px;float:right;}#awwlm-survey-form .awwlm-comments{position: relative;}#awwlm-survey-form .awwlm-comments p{ position: absolute; top: -24px; right: 0px; font-size: 14px; padding: 0px; margin: 0px;} #awwlm-survey-form .awwlm-comments p a{text-decoration:none;}#awwlm-survey-form .awwlm-comments textarea{background: #fff;border: solid 1px rgba(88,115,149,0.5);width: 100%;line-height: 30px;resize:none;margin: 10px 0 0 0;} #awwlm-survey-form p#awwlm-error{margin-top: 10px;padding: 0px;font-size: 13px;color: #ea6464;}
            </style>
        <?php }




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

    /**
     * Installation. Runs on activation.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function install()
    {
        $this->_log_version_number();

        $wsettings = new AWWLM_Wishlist();
        $wsettings->awwlm_register_wishlist_page();
        $wsettings->awwlm_register_default_settings();

        flush_rewrite_rules();
    }

    /**
     * Log the plugin version number.
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    private function _log_version_number()
    {
        update_option($this->_token . '_version', $this->_version);
    }


}
