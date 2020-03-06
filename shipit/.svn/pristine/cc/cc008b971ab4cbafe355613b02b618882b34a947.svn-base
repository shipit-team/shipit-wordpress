<?php
add_filter( 'bulk_actions-edit-shop_order', 'shipit_bulk_actions_edit_product', 20, 1 );
function shipit_bulk_actions_edit_product( $actions ) {
    $actions['send_orders'] = __( 'Enviar a Shipit', 'woocommerce' );
    return $actions;
}


add_filter( 'handle_bulk_actions-edit-shop_order', 'shipit_handle_bulk_action_edit_shop_order', 10, 3 );
function shipit_handle_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
    if ( $action !== 'send_orders' )
    return $redirect_to; 
    
    global $attach_download_dir, $attach_download_file; 
    $headers = array( 
        'Content-Type' => 'application/json',
        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
        'Accept' => 'application/vnd.shipit.v2',
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
    $processed_ids = array();
    
    $url = 'http://api.shipit.cl/v/orders/massive';
    
    foreach ( $post_ids as $post_id) {
        $i++;
        $order = wc_get_order( $post_id );
        $country = $order->get_billing_country();
        $state = $order->get_shipping_state();
        $name_comune = WC()->countries->get_states( $country )[$state];
        if($order->is_paid())
        $paid = __('yes');
        else
        $paid = __('no');
        
        $height = 0;
        $width = 0;
        $length = 0;
        
        $forms = get_option ('woocommerce_shipit_settings' );
        
        $setup_type_packing = $forms['type_packing'];
        $setup_packing_set = $forms['packing_set'];
        $setup_weight_set = $forms['weight_set'];
        $setup_width = $forms['width'];
        $setup_height = $forms['height'];
        $setup_length = $forms['length'];
        $setup_weight =$forms['weight'];
        $weight_unit = get_option('woocommerce_weight_unit');
        $dimension_unit = get_option('woocommerce_dimension_unit');
        switch ($weight_unit) {
            case 'oz':
            $divider_weight = 35.274;
            break;
            case 'lbs':
            $divider_weight = 2.2046;
            break;
            case 'g':
                $divider_weight = 1000;
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
        $width_plus = 0;
        $height_plus = 0;
        $length_plus = 0;
        $weight_plus = 0;
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
            if ($setup_packing_set == 0) {
                
                $height = $product->get_height();
                $width = $product->get_width(); 
                $length = $product->get_length();
            }elseif ($setup_packing_set == 1) {
                
                $height = $setup_height;
                $width = $setup_width;
                $length = $setup_length;
            }elseif ($setup_packing_set == 2) {
                
                $height = ($product->get_height() != '') ? ($product->get_height()/$divider_dimension) : $setup_height;
                $width = ($product->get_width() != '') ? ($product->get_width()/$divider_dimension) : $setup_width;
                $length = ($product->get_length() != '') ? ($product->get_length()/$divider_dimension) : $setup_length;
                
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
                $weight = ($product->get_weight() != '') ? ($product->get_weight()/$divider_weight) : $setup_weight;
                
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
            
            $width_plus += (float)$width * (int)$item['quantity'];
            $height_plus = ((float)$height > $height_plus) ? (float)$height : $height_plus;
            $length_plus = ((float)$length > $height_plus) ? (float)$length : $height_plus;
            $weight_plus += (float)$weight * (int)$item['quantity'];
            
            $sizes[] = [
                "width" => (float)$width,
                "height" => (float)$height,
                "length" => (float)$length,
                "volumetric_weight" => (float)$weight,
            ];
            
            $inventory[] = [   
                "sku_id" => $sku,
                "amount" => $item['qty'],
                "id" => $product_id,
                "description" => $product->get_data('short_description'),
                "warehouse_id" => 1
            ];
        }
        foreach ( $order->get_items('shipping')as $shipping_id => $shipping_item_obj ){
            $shipping_item_data = $shipping_item_obj->get_data()['method_id'];
        }
        $shipping = $shipping_item_data;
        
        $shipit = false;
        
        $testStreets    = array();
        $testStreets[]    = $order->get_shipping_address_1();
        for ($i = 0, $totalTestStreets = count($testStreets); $i < $totalTestStreets; $i++) {    
            
            $address = split_street($testStreets[$i]);
            
        }
        
        $setup_type_packing = $forms['type_packing'];
        
        
        $administrative = array(
            'headers' => $headers_administrative,
        );
        
        $data = wp_remote_get('http://api.shipit.cl/v/setup/administrative', $administrative);
        $admin_shipit = json_decode($data['body']);
        $shipit_id = $admin_shipit->id;
      
        if ($admin_shipit->platform_version == 2 ){
                $headers = array( 
                        'Content-Type' => 'application/json',
                        'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                        'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                        'Accept' => 'application/vnd.shipit.v2',
                    );     
                    $body_encode[] = [
                            'mongo_order_seller' => 'woocommerce',
                            'reference'           => '#'.$post_id,
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
                                    'commune_id'      => (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT),
                                    'street'          => $order->get_shipping_address_1(),
                                    'number'          => '',
                                    'complement'      => $order->get_shipping_address_2(),
                                ],
                                "inventory_activity" => [
                                        "inventory_activity_orders_attributes"=>    
                                        $inventory,
                                    ],
                                ];
                                $processed_ids[] = $post_id;
                            
                            }else {
                                $header = array( 
                                    'Content-Type' => 'application/json',
                                    'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                    'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                    'Accept' => 'application/vnd.shipit.v2',
                                );
                                $headers = array( 
                                    'Content-Type' => 'application/json',
                                    'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
                                    'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
                                    'Accept' => 'application/vnd.orders.v1',
                                );
                                $url = 'http://orders.shipit.cl/v/orders/massive';
                                
                                $json_array = [
                                    'packages' => 
                                    $sizes_packages,   
                                    
                                ];
                                $pload = array(
                                    'method' => 'POST',
                                    'headers' => $header,
                                    'body' => json_encode($json_array),
                                );
                                
                                $data = wp_remote_post('https://boxify.shipit.cl/packs', $pload);
                                
                                $body_request = json_decode($data['body']);
                                
                                
                                
                                
                                $body_encode[] = [
                                    'service' => '',
                                    'state' => 1,
                                    'kind' => 'woocommerce',
                                    'platform' => 'integration',
                                    'reference' => '#'.$post_id,
                                    'items' => $order->get_item_count(),
                                    'courier' => [
                                        'client' => null,
                                    ],
                                    'origin' => [
                                        'street' => ($address['street'] != '') ? $address['street'] : $order->get_billing_address_1(),
                                        'number' => $address['number'],
                                        'complement' => $order->get_billing_address_2(),
                                        'commune_id' => (int) filter_var($order->get_billing_state(), FILTER_SANITIZE_NUMBER_INT),
                                        'full_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                                        'email' => $order->get_billing_email(),
                                        'phone' => $order->get_billing_phone(),
                                        'store' => false,
                                        'origin_id' => null,
                                        'name' => 'predeterminado',
                                    ],
                                    'destiny' => [
                                        'street' => ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1(),
                                        'number' => $address['number'],
                                        'complement' => $order->get_shipping_address_2(),
                                        'commune_id' => (int) filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT),
                                        'commune_name' => $name_comune,
                                        'full_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                                        'email' => $order->get_billing_email(),
                                        'phone' => $order->get_billing_phone(),
                                        'store' => false,
                                        'destiny_id' => null,
                                        'name' => 'predeterminado',
                                        'courier_branch_office_id' => null,
                                        'kind' => 'home_delivery',
                                    ],
                                    'sizes' => [
                                        'width' => ($body_request != null) ? $body_request->packing_measures->width : $width_plus,
                                        'height' => ($body_request != null) ? $body_request->packing_measures->height : $height_plus,
                                        'length' => ($body_request != null) ? $body_request->packing_measures->length : $length_plus,
                                        'weight' => ($body_request != null) ? $body_request->packing_measures->weight : $weight_plus,
                                        'volumetric_weight' => $width_plus * $height_plus * $length_plus,
                                        'store' => false,
                                        'packing_id' => null,
                                        'name' => '',
                                    ],
                                    'courier' => [
                                        'client' => $order->get_shipping_method(),
                                    ],
                                    'prices' => [
                                        'total' => (int)$order->total,
                                        'price' => (int)$order->shipping_total,
                                        'cost' => 0,
                                        'insurance' => 0,
                                        'tax' => (int)$order->cart_tax,
                                        'overcharge' => 0,
                                    ],
                                    'seller' => [
                                            'status' => $order->status,
                                            'name' => 'woocommerce',
                                            'id' => $order->id,
                                            'reference_site' => get_site_url(),
                                    ],
                                ];
                                $processed_ids[] = $post_id;
                                
                                }
                            }
                            
                            
                            $body = [
                                'orders' => 
                                $body_encode
                                
                            ];
                            $body = json_encode($body);
                            $args = array(
                                'body' => $body,
                                'timeout' => '5',
                                'redirection' => '5',
                                'httpversion' => '1.0',
                                'blocking' => true,
                                'headers' => $headers,
                                'cookies' => array()
                            );
                            $response = wp_remote_post( $url, $args );
                            $response_code = wp_remote_retrieve_response_code( $response );

                            foreach ($processed_ids as $key => $order_id) {

                                $order = new WC_Order($order_id );
                                $order->add_order_note('Se ha enviado a Shipit correctamente mediante acción masiva'); 
                            }
                            
                            return $redirect_to = add_query_arg( array(
                                'send_orders' => '1',
                                'processed_count' => count( $processed_ids ),
                                'processed_ids' => implode( ',', $processed_ids ),
                            ), $redirect_to );
                        }
                        
                        
                        add_action( 'admin_notices', 'shipit_bulk_action_admin_notice' );
                        function shipit_bulk_action_admin_notice() {
                            if ( empty( $_REQUEST['send_orders'] ) ) return; 
                            
                            $count = intval( $_REQUEST['processed_count'] );
                            
                            $class = 'notice notice-success is-dismissible';
                            $message = __( 'Se enviaron a Shipit '.$count.' ordenes.', 'sample-text-domain' );
                            
                            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
                        }
                        ?>