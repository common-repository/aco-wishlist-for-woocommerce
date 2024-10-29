<?php

if (!defined('ABSPATH'))
    exit;

    class AWWLM_save_for_later
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

          add_action( 'init', array($this, 'awwlm_register_shortcode') );
          add_action('woocommerce_after_cart_item_name', array($this, 'awwlm_after_cart_item_name'), 10, 2);

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $hide_table = ( isset($awwlm_sl['hide_table']) ) ? $awwlm_sl['hide_table'] : false;
          if( !$hide_table ){
            add_action( 'woocommerce_after_cart', array( $this, 'awwlm_shaow_saved_list' ) );
            add_action( 'woocommerce_cart_is_empty', array( $this, 'awwlm_shaow_saved_list' ), 20 );
          }

          add_action('wp_ajax_awwlm_action_add_to_savelist', array($this, 'awwlm_action_add_to_savelist'));
          add_action('wp_ajax_nopriv_awwlm_action_add_to_savelist', array($this, 'awwlm_action_add_to_savelist'));

          add_action('wp_ajax_awwlm_action_remove_savelist', array($this, 'awwlm_action_remove_savelist'));
          add_action('wp_ajax_nopriv_awwlm_action_remove_savelist', array($this, 'awwlm_action_remove_savelist'));

          add_action('wp_ajax_awwlm_action_addcart_savelist', array($this, 'awwlm_action_addcart_savelist'));
          add_action('wp_ajax_nopriv_awwlm_action_addcart_savelist', array($this, 'awwlm_action_addcart_savelist'));

          add_action('awwlm_savelist_item_add_to_cart', array($this, 'awwlm_savelist_add_to_cart_woocommerce_custom_product_addons'), 10, 2);


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


        function awwlm_after_cart_item_name( $cart_item, $cart_item_key ) {

          if( !is_user_logged_in() ){
            return '';
          }

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $sfl_enable = ( isset($awwlm_sl['enable']) ) ? $awwlm_sl['enable'] : false;

          if( $sfl_enable ){

            $sfl_btn_text = ( isset($awwlm_sl['btn_text']) && $awwlm_sl['btn_text'] != '' ) ? $awwlm_sl['btn_text'] : __('Save for later', 'aco-wishlist-for-woocommerce');

  					echo '<div class="awwlm-save-later-btn">
            <span class="awwlm-button button1 add" data-id="' . esc_attr( $cart_item['data']->get_id() ) . '" data-key="' . esc_attr( $cart_item_key ) . '" title="'.$sfl_btn_text.'" >
            ' . $sfl_btn_text . '
            </span>
            </div>';
          }

				}


        function awwlm_action_add_to_savelist(){

          if( !is_user_logged_in() ) {
            return;
          }
          $response = array('message' => '', 'success' => '' );
          $product_id = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : '';
          $cart_key = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : '';

          $cart_item_data = WC()->cart->get_cart_item( $cart_key );
          $wcpa_data = isset( $cart_item_data['wcpa_data'] ) ? $cart_item_data['wcpa_data'] : '';


          // error_log(print_r( ($cart_item_data) , true));
          // error_log(print_r( ($wcpa_data) , true));
          // error_log(print_r( serialize(WC()->cart->get_cart_item( $cart_key )) , true));
          // error_log(print_r( unserialize(serialize(WC()->cart->get_cart_item( $cart_key ))) , true));
          //
          // error_log(print_r( wc_get_formatted_cart_item_data( $cart_item_data ) , true));


          $savelist = array();

          if( $product_id ){

            $user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );

            if ( $user_savelist ){

              // if( !is_array($wcpa_data) && !in_array($product_id, array_column($user_savelist, 'product')) ){

              // if( is_array($wcpa_data) ){
              //   error_log(print_r( ($wcpa_data) , true));
              // }

              if( empty($wcpa_data) ){

               if(!in_array($product_id, array_column($user_savelist, 'product')) ){
                  $user_savelist[] = array('product' => $product_id, 'type' => 'normal');
                  update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
                } else {
                  $flg = 0;
                  $keys = array_keys(array_column($user_savelist, 'product'), $product_id);
                  if($keys){
                    foreach($keys as $k ){
                      if( (isset($user_savelist[$k]['type'])) && ($user_savelist[$k]['type'] == '' || $user_savelist[$k]['type'] == 'normal') ){
                        $flg = 1;
                      }
                    }
                  }
                  if( $flg == 0 ){
                     $user_savelist[] = array('product' => $product_id, 'type' => 'normal');
                     $user_savelist = array_values($user_savelist);
                     update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
                  }

                }
                /*
                else {
                  $flg = 0;
                  // $keyc = array_search($product_id, array_column($user_savelist, 'product'));
                  $keys = array_keys(array_column($user_savelist, 'product'), $product_id);
                    // error_log(print_r( $user_savelist , true));
                    error_log(print_r( $keys , true));
                  if($keys){
                    foreach($keys as $k ){
                      error_log(print_r( $k , true));
                      // error_log(print_r( @$user_savelist[$k]['cartData'] , true));
                      if( (isset($user_savelist[$k]['cartData'])) && !empty($user_savelist[$k]['cartData']) ){
                        $flg = 1;
                        continue;
                      }
                    }
                  }
                  error_log(print_r( $flg , true));
                  if( $flg == 0 ){
                    $user_savelist[] = array('product' => $product_id);

                    $user_savelist = array_values($user_savelist);
                    update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
                  }

                }
                */
              } else {

                // WCPA area

                $wcpaData= ($cart_item_data);
                // $wcpaData= serialize($cart_item_data);
                // $wcpaData= json_encode($cart_item_data);
                $user_savelist[] = array( 'product' => $product_id, 'cartData' => $wcpaData, 'type' => 'advanced' );
                update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
              }




            } else {
              // empty case
              if( empty($wcpa_data) ){
                $savelist[] = array('product' => $product_id, 'type' => 'normal');
              } else {
                $wcpaData= ($cart_item_data);
                // $wcpaData= serialize($cart_item_data);
                // $wcpaData= json_encode($cart_item_data);
                $savelist[] = array( 'product' => $product_id, 'cartData' => $wcpaData, 'type' => 'advanced' );
              }
              update_user_meta( get_current_user_id(), 'awwlm_savelist', $savelist );

            }

            $Sl_product = wc_get_product( $product_id );
            $Sl_name = $Sl_product->get_name();

			wc_add_notice( sprintf( __( '%s has been saved for later.', 'aco-wishlist-for-woocommerce' ), $Sl_name ), 'success' );

      			$user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
      			$count = count($user_savelist);

            $awwlm_sl = get_option('awwlm_save_for_later_settings');
            $heading = ( isset($awwlm_sl['heading']) ) ? $awwlm_sl['heading'] : '';
            $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;
            $empty_message = ( isset($awwlm_sl['empty_msg']) ) ? $awwlm_sl['empty_msg'] : '';

            $atts = array(
              'savelist_heading' => $heading,
              'count' => $count,
              'user_savelist' => $user_savelist,
              'empty_enable' => $empty_enable,
              'empty_message' => $empty_message,
            );
            $wsettings = new AWWLM_Wishlist();
            $response['save_list'] = json_encode($wsettings->awwlm_get_template('save-for-later.php', $atts ));

            $response['success'] = true;

            $this->awwlm_update_saved_count($product_id);

          if ( $cart_key ) {
						 WC()->cart->remove_cart_item( $cart_key );
					}

        }

          wp_send_json($response);
          die();

        }

        function awwlm_update_saved_count($postID){

          // users saved
          if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $added_user_key = 'awwlm_saved_list_users';
            if( get_post_meta($postID, $added_user_key, true) == '' ){
              $new = array( $user_id );
              add_post_meta($postID, $added_user_key, $new, true);
            } else {
              $users = get_post_meta($postID, $added_user_key, true);
                if (is_array($users) && array_key_exists($user_id,$users)){
                } else {
                  $users[] = $user_id;
                }
                update_post_meta($postID, $added_user_key, $users);
            }
          }

          // saved count
          $added_count_key = 'awwlm_saved_list_count';

          $count = get_post_meta($postID, $added_count_key, true);
          if ($count == '') {
              $count = 0;
              delete_post_meta($postID, $added_count_key);
              add_post_meta($postID, $added_count_key, '1', true);
          } else {
              $count++;
              update_post_meta($postID, $added_count_key, $count);
          }


        }


        function awwlm_shaow_saved_list(){

          echo do_shortcode( "[awwlm_saved_list]" ); 

        }

        function awwlm_register_shortcode() {
           add_shortcode('awwlm_saved_list', array($this, 'awwlm_saved_list_function'));
        }

        function awwlm_saved_list_function(){

          if( !is_user_logged_in() ) {
            return;
          }

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $sfl_enable = ( isset($awwlm_sl['enable']) ) ? $awwlm_sl['enable'] : false;
          if( !$sfl_enable ){
    			  return;
    		  }
          
          $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;
          $empty_message = ( isset($awwlm_sl['empty_msg']) ) ? $awwlm_sl['empty_msg'] : '';

          $user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
          if( empty($user_savelist) && !$empty_enable){
            return;
          }

		        $count = count($user_savelist);

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $heading = ( isset($awwlm_sl['heading']) ) ? $awwlm_sl['heading'] : '';
          $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;
          $empty_message = ( isset($awwlm_sl['empty_msg']) ) ? $awwlm_sl['empty_msg'] : '';

          $atts = array(
            'savelist_heading' => $heading,
            'count' => $count,
            'user_savelist' => $user_savelist,
            'empty_enable' => $empty_enable,
            'empty_message' => $empty_message,
          );


          $wsettings = new AWWLM_Wishlist();
          $return_string = '<div id="awwlm-savelater-wrap">';
          $return_string .= $wsettings->awwlm_get_template('save-for-later.php', $atts );
          $return_string .= '</div>';

          return $return_string;

        }


        function awwlm_action_remove_savelist(){

          if( !is_user_logged_in() ) {
            return;
          }
          $response = array('message' => '', 'success' => '' );
          $product_id = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : '';
          $list_id = isset( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : '';

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;

          $user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
          if( empty($user_savelist)){
            return;
          }

          if ( $user_savelist[$list_id]['product'] == $product_id ) {
          	unset( $user_savelist[ $list_id ] );
            update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
          }

          $Sl_product = wc_get_product( $product_id );
          $Sl_name = $Sl_product->get_name();

          wc_add_notice( sprintf( __( '%s has been removed from saved list', 'aco-wishlist-for-woocommerce' ), $Sl_name ), 'success' );

          $user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
          $count = count($user_savelist);

          
          $heading = ( isset($awwlm_sl['heading']) ) ? $awwlm_sl['heading'] : '';
          $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;
          $empty_message = ( isset($awwlm_sl['empty_msg']) ) ? $awwlm_sl['empty_msg'] : '';

          $atts = array(
            'savelist_heading' => $heading,
            'count' => $count,
            'user_savelist' => $user_savelist,
            'empty_enable' => $empty_enable,
            'empty_message' => $empty_message,
          );
          $wsettings = new AWWLM_Wishlist();
          $response['save_list'] = json_encode($wsettings->awwlm_get_template('save-for-later.php', $atts ));

          wp_send_json($response);

          die();

        }



      function awwlm_action_addcart_savelist(){

			if( !is_user_logged_in() ) {
				return;
			}
			$response = array('message' => '', 'success' => '' );
			$product_id = isset( $_REQUEST['product_id'] ) ? $_REQUEST['product_id'] : '';
			$list_id = isset( $_REQUEST['list_id'] ) ? $_REQUEST['list_id'] : '';

      
			$product = wc_get_product( $product_id );
			if ( $product->is_purchasable() ) {
				WC()->cart->add_to_cart( $product_id );

				// $product_cart_id = WC()->cart->generate_cart_id( $product_id );
				// if( ! WC()->cart->find_product_in_cart( $product_cart_id ) ){
				// 	WC()->cart->add_to_cart( $product_id ); 
				// } else {
					
				// 	$quantities = WC()->cart->get_cart_item_quantities(); 
				// 	if( isset($quantities[$product_id]) && $quantities[$product_id] > 0 ) {
				// 		$quantity = $quantities[$product_id] ;
				// 	}
				// 	$quantity++;  
				// 	WC()->cart->set_quantity($product_cart_id, $quantity, true);
				// 	// WC()->cart->set_session();
        //   // WC()->cart->add_to_cart( $product_id, $quantity ); 

        
				// }

				
			}
      
      
          $user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
          if( empty($user_savelist)){
            return;
          }

          if ( $user_savelist[$list_id]['product'] == $product_id ) {
          	unset( $user_savelist[ $list_id ] );
            update_user_meta( get_current_user_id(), 'awwlm_savelist', $user_savelist );
          }

          $Sl_product = wc_get_product( $product_id );
          $Sl_name = $Sl_product->get_name();

		  wc_add_notice( sprintf( __( '%s has been moved into cart successfully', 'aco-wishlist-for-woocommerce' ), $Sl_name ), 'success' );

			$user_savelist = get_user_meta( get_current_user_id(), 'awwlm_savelist', true );
			$count = count($user_savelist);

          $awwlm_sl = get_option('awwlm_save_for_later_settings');
          $heading = ( isset($awwlm_sl['heading']) ) ? $awwlm_sl['heading'] : '';
          $empty_enable = ( isset($awwlm_sl['empty']) ) ? $awwlm_sl['empty'] : false;
          $empty_message = ( isset($awwlm_sl['empty_msg']) ) ? $awwlm_sl['empty_msg'] : '';

          $atts = array(
            'savelist_heading' => $heading,
            'count' => $count,
            'user_savelist' => $user_savelist,
            'empty_enable' => $empty_enable,
            'empty_message' => $empty_message,
          );
          $wsettings = new AWWLM_Wishlist();
          $response['save_list'] = json_encode($wsettings->awwlm_get_template('save-for-later.php', $atts ));

          wp_send_json($response);
          die();

        }

        function awwlm_cart_wcpa_format_item_data($cartData){

          $out_data = array();
          $wcpa_data = isset($cartData['wcpa_data']) ? $cartData['wcpa_data'] : '' ;
          if( $wcpa_data ){
            foreach ($wcpa_data as $key => $value) {
              if( isset($value['value']) && $value['value'] != '' ){
                $out_data[$value['name']] = array( 'key' => $value['name'], 'display' =>  $value['value']);
              }
            }
          }
          return $out_data;

        }

        function awwlm_savelist_add_to_cart_woocommerce_custom_product_addons($product, $formData){

          $product_id = $product->get_id();
          $item_data = $this->awwlm_cart_wcpa_format_item_data($formData);

          if ( defined( 'WCPA_POST_TYPE' ) && class_exists( 'WCPA_Form' ) && class_exists( 'WCPA_Front_End' ) ) {

            $form     = new WCPA_Form();
      			// $frontend = new WCPA_Front_End();
      			$data     = array();
      			$post_ids = $form->get_form_ids( $product_id );
            $out = array();


      			if ( wcpa_get_option( 'form_loading_order_by_date' ) === true ) {
      				if ( is_array( $post_ids ) && count( $post_ids ) ) {
      					$post_ids = get_posts( array(
                  'post_type'      => WCPA_POST_TYPE,
      						'include'        => $post_ids,
      						'fields'         => 'ids',
      						'posts_per_page' => -1,
      					) );
      				}
      			}
            foreach ( $post_ids as $id ) {
      				if ( get_post_status( $id ) == 'publish' ) {
      					$json_string  = get_post_meta( $id, WCPA_FORM_META_KEY, true );
      					$json_encoded = json_decode( $json_string );
      					if ( $json_encoded && is_array( $json_encoded ) ) {
      						$data = array_merge( $data, $json_encoded );
      					}
      				}
      			}
            foreach ( $data as $v ) {

              if ( ! in_array( $v->type, array( 'header', 'paragraph' ) ) ) {
                if ( isset( $item_data[ $v->name ] ) ) {

                  if ( $v->type == 'placeselector' ) {
                    $out[$item_data[ $v->name ]['key']] = urlencode($item_data[ $v->name ]['display']);
                    if ( isset( $item_data[ $v->name.'_street_number' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_street_number'] = urlencode($item_data[ $v->name.'_street_number' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_route' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_route'] = urlencode($item_data[ $v->name.'_route' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_locality' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_locality'] = urlencode($item_data[ $v->name.'_locality' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_administrative_area_level_1' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_administrative_area_level_1'] = urlencode($item_data[ $v->name.'_administrative_area_level_1' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_postal_code' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_postal_code'] = urlencode($item_data[ $v->name.'_postal_code' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_country' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_country'] = urlencode($item_data[ $v->name.'_country' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_lat' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_lat'] = urlencode($item_data[ $v->name.'_lat' ]['display']);
                    }
                    if ( isset( $item_data[ $v->name.'_lng' ] ) ) {
                      $out[$item_data[ $v->name ]['key'].'_lng'] = urlencode($item_data[ $v->name.'_lng' ]['display']);
                    }

                  } else if ( $v->type == 'color' ) {
                    $out[$item_data[ $v->name ]['key']] = urlencode($item_data[ $v->name ]['display']);
                  } else if ( is_array( $item_data[ $v->name ]['display'] ) ) {
                    foreach( $item_data[ $v->name ]['display'] as $key => $val){
                      $n = $item_data[ $v->name ]['key'];
                      $out[$n.'['.$key.']'] = urlencode($val);
                    }
                  } else {
                    $out[$item_data[ $v->name ]['key']] = urlencode($item_data[ $v->name ]['display']);
                  }


                }
              }
            }

            return $out;
          }

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
