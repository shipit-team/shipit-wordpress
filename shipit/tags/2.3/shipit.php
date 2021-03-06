<?php
/*
Plugin Name: Shipit
Description: Shipit Calculator Shipping couriers
Version:     2.3.0
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
require_once dirname( __FILE__ ) . '/src/shipit_service/sku.php';
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
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'shipit_house_add_checkout_fields' );

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
            // here continue to sync fulfillment skus
            // shipitSyncSkus('hola@shipit.cl', $password);
            shipitupgradeSubscriberToShopManager($user_id);
        }
        register_activation_hook(__FILE__,'activar_shipit');

        function shipitSyncSkus($email, $passowrd) {
            $config = array(
                'headers' => array( 
                    'Content-Type' => 'application/json',
                    'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                    'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                    'Accept' => 'application/vnd.shipit.v4',
                )
            );

            $skus = wp_remote_get('https://api.shipit.cl/v/fulfillment/skus', $config);
            $skus = json_decode($skus['body'], true);
            // here sync SKUS with store products
            $woocommerce_products = wc_get_products();
        }

        function shipitupgradeSubscriberToShopManager($user_id) {
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
                
                $commune_id = (int) filter_var($order->get_billing_state(), FILTER_SANITIZE_NUMBER_INT);
                if ($order->status != 'cancelled' && $order->status != 'failed' && $order->status != 'on-hold' && $order->status != 'refunded' && $order->status != 'pending' && $order->status != 'pending payment') {
                    $request       = shipit_cURL_wrapper_request('v2', 'https://api.shipit.cl/v/orders', 'POST', $order_id, $commune_id);
                    $response_code = wp_remote_retrieve_response_code( $request );
                    
                    if($response_code != 200){
                        
                        $order = new WC_Order($order_id );
                        $order->add_order_note('no pudo ser enviado a Shipit'); 
                        
                    }else {
                        $order = new WC_Order($order_id );
                        $order->add_order_note('Se ha enviado a Shipit correctamente'); 
                    }
                } else {
                    $order = new WC_Order($order_id );
                    $order->add_order_note('No pudo ser enviado a Shipit porque el pedido todavía no está confirmado o está fallido'); 
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
                            $Shipit_Shipping = $this;
                            
                        }
                        function init() {
                            $this->init_form_fields();
                            $this->init_settings();
                            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                        }
                        
                        function init_form_fields() {
                            
                            $this->form_fields = array(
                                'enabled' => array(
                                    'title'       => __( 'Activar', 'dc_raq' ),
                                    'type'        => 'checkbox',
                                    'description' => __( 'Activar el metodo de envio Shipit', 'dc_raq' ),
                                    'default'     => 'yes',
                                ),
                                'time_despach' => array(
                                    'title'       => __( 'Tiempo de entrega', 'dc_raq' ),
                                    'type'        => 'checkbox',
                                    'description' => __( 'Mostrat el tiempo de envio de Shipit', 'dc_raq' ),
                                    'default'     => 'yes'
                                ),
                                'type_packing' => array(
                                    'title' => 'Tipo empaque',
                                    'description' => 'Elige el tipo de empaque que tendra tu envio',
                                    'type' => 'select',
                                    'class' => 'wc-enhanced-select',
                                    'options' => array(
                                        'Sin empaque' => 'Sin empaque',
                                        'Caja de cartón' => 'Caja de cartón',
                                        'Film plástico' => 'Film plástico',
                                        'Caja + Burbuja' => 'Caja + Burbuja',
                                        'Papel kraft' => 'Papel kraft',
                                        'Bolsa Courier + Burbuja' => 'Bolsa Courier + Burbuja',
                                        'Bolsa Courier' => 'Bolsa Courier',
                                        )
                                    ),
                                    'packing_set' => array(
                                        'title' => 'Establecer dimensiones del producto',
                                        'description' => 'Configure una dimensión predefinida para sus productos al momento de la cotización. Deje en blanco o &quot;0&quot; para omitir.',
                                        'type' => 'select',
                                        'class' => 'wc-enhanced-select',
                                        'default'     => 'Sí, cuando falten las dimensiones del producto o no estén configuradas',
                                        'options' => array(
                                            '2' => 'Sí, cuando falten las dimensiones del producto o no estén configuradas',
                                            '1' => 'Sí, utilizar siempre las dimensiones especificadas',
                                            '3' => 'Sí, cuando las dimensiones del producto sean menores que las especificadas',
                                            '4' => 'Sí, cuando las dimensiones del producto sean mayores que las especificadas',
                                            )
                                        ),
                                        'width' => array(
                                            'title' => __( 'Ancho', 'woocommerce' ),
                                            'type' => 'number',
                                            'description' => __( 'CM.', 'woocommerce' ),
                                            'css'      => 'max-width:100px;',
                                            'default' => __( '10', 'woocommerce' )
                                        ),
                                        'height' => array(
                                            'title' => __( 'Alto', 'woocommerce' ),
                                            'type' => 'number',
                                            'description' => __( 'CM.', 'woocommerce' ),
                                            'css'      => 'max-width:100px;',
                                            'default' => __( '10', 'woocommerce' )
                                        ),
                                        'length' => array(
                                            'title' => __( 'Largo', 'woocommerce' ),
                                            'type' => 'number',
                                            'description' => __( 'CM.', 'woocommerce' ),
                                            'css'      => 'max-width:100px;',
                                            'default' => __( '10', 'woocommerce' ),
                                        ),
                                        'weight_set' => array(
                                            'title' => 'Establecer peso del producto',
                                            'description' => 'Configure un peso predefinido para sus productos al momento de la cotización.',
                                            'type' => 'select',
                                            'class' => 'wc-enhanced-select',
                                            'default'     => 'Sí, cuando el peso del producto falte o no esté configurado',
                                            'options' => array(
                                                '2' => 'Sí, cuando el peso del producto falte o no esté configurado',
                                                '1' => 'Sí, utilizar siempre el peso especificado',
                                                '3' => 'Sí, cuando el peso del producto sea menor que el especificado',
                                                '4' => 'Sí, cuando el peso del producto sea mayor que el especificado',
                                                )
                                            ),
                                            'weight' => array(
                                                'title' => __( 'Peso', 'woocommerce' ),
                                                'type' => 'number',
                                                'description' => __( 'KG.', 'woocommerce' ),
                                                'css'      => 'max-width:100px;',
                                                'default' => __( '1', 'woocommerce' ),
                                            ),
                                            'calculate_shiping' => array(
                                                'title' => 'Configuración de envios',
                                                'description' => 'Configure un valor de envio.',
                                                'type' => 'select',
                                                'class' => 'wc-enhanced-select',
                                                'options' => array(
                                                    '0' => 'Mostrar couriers disponibles',
                                                    '1' => 'mostrar el mejor valor por defecto',
                                                    )
                                                ),
                                                'active-setup-price' => array(
                                                    'title'       => __( 'Activar precio definido', 'dc_raq' ),
                                                    'type'        => 'checkbox',
                                                    'description' => __( 'Activar precio preconfigurado', 'dc_raq' ),
                                                    'default'     => 'yes'
                                                ),
                                                'communes'    => array(
                                                    'title'             => __( 'Comunas especificas', 'woocommerce' ),
                                                    'type'              => 'multiselect',
                                                    'description' => 'Configure comunas para valor detallado.',
                                                    'class'             => 'wc-enhanced-select',
                                                    'options'           => WC()->countries->get_states('CL'),
                                                    'custom_attributes' => array(
                                                        'data-placeholder' => __( 'Seleccione comunas', 'woocommerce' ),
                                                    ),
                                                ),
                                                'price-setup' => array(
                                                    'title' => __( 'Subvencionar precio de envios ', 'woocommerce' ),
                                                    'type' => 'number',
                                                    'description' => __( 'Configure su valor de las comunas por %. "100% = Gratis"', 'woocommerce' ),
                                                    'css'      => 'max-width:200px;',
                                                ),
                                                'free_communes'    => array(
                                                    'title'             => __( 'Comunas especificas', 'woocommerce' ),
                                                    'type'              => 'multiselect',
                                                    'description' => 'Configure comunas con despacho gratis.',
                                                    'class'             => 'wc-enhanced-select',
                                                    'options'           => WC()->countries->get_states('CL'),
                                                    'custom_attributes' => array(
                                                        'data-placeholder' => __( 'Seleccione comunas', 'woocommerce' ),
                                                    ),
                                                ),
                                                'price' => array(
                                                    'title' => __( 'Envios gratis a partir:', 'woocommerce' ),
                                                    'type' => 'number',
                                                    'description' => __( 'Configure el valor de minimo de orden para despachos.', 'woocommerce' ),
                                                    'css'      => 'max-width:200px;',
                                                ),
                                                'free_communes_for_price'    => array(
                                                    'title'             => __( 'Comunas con despacho gratis segun valor:', 'woocommerce' ),
                                                    'type'              => 'multiselect',
                                                    'description' => 'Configure comunas con despacho gratis si el valor del producto es mayor.',
                                                    'class'             => 'wc-enhanced-select',
                                                    'options'           => WC()->countries->get_states('CL'),
                                                    'custom_attributes' => array(
                                                        'data-placeholder' => __( 'Seleccione comunas', 'woocommerce' ),
                                                    ),
                                                ),
                                            );
                                            
                                        }
                                        
                                        public function calculate_shipping( $package = array() ) {
                                            $commune_id = (int) filter_var($package["destination"]['state'], FILTER_SANITIZE_NUMBER_INT);
                                            $feeder     = shipit_cURL_Wrapper('v4', 'https://api.shipit.cl/v/integrations/seller/woocommerce', 'GET');
                                            if ($feeder === true && $commune_id != null) {
                                                $shipit_response      = shipit_cURL_Wrapper('v3', 'https://api.shipit.cl/v/prices', 'POST', $commune_id);
                                            }
                                            
                                            $ship = $shipit_response['JSON'];
                                            
                                            if(is_object($ship) && $ship->state == 'error' || !$ship){
                                                
                                            }else{
                                                $i          = 0; 
                                                global $shows;
                                                $shows = new Shipit_Shipping();
                                                
                                                $upper =((int)$shipit_response['total'] > (int)$shows->settings['price']) ?  true : false;
                                                
                                                $setup_calculate = $shows->settings['calculate_shiping'];
                                                $info = ($shows->settings['communes'] != '')  ? in_array('CL'.strval( $commune_id ) , $shows->settings['communes'], TRUE) : 0;
                                                $free = ($shows->settings['free_communes'] != '') ? in_array('CL'.strval( $commune_id ) , $shows->settings['free_communes'], TRUE) : 0;
                                                $free = ($shows->settings['free_communes_for_price'] != '' && $upper == true) ? in_array('CL'.strval( $commune_id ) , $shows->settings['free_communes_for_price'], TRUE) : 0;
                                                
                                                if ($setup_calculate == 0 && $free != true) {
                                                    if (is_array($ship) || is_object($ship))
                                                    {
                                                        foreach ($ship as $s) {
                                                            $i++;
                                                            
                                                            $rate = array(
                                                                'id'    => $this->id.'-'.$i,
                                                                'label' => $s->courier->name,
                                                                'cost'  => ($shows->settings['active-setup-price'] == 'yes') ?  ($info == true) ?  $s->price  - (($s->price * $shows->settings['price-setup']) /100) : $s->price : $s->price,
                                                                'meta_data' => ($shows->settings['time_despach'] == 'yes') ? array('tiempo de entrega aprox: '.$s->days.'dias') : array(),
                                                            );
                                                            
                                                            $this->add_rate( $rate );
                                                            
                                                        }
                                                    }
                                                }else {
                                                    $first = true;
                                                    if (is_array($ship) || is_object($ship))
                                                    {
                                                        foreach ($ship as $s) {
                                                            $i++;
                                                            if ( $first ) {
                                                                $rate = array(
                                                                    'id'    => $this->id.'-'.$i,
                                                                    'label' => 'shipit',
                                                                    'cost'  => ($shows->settings['active-setup-price'] == 'yes') ?  ($info == true) ?  ($free == true) ? 0 : $s->price  - (($s->price * $shows->settings['price-setup']) /100) : ($free == true) ? 0 : $s->price : $s->price,
                                                                    'meta_data' => ($shows->settings['time_despach'] == 'yes') ? array('tiempo de entrega aprox: '.$s->days.'dias') : array(),
                                                                );
                                                                
                                                                $this->add_rate( $rate );
                                                                $first = false;
                                                            }    
                                                            
                                                        }
                                                    }
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
                            
                            function Shipit_add_image_shipping( $label, $methods ) {
                                $shipping = $methods->get_method_id();
                                if($shipping == 'shipit'){
                                    $label = (number_format($methods->get_cost()) == 0 ) ? '<img style="display:inline;max-width: 75px;vertical-align: middle;" class="shipit_icon" id="img" src="'.plugin_dir_url(__FILE__) . 'src/images/'.$methods->get_label().'.png"> <span class="woocommerce-Price-amount amount"> GRATIS</span><br><span class="text-mute">'.$methods->meta_data[0].'</span>' : '<img style="display:inline;max-width: 75px;vertical-align: middle;" class="shipit_icon" id="img" src="'.plugin_dir_url(__FILE__) . 'src/images/'.$methods->get_label().'.png"> <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">&#36;</span>'.number_format($methods->get_cost()).'</span><br><span class="text-mute">'.$methods->meta_data[0].'</span>';
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
                                $body = '';
                                if ('POST' == $method) {
                                    $cart = WC()->cart->get_cart();
                                    $count = WC()->cart->get_cart_contents_count();
                                    $height = 0;
                                    $width = 0;
                                    $length = 0;
                                    
                                    $forms = new Shipit_Shipping();
                                    
                                    
                                    $setup_type_packing = $forms->settings['type_packing'];
                                    $setup_packing_set = $forms->settings['packing_set'];
                                    $setup_weight_set = $forms->settings['weight_set'];
                                    $setup_width = $forms->settings['width'];
                                    $setup_height = $forms->settings['height'];
                                    $setup_length = $forms->settings['length'];
                                    $setup_weight =$forms->settings['weight'];
                                    $weight_unit = get_option('woocommerce_weight_unit');
                                    $dimension_unit = get_option('woocommerce_dimension_unit');

                                    $width_plus = 0;
                                    $height_plus = 0;
                                    $length_plus = 0;
                                    $weight_plus = 0;

                                    switch ($weight_unit) {
                                        case 'oz':
                                            $divider_weight = 35.274;
                                        break;
                                        case 'lbs':
                                            $divider_weight = 2.2046;
                                        break;
                                        case 'kg':
                                            $divider_weight = 1;
                                        break;
                                    }
                                    switch ($dimension_unit) {
                                        case 'mm':
                                            $divider_dimension = 10.000;
                                        break;
                                        case 'yd':
                                            $divider_dimension = 0.010936;
                                        break;
                                        case 'm':
                                            $divider_dimension = 0.010000;
                                        break;
                                        case 'in':
                                            $divider_dimension = 0.39370;
                                        break;
                                        case 'cm':
                                            $divider_dimension = 1;
                                        break;
                                    }
                                    
                                    foreach( $cart as $cart_item ){
                                        
                                        if ($cart_item['variation_id'] != '' && isset($cart_item['variation_id'])) { 
                                            $product = wc_get_product($cart_item['variation_id']);
                                            $product_id = $cart_item['variation_id'];
                                        } else {
                                            $product = wc_get_product($cart_item['product_id']);
                                            $product_id = $cart_item['product_id'];
                                        }
                                        $product = wc_get_product( $product_id );
                                        $qty = WC()->cart->get_cart_item_quantities();
                                        if ($setup_packing_set == 0) {
                                            
                                            $height = $product->get_height();
                                            $width = $product->get_width(); 
                                            $length = $product->get_length();
                                        }elseif ($setup_packing_set == 1) {
                                            
                                            $height = $setup_height;
                                            $width = $setup_width;
                                            $length = $setup_length;
                                        }elseif ($setup_packing_set == 2) {
                                            
                                            
                                            $height = ($product->get_height() != '' && $product->get_height() != 0) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = ($product->get_width() != '' && $product->get_width() != 0) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = ($product->get_length() != '' && $product->get_length() != 0) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                            
                                        }elseif ($setup_packing_set == 3) {
                                            
                                            $height = (($product->get_height()/$divider_dimension) > $setup_height) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = (($product->get_width()/$divider_dimension) > $setup_width) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = (($product->get_length()/$divider_dimension) > $setup_length) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                            
                                        }elseif ($setup_packing_set == 4) {
                                            
                                            $height = (($product->get_height()/$divider_dimension) < $setup_height) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = (($product->get_width()/$divider_dimension) < $setup_width) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = (($product->get_length()/$divider_dimension) < $setup_length) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                        }
                                        if ($setup_weight_set == 0) {
                                            $weight = $product->get_weight();
                                            
                                        }elseif ($setup_weight_set == 1) {
                                            $weight = $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 2) {
                                            $weight = ($product->get_weight() != '' && $product->get_weight() != 0) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 3) {
                                            $weight = (($product->get_weight()/$divider_weight) > $setup_weight) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 4) {
                                            $weight = (($product->get_weight()/$divider_weight) < $setup_weight) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }
                                        
                                        $array[] = [   
                                            'width'         => (float)$width,
                                            'height'        => (float)$height,
                                            'length'        => (float)$length,
                                            'weight'        => (float)$weight,
                                            'quantity'      => (int)$cart_item['quantity']
                                        ];
                                        
                                        $width_plus += (float)$width * (int)$cart_item['quantity'];
                                        $height_plus = ((float)$height > $height_plus) ? (float)$height : $height_plus;
                                        $length_plus = ((float)$length > $length_plus) ? (float)$length : $length_plus;
                                        $weight_plus += (float)$weight * (int)$cart_item['quantity'];
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
                                    
                                    if((int)$cart_item['quantity'] > 1){
                                        $data = wp_remote_post('https://boxify.shipit.cl/packs', $pload);
                                        $response_code = wp_remote_retrieve_response_code( $data );
                                        $body_request = ($response_code === 200) ?  json_decode($data['body']) :  null;
                                    } else {
                                        $body_request = null;
                                    }
                                    
                                    $json_array = [
                                        'package' => [
                                            'length'        => ($body_request != null) ? $body_request->packing_measures->length : $length_plus,
                                            'destiny'       => 'Domicilio',
                                            'weight'        => ($body_request != null) ? $body_request->packing_measures->weight : $weight_plus,
                                            'width'         => ($body_request != null) ? $body_request->packing_measures->width : $width_plus,
                                            'height'        => ($body_request != null) ? $body_request->packing_measures->height : $height_plus,
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
                                        return array('total'=> WC()->cart->get_subtotal(), 'JSON' => json_decode($response['body'])->prices);
                                    } elseif ($url == 'https://api.shipit.cl/v/integrations/seller/woocommerce') {
                                        return json_decode($response['body'])->woocommerce->show_shipit_checkout;
                                    } else {
                                        return json_decode($response['body']);
                                    }
                                }else {
                                    return 'Servicio no disponible, lamentamos las molestias';
                                }
                            }
                            
                            function shipit_cURL_wrapper_request($version, $url, $method,  $order_id = null, $commune_id = null ) {
                                
                                if ('POST' == $method) {
                                    
                                    $headers = array( 
                                        'Content-Type' => 'application/json',
                                        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                        'Accept' => 'application/vnd.shipit.' . $version,
                                    );
                                    $headers_administrative = array( 
                                        'Content-Type' => 'application/json',
                                        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                        'Accept' => 'application/vnd.shipit.v4',
                                    );
                                    $headers_config = array( 
                                        'Content-Type' => 'application/json',
                                        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                        'Accept' => 'application/vnd.orders.v1',
                                    );

                                    $config = array(
                                        'headers' => $headers_config,
                                    );

                                    $administrative = array(
                                        'headers' => $headers_administrative,
                                    );
                                    $data = wp_remote_get('https://api.shipit.cl/v/setup/administrative', $administrative);
                                    $skus_request = wp_remote_get('https://api.shipit.cl/v/fulfillment/skus', $administrative);
                                    $skus_array = (array) json_decode($skus_request['body'], true)['skus'];
                                    $admin_shipit = json_decode($data['body']);
                                    $services = $admin_shipit->service->name;
                                    $shipit_id = $admin_shipit->id;
                                    $data_seller = wp_remote_get('https://orders.shipit.cl/v/integrations/seller/woocommerce', $config);
                                    $config_shipit = json_decode($data_seller['body']);
                                    $order = wc_get_order( $order_id );
                                    $country = $order->get_billing_country();
                                    $state = $order->get_billing_state();
                                    $name_comune = WC()->countries->get_states( $country )[$state];
                                    if($order->is_paid())
                                    $paid = __('yes');
                                    else
                                    $paid = __('no');
                                    
                                    $height = 0;
                                    $width = 0;
                                    $length = 0;
                                    
                                    $forms = new Shipit_Shipping();
                                    
                                    $setup_type_packing = $forms->settings['type_packing'];
                                    $setup_packing_set = $forms->settings['packing_set'];
                                    $setup_weight_set = $forms->settings['weight_set'];
                                    $setup_width = $forms->settings['width'];
                                    $setup_height = $forms->settings['height'];
                                    $setup_length = $forms->settings['length'];
                                    $setup_weight =$forms->settings['weight'];
                                    $weight_unit = get_option('woocommerce_weight_unit');
                                    $dimension_unit = get_option('woocommerce_dimension_unit');

                                    $width_plus = 0;
                                    $height_plus = 0;
                                    $length_plus = 0;
                                    $weight_plus = 0;
                                    $inventory = array();
                                    switch ($weight_unit) {
                                        case 'oz':
                                            $divider_weight = 35.274;
                                        break;
                                        case 'lbs':
                                            $divider_weight = 2.2046;
                                        break;
                                        case 'kg':
                                            $divider_weight = 1;
                                        break;
                                    }
                                    switch ($dimension_unit) {
                                        case 'mm':
                                            $divider_dimension = 10.000;
                                        break;
                                        case 'yd':
                                            $divider_dimension = 0.010936;
                                        break;
                                        case 'm':
                                            $divider_dimension = 0.010000;
                                        break;
                                        case 'in':
                                            $divider_dimension = 0.39370;
                                        break;
                                        case 'cm':
                                            $divider_dimension = 1;
                                        break;
                                    }
                                    foreach ( $order->get_items() as $item ){
                                        
                                        if ($item['variation_id'] != '' && isset($item['variation_id'])) { 
                                            $product = wc_get_product($item['variation_id']);
                                            $product_id = $item['variation_id'];
                                        } else {
                                            $product = wc_get_product($item['product_id']);
                                            $product_id = $item['product_id'];
                                        }
                                        $product = wc_get_product( $product_id );
                                        $h = $product->get_height();
                                        $w = $product->get_width();
                                        $l = $product->get_length();
                                        $sku = ($product->get_sku() != '') ? $product->get_sku() : $product_id;
                                        
                                        $qty = WC()->cart->get_cart_item_quantities();
                                        if ($setup_packing_set == 0) {
                                            
                                            $height = $product->get_height();
                                            $width = $product->get_width(); 
                                            $length = $product->get_length();
                                        }elseif ($setup_packing_set == 1) {
                                            
                                            $height = $setup_height;
                                            $width = $setup_width;
                                            $length = $setup_length;
                                        }elseif ($setup_packing_set == 2) {
                                            
                                            
                                            $height = ($product->get_height() != '' && $product->get_height() != 0) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = ($product->get_width() != '' && $product->get_width() != 0) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = ($product->get_length() != '' && $product->get_length() != 0) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                            
                                        }elseif ($setup_packing_set == 3) {
                                            
                                            $height = (($product->get_height()/$divider_dimension) > $setup_height) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = (($product->get_width()/$divider_dimension) > $setup_width) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = (($product->get_length()/$divider_dimension) > $setup_length) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                            
                                        }elseif ($setup_packing_set == 4) {
                                            
                                            $height = (($product->get_height()/$divider_dimension) < $setup_height) ? ($product->get_height()/$divider_dimension) : $setup_height;
                                            $width = (($product->get_width()/$divider_dimension) < $setup_width) ? ($product->get_width()/$divider_dimension) : $setup_width;
                                            $length = (($product->get_length()/$divider_dimension) < $setup_length) ? ($product->get_length()/$divider_dimension) : $setup_length;
                                        }
                                        if ($setup_weight_set == 0) {
                                            $weight = $product->get_weight();
                                            
                                        }elseif ($setup_weight_set == 1) {
                                            $weight = $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 2) {
                                            $weight = ($product->get_weight() != '' && $product->get_weight() != 0) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 3) {
                                            $weight = (($product->get_weight()/$divider_weight) > $setup_weight) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }elseif ($setup_weight_set == 4) {
                                            $weight = (($product->get_weight()/$divider_weight) < $setup_weight) ? ($product->get_weight()/$divider_weight) : $setup_weight;
                                            
                                        }
                                        
                                        $sizes_packages[] = [   
                                            'width'         => (float)$width,
                                            'height'        => (float)$height,
                                            'length'        => (float)$length,
                                            'weight'        => (float)$weight,
                                            'quantity'      => (int)$item['quantity']
                                        ];
                                           
                                        $sizes[] = [
                                            "width" => (float)$width,
                                            "height" => (float)$height,
                                            "length" => (float)$length,
                                            "volumetric_weight" => (float)$weight,
                                        ];

                                        # here iterate and insert skus from shipit
                                        if (!empty($skus_array)) {
                                            foreach ($skus_array as $sku_object) {
                                                # here find sku from product at store
                                                if (strtolower($sku_object['name']) == strtolower($sku)) {
                                                    // New sku object
                                                    //$inventory_sku = new Sku($sku_object['id'], $sku_object['amount'], $sku_object['description'], $sku_object['warehouse_id']);
                                                    // push sku object
                                                    array_push($inventory, [
                                                        "sku_id" => $sku_object['id'],
                                                        "amount" => $item['qty'],
                                                        "description" => $sku_object['description'],
                                                        "warehouse_id" => $sku_object['warehouse_id']
                                                    ]);
                                                }
                                            }
                                        }

                                        $width_plus += (float)$width * (int)$item['quantity'];
                                        $height_plus = ((float)$height > $height_plus) ? (float)$height : $height_plus;
                                        $length_plus = ((float)$length > $length_plus) ? (float)$length : $length_plus;
                                        $weight_plus = (float)$weight * (int)$item['quantity'];
                                    }

                                    foreach ( $order->get_items('shipping')as $shipping_id => $shipping_item_obj ){
                                        $shipping_item_data = $shipping_item_obj->get_data()['method_id'];
                                    }
                                    $shipping = $shipping_item_data;
                                    
                                    $shipit = true;
                                    
                                    if($shipping == 'shipit'){
                                        $shipit = false;
                                    }
                                    
                                    $testStreets    = array();
                                    $testStreets[]    = $order->get_shipping_address_1();
                                    for ($i = 0, $totalTestStreets = count($testStreets); $i < $totalTestStreets; $i++) {    
                                        
                                        $address = split_street($testStreets[$i]);
                                        
                                    }
                                    $forms = new Shipit_Shipping();
                                    $setup_type_packing = $forms->settings['type_packing'];
                                    
                                    if ($admin_shipit->platform_version == 2 ){
                                        if($config_shipit->configuration->automatic_delivery === false){
                                            
                                            $body = [
                                                "order" => [
                                                    'mongo_order_seller' => 'woocommerce',
                                                    'seller_order_id'     => $order_id,
                                                    'reference'           => '#'.$order_id,
                                                    'full_name'           => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                                                    'email'               => $order->get_billing_email(),
                                                    'items_count'         => $order->get_item_count(),
                                                    'cellphone'           => $order->get_billing_phone(),
                                                    'is_payable'          => false,
                                                    'packing'             => $setup_type_packing,
                                                    'shipping_type'       => 'Normal',
                                                    'destiny'             => 'Domicilio',
                                                    'courier_for_client'  => $order->get_shipping_method(),
                                                    'approx_size'         => 'Mediano ('.$h.'x'.$l.'x'.$w.'cm)',
                                                    'sent' => $shipit,
                                                    'address_attributes'  => [
                                                        'commune_id'      => (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT),
                                                        'street'          => ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1(),
                                                        'number'          => $address['number'],
                                                        'complement'      => $order->get_shipping_address_2(),
                                                    ],
                                                    "inventory_activity" => [
                                                        "inventory_activity_orders_attributes"=>    
                                                        $inventory,
                                                    ],
                                                    ]
                                                ];
                                                
                                            }else{
                                                $body = [
                                                    "package" => [
                                                        'mongo_order_seller'  => 'woocommerce',
                                                        'seller_order_id'     => $order_id,
                                                        'reference'           => '#'.$order_id,
                                                        'full_name'           => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                                                        'email'               => $order->get_billing_email(),
                                                        'items_count'         => $order->get_item_count(),
                                                        'cellphone'           => $order->get_billing_phone(),
                                                        'is_payable'          => false,
                                                        'packing'             => $setup_type_packing,
                                                        'shipping_type'       => 'Normal',
                                                        'destiny'             => 'Domicilio',
                                                        'courier_for_client'  => $order->get_shipping_method(),
                                                        'approx_size'         => 'Mediano ('.$h.'x'.$l.'x'.$w.'cm)',
                                                        'sent' => $shipit,
                                                        'address_attributes'  => [
                                                            'commune_id'      => (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT),
                                                            'street'          => ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1(),
                                                            'number'          => $address['number'],
                                                            'complement'      => $order->get_shipping_address_2(),
                                                            ]
                                                            ]
                                                        ];
                                                        
                                                        $url = 'https://api.shipit.cl/v/packages';
                                                    }
                                                }else {
                                                    
                                                    $headers = array( 
                                                        'Content-Type' => 'application/json',
                                                        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                                        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                                        'Accept' => 'application/vnd.orders.v1',
                                                    );
                                                    $url = 'https://orders.shipit.cl/v/orders';
                                                    
                                                    $json_array = [
                                                        'packages' => 
                                                        $sizes_packages,   
                                                        
                                                    ];
                                                    $pload = array(
                                                        'method' => 'POST',
                                                        'headers' => $headers,
                                                        'body' => json_encode($json_array),
                                                    );
                                                    
                                                    if((int)$item['quantity'] > 1){
                                                       
                                                    $data = wp_remote_post('https://boxify.shipit.cl/packs', $pload);
                                                    $response_code = wp_remote_retrieve_response_code( $data );
                                                    $body_request = ($response_code === 200) ?  json_decode($data['body']) :  null;
                                                    
                                                    } else {
                                                        $body_request = null;
                                                    }
                                                    
                                                    $body = '';
                                                    if($config_shipit->configuration->automatic_delivery === false){
                                                        $request_params = array();
                                                        $request_params['order'] = array();
                                                        $request_params['order']['platform'] = 'integration';
                                                        $request_params['order']['kind'] = 'woocommerce';
                                                        $request_params['order']['reference'] = '#'.$order_id;
                                                        $request_params['order']['items'] = $order->get_item_count();
                                                        $request_params['order']['sandbox'] = false;
                                                        $request_params['order']['company_id'] = $shipit_id;
                                                        $request_params['order']['service'] = $services;
                                                        $request_params['order']['state'] = 1;
                                                        $request_params['order']['products'] = $inventory; 
                                                        $request_params['order']['payable'] = false;
                                                        $request_params['order']['payment'] = array();
                                                        $request_params['order']['payment']['type'] = '';
                                                        $request_params['order']['payment']['subtotal'] = 0;
                                                        $request_params['order']['payment']['tax'] = 0;
                                                        $request_params['order']['payment']['currency'] = 0;
                                                        $request_params['order']['payment']['discounts'] = 0;
                                                        $request_params['order']['payment']['total'] = (int)$order->total;
                                                        $request_params['order']['payment']['status'] = '';
                                                        $request_params['order']['payment']['confirmed'] = false;
                                                        $request_params['order']['source'] = array();
                                                        $request_params['order']['source']['channel'] = '';
                                                        $request_params['order']['source']['ip'] = '';
                                                        $request_params['order']['source']['browser'] = '';
                                                        $request_params['order']['source']['language'] = '';
                                                        $request_params['order']['source']['location'] = '';
                                                        $request_params['order']['seller'] = array();
                                                        $request_params['order']['seller']['status'] = $order->status;
                                                        $request_params['order']['seller']['name'] = 'woocommerce';
                                                        $request_params['order']['seller']['id'] = $order->id;
                                                        $request_params['order']['seller']['reference_site'] = get_site_url();
                                                        $request_params['order']['gift_card'] = array();
                                                        $request_params['order']['gift_card']['from'] = '';
                                                        $request_params['order']['gift_card']['amount'] = 0;
                                                        $request_params['order']['gift_card']['total_amount'] = 0;
                                                        $request_params['order']['sizes'] = array();
                                                        $request_params['order']['sizes']['width'] = ($body_request != null) ? $body_request->packing_measures->width : $width_plus;
                                                        $request_params['order']['sizes']['height'] = ($body_request != null) ? $body_request->packing_measures->height : $height_plus;
                                                        $request_params['order']['sizes']['length'] = ($body_request != null) ? $body_request->packing_measures->length : $length_plus;
                                                        $request_params['order']['sizes']['weight'] = ($body_request != null) ? $body_request->packing_measures->weight : $weight_plus;
                                                        $request_params['order']['sizes']['volumetric_weight'] = ($width_plus * $height_plus * $length_plus) / 4000;
                                                        $request_params['order']['sizes']['store'] = false;
                                                        $request_params['order']['sizes']['packing_id'] = null;
                                                        $request_params['order']['sizes']['name'] = '';
                                                        $request_params['order']['courier'] = array();
                                                        $request_params['order']['courier']['client'] = $order->get_shipping_method();
                                                        $request_params['order']['prices'] = array();
                                                        $request_params['order']['prices']['total'] = (int)$order->total;
                                                        $request_params['order']['prices']['price'] = (int)$order->shipping_total;
                                                        $request_params['order']['prices']['cost'] = 0;
                                                        $request_params['order']['prices']['insurance'] = 0;
                                                        $request_params['order']['prices']['tax'] = (int)$order->cart_tax;
                                                        $request_params['order']['prices']['overcharge'] = 0;
                                                        $request_params['order']['insurance'] = array();
                                                        $request_params['order']['insurance']['ticket_amount'] = 0;
                                                        $request_params['order']['insurance']['ticket_number'] = 392832;
                                                        $request_params['order']['insurance']['name'] = 'Sólido';
                                                        $request_params['order']['insurance']['store'] = false;
                                                        $request_params['order']['insurance']['company_id'] = '1';
                                                        $request_params['order']['state_track'] = array();
                                                        $request_params['order']['state_track']['draft'] = '';
                                                        $request_params['order']['state_track']['confirmed'] = '2019-06-07T17:13:09.141-04:00';
                                                        $request_params['order']['state_track']['deliver'] = '';
                                                        $request_params['order']['state_track']['canceled'] = '';
                                                        $request_params['order']['state_track']['archived'] = '';
                                                        $request_params['order']['origin'] = array();
                                                        $request_params['order']['origin']['street'] = '';
                                                        $request_params['order']['origin']['number'] = '';
                                                        $request_params['order']['origin']['complement'] = '';
                                                        $request_params['order']['origin']['commune_id'] = '';
                                                        $request_params['order']['origin']['full_name'] = '';
                                                        $request_params['order']['origin']['email'] = '';
                                                        $request_params['order']['origin']['phone'] = '';
                                                        $request_params['order']['origin']['store'] = false;
                                                        $request_params['order']['origin']['origin_id'] = null;
                                                        $request_params['order']['origin']['name'] = '';
                                                        $request_params['order']['destiny'] = array();
                                                        $request_params['order']['destiny']['street'] = ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1();
                                                        $request_params['order']['destiny']['number'] = $address['number'];
                                                        $request_params['order']['destiny']['complement'] = (isset($address['numberAddition']) && $address['numberAddition']!='' ) ? $address['numberAddition'].'/'.$order->get_shipping_address_2() : $order->get_shipping_address_2();
                                                        $request_params['order']['destiny']['commune_id'] = (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT);
                                                        $request_params['order']['destiny']['commune_name'] = $name_comune;
                                                        $request_params['order']['destiny']['full_name'] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
                                                        $request_params['order']['destiny']['email'] = $order->get_billing_email();
                                                        $request_params['order']['destiny']['phone'] = $order->get_billing_phone();
                                                        $request_params['order']['destiny']['store'] = false;
                                                        $request_params['order']['destiny']['destiny_id'] = null;
                                                        $request_params['order']['destiny']['name'] = 'predeterminado';
                                                        $request_params['order']['destiny']['courier_branch_office_id'] = null;
                                                        $request_params['order']['destiny']['kind'] = 'home_delivery';
                                                        
                                                        $body = $request_params;
                                                        
                                                    }else {
                                                        $request_params = array();
                                                        $request_params['order'] = array();
                                                        $request_params['order']['platform'] = 'integration';
                                                        $request_params['order']['kind'] = 'woocommerce';
                                                        $request_params['order']['reference'] = '#'.$order_id;
                                                        $request_params['order']['items'] = $order->get_item_count();
                                                        $request_params['order']['sandbox'] = false;
                                                        $request_params['order']['company_id'] = $shipit_id;
                                                        $request_params['order']['service'] = $services;
                                                        $request_params['order']['state'] = 1;
                                                        $request_params['order']['products'] = $inventory; 
                                                        $request_params['order']['payable'] = false;
                                                        $request_params['order']['payment'] = array();
                                                        $request_params['order']['payment']['type'] = '';
                                                        $request_params['order']['payment']['subtotal'] = 0;
                                                        $request_params['order']['payment']['tax'] = 0;
                                                        $request_params['order']['payment']['currency'] = 0;
                                                        $request_params['order']['payment']['discounts'] = 0;
                                                        $request_params['order']['payment']['total'] = (int)$order->total;
                                                        $request_params['order']['payment']['status'] = '';
                                                        $request_params['order']['payment']['confirmed'] = false;
                                                        $request_params['order']['source'] = array();
                                                        $request_params['order']['source']['channel'] = '';
                                                        $request_params['order']['source']['ip'] = '';
                                                        $request_params['order']['source']['browser'] = '';
                                                        $request_params['order']['source']['language'] = '';
                                                        $request_params['order']['source']['location'] = '';
                                                        $request_params['order']['seller'] = array();
                                                        $request_params['order']['seller']['status'] = $order->status;
                                                        $request_params['order']['seller']['name'] = 'woocommerce';
                                                        $request_params['order']['seller']['id'] = $order->id;
                                                        $request_params['order']['seller']['reference_site'] = get_site_url();
                                                        $request_params['order']['gift_card'] = array();
                                                        $request_params['order']['gift_card']['from'] = '';
                                                        $request_params['order']['gift_card']['amount'] = 0;
                                                        $request_params['order']['gift_card']['total_amount'] = 0;
                                                        $request_params['order']['sizes'] = array();
                                                        $request_params['order']['sizes']['width'] = ($body_request != null) ? $body_request->packing_measures->width : $width_plus;
                                                        $request_params['order']['sizes']['height'] = ($body_request != null) ? $body_request->packing_measures->height : $height_plus;
                                                        $request_params['order']['sizes']['length'] = ($body_request != null) ? $body_request->packing_measures->length : $length_plus;
                                                        $request_params['order']['sizes']['weight'] = ($body_request != null) ? $body_request->packing_measures->weight : $weight_plus;
                                                        $request_params['order']['sizes']['volumetric_weight'] = $width_plus * $height_plus * $length_plus;
                                                        $request_params['order']['sizes']['store'] = false;
                                                        $request_params['order']['sizes']['packing_id'] = null;
                                                        $request_params['order']['sizes']['name'] = '';
                                                        $request_params['order']['courier'] = array();
                                                        $request_params['order']['courier']['client'] = $order->get_shipping_method();
                                                        $request_params['order']['prices'] = array();
                                                        $request_params['order']['prices']['total'] = (int)$order->total;
                                                        $request_params['order']['prices']['price'] = (int)$order->shipping_total;
                                                        $request_params['order']['prices']['cost'] = 0;
                                                        $request_params['order']['prices']['insurance'] = 0;
                                                        $request_params['order']['prices']['tax'] = (int)$order->cart_tax;
                                                        $request_params['order']['prices']['overcharge'] = 0;
                                                        $request_params['order']['insurance'] = array();
                                                        $request_params['order']['insurance']['ticket_amount'] = 0;
                                                        $request_params['order']['insurance']['ticket_number'] = 392832;
                                                        $request_params['order']['insurance']['name'] = 'Sólido';
                                                        $request_params['order']['insurance']['store'] = false;
                                                        $request_params['order']['insurance']['company_id'] = '1';
                                                        $request_params['order']['state_track'] = array();
                                                        $request_params['order']['state_track']['draft'] = '';
                                                        $request_params['order']['state_track']['confirmed'] = '2019-06-07T17:13:09.141-04:00';
                                                        $request_params['order']['state_track']['deliver'] = '';
                                                        $request_params['order']['state_track']['canceled'] = '';
                                                        $request_params['order']['state_track']['archived'] = '';
                                                        $request_params['order']['origin'] = array();
                                                        $request_params['order']['origin']['street'] = '';
                                                        $request_params['order']['origin']['number'] = '';
                                                        $request_params['order']['origin']['complement'] = '';
                                                        $request_params['order']['origin']['commune_id'] = '';
                                                        $request_params['order']['origin']['full_name'] = '';
                                                        $request_params['order']['origin']['email'] = '';
                                                        $request_params['order']['origin']['phone'] = '';
                                                        $request_params['order']['origin']['store'] = false;
                                                        $request_params['order']['origin']['origin_id'] = null;
                                                        $request_params['order']['origin']['name'] = '';
                                                        $request_params['order']['destiny'] = array();
                                                        $request_params['order']['destiny']['street'] = ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1();
                                                        $request_params['order']['destiny']['number'] = $address['number'];
                                                        $request_params['order']['destiny']['complement'] = (isset($address['numberAddition']) && $address['numberAddition']!='' ) ? $address['numberAddition'].'/'.$order->get_shipping_address_2() : $order->get_shipping_address_2();
                                                        $request_params['order']['destiny']['commune_id'] = (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT);
                                                        $request_params['order']['destiny']['commune_name'] = $name_comune;
                                                        $request_params['order']['destiny']['full_name'] = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
                                                        $request_params['order']['destiny']['email'] = $order->get_billing_email();
                                                        $request_params['order']['destiny']['phone'] = $order->get_billing_phone();
                                                        $request_params['order']['destiny']['store'] = false;
                                                        $request_params['order']['destiny']['destiny_id'] = null;
                                                        $request_params['order']['destiny']['name'] = 'predeterminado';
                                                        $request_params['order']['destiny']['courier_branch_office_id'] = null;
                                                        $request_params['order']['destiny']['kind'] = 'home_delivery';
                                                        
                                                        $body = $request_params;
                                                        
                                                        $args = array(
                                                            'body' => json_encode($body),
                                                            'timeout' => '5',
                                                            'redirection' => '5',
                                                            'httpversion' => '1.0',
                                                            'blocking' => true,
                                                            'headers' => $headers,
                                                            'cookies' => array()
                                                        );
                                                        
                                                        $response_order = wp_remote_post( $url, $args );
                                                        $campo = json_decode($response_order['body']);
                                                        
                                                        $body = array();
                                                        $body['order'] = array();
                                                        $body['order']['id'] = $campo->id;
                                                        
                                                        $headers = array( 
                                                            'Content-Type' => 'application/json',
                                                            'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                                            'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                                            'Accept' => 'application/vnd.shipit.v4',
                                                        );
                                                        $url = 'https://api.shipit.cl/v/shipments';
                                                    }
                                                    
                                                }
                                            }
                                            $args = array(
                                                'body' => json_encode($body),
                                                'timeout' => '5',
                                                'redirection' => '5',
                                                'httpversion' => '1.0',
                                                'blocking' => true,
                                                'headers' => $headers,
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
                                    function split_street($streetStr) {
                                        
                                        $aMatch         = array();
                                        $pattern        = '/^([\w[:punct:] ]+) ([0-9]{1,5})([\w[:punct:]\-]*)$/';
                                        $matchResult    = preg_match($pattern, $streetStr, $aMatch);
                                        
                                        $street         = (isset($aMatch[1])) ? $aMatch[1] : '';
                                        $number         = (isset($aMatch[2])) ? $aMatch[2] : '';
                                        $numberAddition = (isset($aMatch[3])) ? $aMatch[3] : '';
                                        
                                        return array('street' => $street, 'number' => $number, 'numberAddition' => $numberAddition);
                                        
                                    }
                                    