<?php
/*
Plugin Name: Shipit
Description: Shipit Calculator Shipping couriers
Version:     1.1.1
Author:      Shipit
Author URI:  https://Shipit.cl/
License: GPLv2 or later

Shipit-calculator is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Shipit-calculator is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Shipit-calculator. If not, see {License URI}.
*/


require_once dirname( __FILE__ ) . '/src/class.settings-api.php';
require_once dirname( __FILE__ ) . '/src/shipit-settings.php';
require_once dirname( __FILE__ ) . '/src/webhook.php';
require_once dirname( __FILE__ ) . '/src/auther.php';
require_once dirname( __FILE__ ) . '/src/bulk_actions.php';



new Shipit_Settings_Admin();

defined('ABSPATH') or die("Bye bye");



function shipit_script_load () {
    wp_enqueue_script('shipitjavascript', plugin_dir_url ( __FILE__ ). 'src/js/javascript.js', array('jquery')); 
    wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'src/css/style_shipit.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'wp_head', 'shipit_script_load', 0 );

function shipit_house_add_checkout_fields( $fields ) {
    $fields['billing_phone'] = array(
        'label'        => __( 'Telefono' ),
        'type'        => 'text',
        'class'        => array( 'form-row-wide' ),
        'placeholder'   => __('+569 --------'),
        'priority'     => 35,
        'required'     => true,
    );
    $fields['billing_houseno'] = array(
        'label'     => __('Numero de vivienda', 'woocommerce'),
        'type'        => 'text',
        'class'     => array('form-row-last'),
        'placeholder'   => __('Numero de vivienda'),
        'priority' => 51,
        'required'  => true,
        
    );
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'shipit_house_add_checkout_fields' );
function shipit_house_add_checkout_fields_shipping( $fields ) {
    
    $fields['shipping_houseno'] = array(
        'label'     => __('Numero de vivienda', 'woocommerce'),
        'type'        => 'text',
        'placeholder'   => _x('Numero de vivienda', 'placeholder', 'woocommerce'),
        'priority' => 51,
        'required'  => true,
        'class'     => array('form-row-last'),
    );
    return $fields;
}
add_filter('woocommerce_shipping_fields', 'shipit_house_add_checkout_fields_shipping' );
function activar_shipit()
{
    add_option('shipit_user','','yes');
    add_option('shipit_token','','yes');
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    global $wpdb;
    
    $wpdb->hide_errors();
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shipit (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        package varchar(1000) NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";
        
        dbDelta( $sql );
        
        $password = hash_password(get_bloginfo('name').'123');
        
        $userdata = array(
            'user_login'  =>  get_bloginfo('name').'_shipit',
            'nickname'    =>  'Shipit',
            'user_email'  => 'hola@shipit.cl',
            'user_url'    =>  get_site_url(),
            'user_pass'   =>  $password
        );
        
        $user_id = wp_insert_user( $userdata ) ;
        usersend($password);
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}user_shipit (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            temp varchar(1000) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
            
            dbDelta( $sql );
            $sql = "INSERT INTO {$wpdb->prefix}user_shipit
            (temp,created_at)
            VALUES(
                '".base64_encode(get_bloginfo('name')."_shipit" . ':' . $password)."',
                NOW()
            );";
            
            dbDelta( $sql );
            shipitupgradeSubscriberToShopManager($user_id);
        }
        register_activation_hook(__FILE__,'activar_shipit');
        
        function shipitupgradeSubscriberToShopManager($user_id)
        {
            $user = new WP_User($user_id);
            if (in_array('subscriber', $user->roles)) {
                $user->set_role('shop_manager');
            }
        }
        
        function hash_password( $password ) {
            global $wp_hasher;
            
            if ( empty( $wp_hasher ) ) {
                require_once( ABSPATH . WPINC . '/class-phpass.php' );
                
                $wp_hasher = new PasswordHash( 8, true );
            }
            
            return $wp_hasher->HashPassword( trim( $password ) );
            
        }
        
        add_action('woocommerce_thankyou', 'enroll_shipit', 10, 1);
        function enroll_shipit( $order_id) {
            if ( ! $order_id )
            return;
            
            
            if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {
                
                
                $order = wc_get_order( $order_id );
                
                if($order->is_paid())
                $paid = __('yes');
                else
                $paid = __('no');
                
                
                $commune_id = $order->get_billing_state();
                $request       = shipit_cURL_wrapper_request('v2', 'http://api.shipit.cl/v/orders', 'POST', $order_id, $commune_id);
                $response_code = wp_remote_retrieve_response_code( $request );
                
                if($response_code != 200){

                    $order = new WC_Order($order_id );
                    $order->add_order_note('no pudo ser enviado a Shipit');
                    
                }else {
                    $order = new WC_Order($order_id );
                    $order->add_order_note('Se a enviado a Shipit correctamente');
                }
                
                
                
                
                $order->update_meta_data( '_thankyou_action_done', true );
                $order->save();
            }
        }
        
        
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            
            function shipit_method() {
                if ( ! class_exists( 'Shipit_Shipping' ) ) {
                    class Shipit_Shipping extends WC_Shipping_Method {
                        
                        public function __construct() {
                            $this->id                 = 'shipit';
                            $this->method_title       = __( 'Shipit' );
                            $this->method_description = __( 'Shipit Cotizador' );
                            
                            $this->countries = array(
                                'CL'
                            );
                            
                            $this->init();
                            
                            $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                            $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Shipit' );
                        }
                        function init() {
                            $this->init_form_fields();
                            $this->init_settings();
                            
                            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                        }
                        
                        function init_form_fields() {
                            
                            $this->form_fields = array(
                                
                                'enabled' => array(
                                    'title'       => __( 'Enable', 'dc_raq' ),
                                    'type'        => 'checkbox',
                                    'description' => __( 'Enable this shipping method.', 'dc_raq' ),
                                    'default'     => 'yes'
                                ),
                                
                                'title' => array(
                                    'title'       => __( 'Title', 'dc_raq' ),
                                    'type'        => 'text',
                                    'description' => __( 'Title to be displayed on site', 'dc_raq' ),
                                    'default'     => __( 'Envio Shipit', 'dc_raq' )
                                ),
                                
                            );
                            
                        }
                        
                        public function calculate_shipping( $package = array() ) {
                            $commune_id = $package["destination"]['state'];
                            $feeder     = shipit_cURL_Wrapper('v4', 'http://api.shipit.cl/v/integrations/seller/woocommerce', 'GET');
                            if ($feeder === true) {
                            $ship       = shipit_cURL_Wrapper('v3', 'http://api.shipit.cl/v/prices', 'POST', $commune_id);
                            }
                            if($ship->state == 'error' || !$ship){
                                
                            }else{
                                $i          = 0;
                                foreach ($ship as $s) {
                                    $i++;
                                    
                                    $rate = array(
                                        'id'    => $this->id.'-'.$i,
                                        'label' => $s->courier->name,
                                        'cost'  => $s->price,
                                        'meta_data' => array('tiempo de entrega aprox: '.$s->days.'dias'),
                                    );
                                    
                                    $this->add_rate( $rate );
                                    
                                }
                            }
                        }
                        
                    }
                }
            }
            function add_shipit_method( $methods ) {
                $methods[] = 'Shipit_Shipping';
                return $methods;
            }
            add_filter( 'woocommerce_shipping_methods', 'add_shipit_method' );
            
            function Shipit_add_image_shipping( $label, $method ) {
                $shipping = $method->get_method_id();
                if($shipping == 'shipit'){
                    $label =  '<img style="display:inline;max-width: 75px;vertical-align: middle;" class="shipit_icon" id="img" src="'.plugin_dir_url(__FILE__) . 'src/images/'.$method->get_label().'.png"> <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">&#36;</span>'.number_format($method->get_cost()).'</span><br><span class="text-mute">'.$method->meta_data[0].'</span>';
                }
                
                return $label;
            }
            add_action( 'woocommerce_shipping_init', 'shipit_method' );
            add_filter( 'woocommerce_cart_shipping_method_full_label', 'Shipit_add_image_shipping', 10, 2 );
            
            
            function shipit_cURL_wrapper($version, $url, $method, $commune_id = null, $city = null) {    
                        
                $headers = array( 
                    'Content-Type' => 'application/json',
                    'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                    'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                    'Accept' => 'application/vnd.shipit.' . $version,
                );
                
                    if ('POST' == $method) {
                        $cart = WC()->cart->get_cart();
                        $count = WC()->cart->get_cart_contents_count();
                        $height = 0;
                        $width = 0;
                        $length = 0;
                        
                        
                        foreach( $cart as $cart_item ){
                            
                            $product = wc_get_product( $cart_item['product_id'] );
                            $qty = WC()->cart->get_cart_item_quantities();
                            $weight = $product->get_weight();
                            $height = $product->get_height();
                            $width = $product->get_width();
                            $length = $product->get_length();
                            
                            $array[] = [   
                                'width'         => (float)$width,
                                'height'        => (float)$height,
                                'length'        => (float)$length,
                                'weight'        => (float)$weight,
                                'quantity'      => $cart_item['quantity']
                            ];
                        }   
                        
                        $json_array = [
                            'packages' => 
                            $array,   
                            
                        ];
                        
                     
                        $pload = array(
                            'method' => 'POST',
                            'headers' => $headers,
                            'body' => json_encode($json_array),
                        );
                        
                        $data = wp_remote_post('https://boxify.shipit.cl/packs', $pload);
                        
                        
                        $body_request = json_decode($data['body']);
                        
                        $json_array = [
                            'package' => [
                                'length'        => $body_request->packing_measures->length,
                                'destiny'       => 'Domicilio',
                                'weight'        => $body_request->packing_measures->weight,
                                'width'         => $body_request->packing_measures->width,
                                'height'        => $body_request->packing_measures->height,
                                'to_commune_id' => $commune_id,
                            ],
                        ];
                        
                        $body = json_encode($json_array);
                       
                    }
                    $args = array(
                        'method' => $method,
                        'body' => $body,
                        'timeout' => '5',
                        'redirection' => '5',
                        'httpversion' => '1.0',
                        'blocking' => true,
                        'headers' => $headers,
                        'cookies' => array()
                    );
                    $response = wp_remote_request( $url, $args );
                    $response_code = wp_remote_retrieve_response_code( $response );
                
                    if($response_code === 200 || $response_code === 201){
                
                        if ('POST' == $method) {
                            return json_decode($response['body'])->prices;
                        } else {
                            return json_decode($response['body'])->woocommerce->show_shipit_checkout;
                        }
                    }else {
                        return 'Servicio no disponible, lamentamos las molestias';
                    }
                }
                
                function shipit_cURL_wrapper_request($version, $url, $method,  $order_id = null, $commune_id = null ) {
                        
                        if ('POST' == $method) {
                            $order = wc_get_order( $order_id );
                            
                            if($order->is_paid())
                            $paid = __('yes');
                            else
                            $paid = __('no');
                            
                            foreach ( $order->get_items() as $item ){
                                $product_id = $item['product_id'];
                                $product = wc_get_product( $product_id );
                                $h = $product->get_height();
                                $w = $product->get_width();
                                $l = $product->get_length();
                                $sku = ($product->get_sku() != '') ? $product->get_sku() : $product_id;
                                $variation = ($item['variation_id'] != '' && isset($item['variation_id'])) ? $sku.'-'.$item['variation_id'] : $sku;
                                $inventory[] = [   
                                    "sku_id" => $variation,
                                    "amount" => $item['qty'],
                                ];
                            }
                            foreach ( $order->get_items('shipping')as $shipping_id => $shipping_item_obj ){
                                $shipping_item_data = $shipping_item_obj->get_data()['method_id'];
                            }
                            $shipping = $shipping_item_data;
                            
                            $shipit = true;
                            
                            if($shipping == 'shipit'){
                                $shipit = false;
                            }
                            $body = [
                                "order" => [
                                    'mongo_order_seller' => 'woocommerce',
                                    'reference'           => '#'.$order_id,
                                    'full_name'           => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                                    'email'               => $order->get_billing_email(),
                                    'items_count'         => $order->get_item_count(),
                                    'cellphone'           => $order->get_billing_phone(),
                                    'is_payable'          => false,
                                    'packing'             => 'Sin empaque',
                                    'shipping_type'       => 'Normal',
                                    'destiny'             => 'Domicilio',
                                    'courier_for_client'  => $order->get_shipping_method(),
                                    'approx_size'         => 'Mediano ('.$h.'x'.$l.'x'.$w.'cm)',
                                    'sent' => $shipit,
                                    'address_attributes'  => [
                                        'commune_id'      => $order->get_shipping_state(),
                                        'street'          => $order->get_shipping_address_1(),
                                        'number'          => $order->shipping_houseno,
                                        'complement'      => $order->get_shipping_address_2(),
                                    ],
                                    "inventory_activity" => [
                                        "inventory_activity_orders_attributes"=>    
                                        $inventory,
                                    ],
                                    ]
                                ];
                                
                            }
                            $args = array(
                                'body' => json_encode($body),
                                'timeout' => '5',
                                'redirection' => '5',
                                'httpversion' => '1.0',
                                'blocking' => true,
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                    'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                    'Accept' => 'application/vnd.shipit.' . $version,
                                ),
                                'cookies' => array()
                            );
                            $response = wp_remote_post( $url, $args );
                            
                            
                            if ('POST' == $method) {
                                return $response;
                            } else {
                                return $arr[0]->id;
                            }
                    }
                    
                }
