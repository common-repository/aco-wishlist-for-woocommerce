<?php
/**
 * Integration with: WooCommerce Custom Product Addons
 *
 * @url https://wordpress.org/plugins/woo-custom-product-addons/
 *
 */

if (!defined('ABSPATH'))
    exit;


    class AWWLM_woocommerce_custom_product_addons
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

        add_filter( 'awwlm_wishlist_item_meta_data', array($this,  'awwlm_wishlist_item_meta_woocommerce_custom_product_addons'), 10, 2 );
        add_filter( 'awwlm_wishlist_item_price', array($this, 'awwlm_item_price_woocommerce_custom_product_addons'), 10, 3 );
        add_filter( 'awwlm_wishlist_item_add_to_cart',  array($this, 'awwlm_item_add_to_cart_woocommerce_custom_product_addons'), 10, 2 );

        add_filter( 'awwlm_wishlist_item_action_add_to_cart',  array($this, 'awwlm_item_action_add_to_cart_woocommerce_custom_product_addons'), 10, 2 );

        add_action( 'wp_footer', array($this,'awwlm_wcpa_footer_scripts') );
        add_action( 'woocommerce_after_add_to_cart_button', array($this,'awwlm_hidden_field_before_add_to_cart_button'), 5 );

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

        function awwlm_wcpa_format_item_data($item_data){

          $out_data = array();
          $item_data = unserialize($item_data);

          foreach( $item_data as $k => $v ){
            if( $v != '' ){
              // if( substr($k, 0, strlen('file-')) === 'file-' ){
              //   $k = chop($k,"_ajax");
              // }
              $out_data[$k] = array( 'key' => $k, 'display' => $v);
            }
          }
          return $out_data;
        }

        public function awwlm_wishlist_item_meta_woocommerce_custom_product_addons($product, $it_data){

          // $item_data = unserialize($item_data);
          $item_data = $this->awwlm_wcpa_format_item_data($it_data);

          $product_id = $product->get_id();
          if(  $product->is_type( 'variation' ) ){
            $product_id = $product->get_parent_id();
          }

          if ( defined( 'WCPA_POST_TYPE' ) && class_exists( 'WCPA_Front_End' ) && class_exists( 'WCPA_Form' ) ) {

            if ( !isset( $item_data['wcpa_field_key_checker'] ) ) {
              return;
            }

            $form     = new WCPA_Form();
      			$frontend = new WCPA_Front_End();
      			$data     = array();
      			$post_ids = $form->get_form_ids( $product_id );

      			if ( isset( $item_data['wcpa_field_key_checker'] ) ) {
      				unset( $item_data['wcpa_field_key_checker'] );
      			}

      			if ( isset( $item_data['quantity'] ) ) {
      				unset( $item_data['quantity'] );
      			}

      			if ( isset( $item_data['awwlm_product_price'] ) ) {
      				unset( $item_data['awwlm_product_price'] );
      			}

      			if ( isset( $item_data['add-to-cart'] ) ) {
      				unset( $item_data['add-to-cart'] );
      			}

      			if ( isset( $item_data['product_id'] ) ) {
      				unset( $item_data['product_id'] );
      			}

      			if ( isset( $item_data['variation_id'] ) ) {
      				unset( $item_data['variation_id'] );
      			}

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

            $data_new = array();
            if( $data ){
              foreach ( $data as $dt ) {
                $data_new[$dt->name] = $dt;
              }
            }

            $item_data_new = array();
            if ( $item_data ) {
              foreach ( $item_data as $ik => $iv ) {
                if ( isset( $data_new[ $ik ] ) ) {

                  if ( ! in_array( $data_new[ $ik ]->type, array( 'header', 'paragraph' ) ) ) {
                    $nm = $data_new[ $ik ]->name;

                     if ( isset( $item_data[ $nm ] ) ) {
                       if( $data_new[ $ik ]->type == 'image-group' ){

                         $indx = $item_data[ $nm ]['display'];
                         $wval = $data_new[ $ik ]->values;
                         if ( is_array( $indx ) ) {
                           $varray = array();
                           foreach( $indx as $ind ){
                             $varray[] = $wval[$ind]->label;
                           }
                           $value['display'] = $varray;
                         } else {
                           $value['display'] = $wval[$indx]->label;
                         }

                       } else if( $data_new[ $ik ]->type == 'placeselector' ){
                         $disp = $item_data[ $nm ]['display'];
                         if ( isset( $item_data[ $nm.'_route' ] ) ) {
                           $disp .= '<br>'.sanitize_text_field( wp_unslash( $item_data[ $nm.'_route' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_locality' ] ) ) {
                           $disp .= '<br>'.sanitize_text_field( wp_unslash( $item_data[ $nm.'_locality' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_administrative_area_level_1' ] ) ) {
                           $disp .= '<br>'.sanitize_text_field( wp_unslash( $item_data[ $nm.'_administrative_area_level_1' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_postal_code' ] ) ) {
                           $disp .= '<br>'.sanitize_text_field( wp_unslash( $item_data[ $nm.'_postal_code' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_country' ] ) ) {
                           $disp .= '<br>'.sanitize_text_field( wp_unslash( $item_data[ $nm.'_country' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_lat' ] ) ) {
                           $disp .= '<br>Lat: '.sanitize_text_field( wp_unslash( $item_data[ $nm.'_lat' ]['display'] ) );
                         }
                         if ( isset( $item_data[ $nm.'_lng' ] ) ) {
                           $disp .= '<br>Long: '.sanitize_text_field( wp_unslash( $item_data[ $nm.'_lng' ]['display'] ) );
                         }

                         $item_data[ $nm ]['display'] = $disp;
                         $value = $item_data[ $nm ];

                       } else if ( is_array( $item_data[ $nm ] ) ) {
                        $_values = $item_data[ $nm ];
                        array_walk( $_values, function ( &$a ) {
                          sanitize_text_field( $a );
                        } );
                        $value = $_values;
                      } else if ( $data_new[ $ik ] == 'textarea' ) {
                        $value = sanitize_textarea_field( wp_unslash( $item_data[ $nm ] ) );
                      } else {
                        $value = sanitize_text_field( wp_unslash( $item_data[ $nm ] ) );
                      }
                    }
                    $val = $value['display'];
                    if( is_array($val) ) {
                      $value_item = implode(', ', $val );
                    } else {
                      $value_item = $val;
                    }

                    $item_data_new[ $nm ]['key']     = ( isset( $data_new[ $ik ]->label ) ) ? $data_new[ $ik ]->label : '';
                    if( $data_new[ $ik ]->type == 'placeselector' ){
                      $item_data_new[ $nm ]['display'] = $value_item;
                    } else {
                    $item_data_new[ $nm ]['display'] = $frontend->cart_display( array(
                      'type'      => $data_new[ $ik ]->type,
                      'name'      => $data_new[ $ik ]->name,
                      'label'     => ( isset( $data_new[ $ik ]->label ) ) ? $data_new[ $ik ]->label : '',
                      'value'     => $value_item,
                      // 'value'     => $value['display'],
                      //'price'     => ( isset( $v->price ) ) ? $v->price : false,
                      'price'     => false,
                      //'form_data' => $form_data,
                    ), wc_get_product( $product_id ) );
                  }
                  }

                }
              }
      			}

            if($item_data_new){ ?>
              <dl class="variation">
              	<?php foreach ( $item_data_new as $datas ) : ?>
              		<?php if ( $datas['key'] ) { ?>
              			<dt class="variation-<?php echo sanitize_html_class( $datas['key'] ); ?>"><?php echo wp_kses_post( $datas['key'] ); ?>:</dt>
              		<?php } ?>
              		<?php if ( $datas['display'] ) { ?>
              			<dd class="variation-<?php echo sanitize_html_class( $datas['key'] ); ?>"><?php echo wp_kses_post( $datas['display'] ); ?></dd>
              		<?php } ?>
              	<?php endforeach; ?>
              </dl>
              <?php
            }

          }


        }

        function awwlm_item_price_woocommerce_custom_product_addons( $price, $product, $formData ){

          $formData = unserialize($formData);

          if ( isset( $formData['awwlm_product_price'] ) ) {
            $price = wc_price($formData['awwlm_product_price']);
          }

        	return $price;

        }


        function awwlm_item_add_to_cart_woocommerce_custom_product_addons( $product, $formData ){

          $product_id = $product->get_id();
          $item_data = $this->awwlm_wcpa_format_item_data($formData);

          if ( defined( 'WCPA_POST_TYPE' ) && class_exists( 'WCPA_Form' ) && class_exists( 'WCPA_Front_End' ) ) {

            if ( !isset( $item_data['wcpa_field_key_checker'] ) ) {
              return;
            }

            $form     = new WCPA_Form();
      			$frontend = new WCPA_Front_End();
      			$data     = array();
      			$post_ids = $form->get_form_ids( $product_id );
            $out = array();

      			if ( isset( $item_data['wcpa_field_key_checker'] ) ) {
      				unset( $item_data['wcpa_field_key_checker'] );
      			}

      			if ( isset( $item_data['quantity'] ) ) {
      				unset( $item_data['quantity'] );
      			}

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

        function awwlm_item_action_add_to_cart_woocommerce_custom_product_addons($product, $it_data ){

          $item_data = $this->awwlm_wcpa_format_item_data( $it_data );

            if ( isset( $item_data['wcpa_field_key_checker'] ) ) {
              return true;
            }
        }

        function awwlm_check_wcpa_form($product_id){
          $has_form = false;
          if ( defined( 'WCPA_POST_TYPE' ) ) {

            $form     = new WCPA_Form();
      			$post_ids = $form->get_form_ids( $product_id );

      			if ( is_array( $post_ids ) && count( $post_ids ) ) {
              $has_form = true;
            }

          }
          return $has_form;

        }

        function awwlm_wcpa_footer_scripts(){
          if( is_product() ){
            global $product;
            $has_form = $this->awwlm_check_wcpa_form($product->get_id());
            if( $has_form == true){
              ?>
              <script>
                jQuery(".wcpa_form_outer").on('wcpa.price_updated', function () {
                  var price = jQuery(this).data('wcpa').price.total;
                  jQuery('#awwlm_product_price').val(price);
                });
              </script>
              <?php
            }
          }
        }

        function awwlm_hidden_field_before_add_to_cart_button(){
          global $product;
          $has_form = $this->awwlm_check_wcpa_form($product->get_id());
          if( $has_form == true){
            echo '<input type="hidden" name="awwlm_product_price" id="awwlm_product_price" value="">';
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
