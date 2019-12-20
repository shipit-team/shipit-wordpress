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
    
    $processed_ids = array();
    
    $url = 'http://api.shipit.cl/v/orders/massive';
    
    foreach ( $post_ids as $post_id ) {
        
        $order = wc_get_order( $post_id );
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
        
        $shipit = false;
        
        $json_array[] = [
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
                'commune_id'      => $order->get_shipping_state(),
                'street'          => $order->get_shipping_address_1(),
                'number'          => $order->shipping_houseno,
                'complement'      => $order->get_shipping_address_2(),
            ],
            "inventory_activity" => [
                "inventory_activity_orders_attributes"=>    
                $inventory,
            ],
        ];
        $processed_ids[] = $post_id;
    }
    $json_array = [
        'orders' => 
        $json_array
        
    ];
    $body = json_encode($json_array);
    $args = array(
        'body' => $body,
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Content-Type' => 'application/json',
            'X-Shipit-Email' => get_option ('shipit_user' )['shipit_user'] ,
            'X-Shipit-Access-Token' => get_option ('shipit_user' )['shipit_token'] ,
            'Accept' => 'application/vnd.shipit.v2',
        ),
        'cookies' => array()
    );
    $response = wp_remote_post( $url, $args );
    $response_code = wp_remote_retrieve_response_code( $response );
    
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