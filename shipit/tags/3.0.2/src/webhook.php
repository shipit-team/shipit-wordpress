<?php
  function configureIntegrationSetting() {
    global $wpdb;
    $integration = new Integration(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
    $hashToken = $wpdb->get_var("SELECT user_pass FROM {$wpdb->prefix}users WHERE user_email = 'hola@shipit.cl' ORDER BY id DESC LIMIT 1");
    $setting = $integration->setting();
    return $integration->configure([
      'name' => 'woocommerce',
      'configuration' => [
        'client_id'   => get_bloginfo('name') . '_shipit',
        'client_secret' => $hashToken,
        'store_name' => get_bloginfo('name'),
        'checkout' => $setting->checkout
      ]
    ]);
  }

  function configureWebhookSetting() {
    global $wpdb;
    $baseEncodeUserShipit = $wpdb->get_var("SELECT temp FROM {$wpdb->prefix}user_shipit ORDER BY id DESC LIMIT 1");
    $core = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v4');
    return $core->setWebhook([
      'webhook' => [
        'package' => [
          'url' => get_site_url().'/wp-json/shipit/orders/',
          'options' => [
            'sign_body' => [
              'required' => false,
              'token' => ''
            ],
            'authorization' => [
              'required' => true,
              'kind' => 'Basic',
              'token' => $baseEncodeUserShipit
            ]
          ]
        ]
      ]
    ]);
  }

  add_action('admin_post_add_foobar', 'shipit_admin_add_foobar');
  function shipit_admin_add_foobar() {
    global $wpdb;

    $webhookResponse = configureWebhookSetting();
    $integrationResponse = configureIntegrationSetting();
    if (isset($webhookResponse->webhook) && isset($integrationResponse->configuration)) {
      shipit_admin_notice__success();
    } else {
      shipit_admin_notice__error();
    }
  }

  add_action('admin_head', 'styling_admin_order_list');
  function styling_admin_order_list() {
    $order_status = 'status-invoiced';
    ?>
    <style>
    .order-status.status-in_preparation {background-color: #58b5f4;color: #fff;}
    .order-status.status-in_route {background-color: #f4cf58;color: #fff;}
    .order-status.status-ready_to_dispatch {background-color: #1f97e7;color: #fff;}
    .order-status.status-dispatched {background-color: #0f7cc5;color: #fff;}
    .order-status.status-failed {background-color: #dd7272;color: #fff;}
    .order-status.status-other {background-color: #484a7d;color: #fff;}
    .order-status.status-by_retired {background-color: #00c2de;color: #fff;}
    .order-status.status-pending {background-color: #cc0000;color: #fff;}
    .order-status.status-at_shipit {background-color: #00c2de;color: #fff;}
    .order-status.status-indemnify{background-color: #484a7d;color: #fff;}
    .order-status.status-delivered {background-color: #04c778;color: #fff;}
    </style>
    <?php
  }

  add_action('init', 'shipit_register_my_new_order_statuses');
  function shipit_register_my_new_order_statuses() {
    register_post_status('wc-in_preparation', array(
      'label' => _x('in_preparation', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('En preparaci&oacute;n <span class="count">(%s)</span>', 'En preparaci&oacute;n<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-in_route', array(
      'label' => _x('in_route', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('En ruta <span class="count">(%s)</span>', 'En ruta<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-delivered', array(
      'label' => _x('delivered', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Entregado <span class="count">(%s)</span>', 'Entregado<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-failed', array(
      'label' => _x('failed', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Fallido <span class="count">(%s)</span>', 'Fallido<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-by_retired', array(
      'label' => _x('by_retired', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Para retiro <span class="count">(%s)</span>', 'Para retiro<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-other', array(
      'label' => _x('other', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop( 'Otros <span class="count">(%s)</span>', 'Otros<span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-slope', array(
      'label' => _x('pending', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Pendiente <span class="count">(%s)</span>', 'Pendiente<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status('wc-to_marketplace', array(
      'label' => _x('to_marketplace', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Hacia comercio <span class="count">(%s)</span>', 'Hacia comercio<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status('wc-indemnify', array(
      'label' => _x('indemnify', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('indemnizar <span class="count">(%s)</span>', 'indemnizar<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status('wc-ready_to_dispatch', array(
      'label' => _x('ready_to_dispatch', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('listo para despacho <span class="count">(%s)</span>', 'listo para despacho<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status( 'wc-dispatched', array(
      'label' => _x('dispatched', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Despachado <span class="count">(%s)</span>', 'Despachado<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status('wc-at_shipit', array(
      'label' => _x('at_shipit', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Hacia Shipit <span class="count">(%s)</span>', 'Hacia Shipit<span class="count">(%s)</span>', 'woocommerce')
    ));
    register_post_status('wc-returned', array(
      'label' => _x('returned', 'Order status', 'woocommerce'),
      'public' => true,
      'exclude_from_search' => false,
      'show_in_admin_all_list' => true,
      'show_in_admin_status_list' => true,
      'label_count' => _n_noop('Devuelto <span class="count">(%s)</span>', 'Devuelto<span class="count">(%s)</span>', 'woocommerce')
    ));
  }

  add_filter('wc_order_statuses', 'shipit_my_new_wc_order_statuses');

  function shipit_my_new_wc_order_statuses($order_statuses) {
    $order_statuses['wc-in_preparation'] = _x('En preparacion', 'Order status', 'woocommerce');
    $order_statuses['wc-in_route'] = _x('En ruta', 'Order status', 'woocommerce');
    $order_statuses['wc-delivered'] = _x('Entregado', 'Order status', 'woocommerce');
    $order_statuses['wc-failed'] = _x('Fallido', 'Order status', 'woocommerce');
    $order_statuses['wc-by_retired'] = _x('Para Retiro', 'Order status', 'woocommerce');
    $order_statuses['wc-other'] = _x('Otro', 'Order status', 'woocommerce');
    $order_statuses['wc-slope'] = _x('Pendiente', 'Order status', 'woocommerce');
    $order_statuses['wc-to_marketplace'] = _x('Hacia comercio', 'Order status', 'woocommerce');
    $order_statuses['wc-indemnify'] = _x('indemnizar', 'Order status', 'woocommerce');
    $order_statuses['wc-ready_to_dispatch'] = _x('listo para despacho', 'Order status', 'woocommerce');
    $order_statuses['wc-dispatched'] = _x('Despachado', 'Order status', 'woocommerce');
    $order_statuses['wc-at_shipit'] = _x('Hacia Shipit', 'Order status', 'woocommerce');
    $order_statuses['wc-returned'] = _x('Devolucion', 'Order status', 'woocommerce');
    return $order_statuses;
  }

  add_action('rest_api_init', 'my_register_route');
  function my_register_route() {
    register_rest_route('shipit', 'orders', array(
      'methods' => 'POST, PUT, PATCH',
      'callback' => 'shipit_action_woocommerce_update_order' ,
      'permission_callback' => function() {
        return current_user_can('edit_others_posts');
      }
    ));
  }

  function shipit_action_woocommerce_update_order(WP_REST_Request $request) {
    $param = $request->get_body();
    $json = json_decode($param);
    $int = (int)preg_replace('/\D/ui','',$json->reference);
    $order = new WC_Order($int);
    $statuses = wc_get_order_statuses();
    $status_label = isset($statuses['wc-'.$json->status]) ? $json->status : 'other';
    $order->update_status($status_label, 'Estado actualizado por Shipit');
    return rest_ensure_response($int);
  }

  function shipit_admin_notice__success() {
    ?>
    <div class="notice notice-success is-dismissible">
    <p><?php _e('Credenciales enviadas correctamente a Shipit', 'sample-text-domain'); ?></p>
    </div>
    <?php
  }

  function shipit_admin_notice__error() {
    ?>
    <div class="notice notice-error is-dismissible">
    <p><?php _e('Hubo un error con el envio de las credenciales', 'sample-text-domain'); ?></p>
    </div>
    <?php
  }

  register_activation_hook(__FILE__, 'shipit_install_cleancache');

  function shipit_install_cleancache() {
    wp_cache_delete("clp_usd_shipit", "shipit");
  }

  function add_clp_paypal_valid_currency($currencies) {
    array_push($currencies, 'CLP');
    return $currencies;
  }

  add_filter('woocommerce_paypal_supported_currencies', 'add_clp_paypal_valid_currency');

  function convert_clp_to_usd($paypal_args) {
    $shipit_group = "shipit";
    $shipit_expire = 604800;
    if ($paypal_args['currency_code'] == 'CLP') {
      $currency_value = wp_cache_get('clp_usd_shipit', $shipit_group);
      if ($currency_value === false) {
        $json = wp_remote_get('https://free.currconv.com/api/v7/convert?q=USD_CLP&compact=ultra&apiKey=1379232bb33f7020ad47', $args = array());
        $exchangeRates = json_decode($json['body']);
        $currency_value = (int)$exchangeRates->USD_CLP;
      }
      wp_cache_set('clp_usd_shipit', $currency_value, $shipit_group, $shipit_expire);
      $convert_rate = $currency_value;
      $paypal_args['currency_code'] = 'USD';
      $i = 1;
      while (isset($paypal_args['amount_' . $i])) {
        $paypal_args['amount_' . $i] = round($paypal_args['amount_' . $i] / $convert_rate, 2);
        ++$i;
      }
    }
    return $paypal_args;
  }

  add_filter('woocommerce_paypal_args', 'convert_clp_to_usd');
?>