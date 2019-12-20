<?php

add_filter( 'cron_schedules', 'isa_add_every_fifteen_minutes' );
function isa_add_every_fifteen_minutes( $schedules ) {
    $schedules['every_fifteen_minutes'] = array(
        'interval'  => 900,
        'display'   => __( 'trigger 15', 'send package' )
    );
    return $schedules;
}

if ( ! wp_next_scheduled( 'isa_add_every_fifteen_minutes' ) ) {
    wp_schedule_event( time(), 'every_fifteen_minutes', 'isa_add_every_fifteen_minutes' );
}


add_action( 'isa_add_every_fifteen_minutes', 'every_fifteen_minutes_event_func' );
function every_fifteen_minutes_event_func() {
    $commune_id = cURL_Wrapper('v2', 'http://staging.api.shipit.cl/v/communes', 'GET', null, strtoupper(($order->get_billing_city())));
    $request       = cURL_wrapper_request('v2', 'http://staging.api.shipit.cl/v/packages', 'POST', $order_id, $commune_id);
}


?>