<?php
  add_filter('bulk_actions-edit-shop_order', 'shipit_bulk_actions_edit_product', 20, 1);
  function shipit_bulk_actions_edit_product($actions) {
    $actions['send_orders'] = __('Enviar a Shipit', 'woocommerce');
    return $actions;
  }

  add_filter('handle_bulk_actions-edit-shop_order', 'shipit_handle_bulk_action_edit_shop_order', 10, 3);
  function getSelectedProduct($cartItem) {
    return $cartItem['variation_id'] != '' && isset($cartItem['variation_id']) ? wc_get_product($cartItem['variation_id']) : wc_get_product($cartItem['product_id']);
  }

  function shipit_handle_bulk_action_edit_shop_order($redirect_to, $action, $post_ids) {
    if ($action !== 'send_orders') return $redirect_to;

    global $attach_download_dir, $attach_download_file;
    $core = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v4');
    $integration = new Integration(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
    $opit = new Opit(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
    $measuresCollection = new MeasureCollection();
    $helper = new WoocommerceSettingHelper(get_option('woocommerce_weight_unit'), get_option('woocommerce_dimension_unit'));

    $measureConversion = $helper->getMeasureConversion();
    $weightConversion = $helper->getWeightConversion();
    $company = $core->administrative();
    $skus = array();
    if ($company->service->name == 'fulfillment') {
      $skus = $core->skus();
    }

    $insuranceSetting = $core->insurance();
    $opitSetting = $opit->setting();

    $i = 0;
    foreach ($post_ids as $post_id) {
      $i++;
      $order = wc_get_order($post_id);
      $country = $order->get_billing_country();
      $state = $order->get_shipping_state();
      $communeName = WC()->countries->get_states($country)[$state];
      $paid = $order->is_paid() ? __('yes') : __('no');
      $forms = get_option('woocommerce_shipit_settings');
      $inventory = array();
      $productCategories = "";
      foreach ($order->get_items() as $cartItem) {
        $product = getSelectedProduct($cartItem);
        $terms = get_the_terms($product->get_id(), 'product_cat');
        foreach ($terms as $term) {
          $productCategories = $productCategories.' '.$term->slug;
        }
        if (!empty($skus)) {
          $sku = $product->get_sku() != '' ? $product->get_sku() : $product->get_id();
          foreach ($skus as $skuObject) {
            # here find sku from product at store
            if (strtolower($skuObject['name']) == strtolower($sku)) {
              array_push($inventory, [
                'sku_id' => $skuObject['id'],
                'amount' => $cartItem['qty'],
                'description' => $skuObject['description'],
                'warehouse_id' => $skuObject['warehouse_id']
              ]);
            }
            $measure = new Measure((float)$skuObject['width'], (float)$skuObject['height'], (float)$skuObject['length'], (float)$skuObject['weight'], (int)$cartItem['qty']);
            $measuresCollection->setMeasures($measure->buildBoxifyRequest());
          }
        } else {
          $width = $helper->packingSetting($product->get_width(), $forms->settings, 'width', 'packing_set', $measureConversion);
          $height = $helper->packingSetting($product->get_height(), $forms->settings, 'height', 'packing_set', $measureConversion);
          $length = $helper->packingSetting($product->get_length(), $forms->settings, 'length', 'packing_set', $measureConversion);
          $weight = $helper->packingSetting($product->get_weight(), $forms->settings, 'weight', 'weight_set', $weightConversion);

          $measure = new Measure((float)$width, (float)$height, (float)$length, (float)$weight, (int)$cartItem['quantity']);
          $measuresCollection->setMeasures($measure->buildBoxifyRequest());    
        }
      }

      foreach ($order->get_items('shipping')as $shipping_id => $shipping_item_obj){
        $shipping_item_data = $shipping_item_obj->get_data()['method_id'];
      }

      $shipit = false;
      $testStreets = array();
      $testStreets[] = $order->get_shipping_address_1();
      for ($i = 0, $totalTestStreets = count($testStreets); $i < $totalTestStreets; $i++) {
        $address = split_street($testStreets[$i]);
      }
      $parcel = $measuresCollection->calculate();
      if ($company->platform_version == 2) {
        $request_payload[] = [
          'mongo_order_seller' => 'woocommerce',
          'reference' => '#'.$post_id,
          'full_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
          'email' => $order->get_billing_email(),
          'items_count' => $order->get_item_count(),
          'cellphone' => $order->get_billing_phone(),
          'is_payable' => false,
          'packing' => 'Sin empaque',
          'shipping_type' => 'Normal',
          'destiny' => 'Domicilio',
          'courier_for_client' => $order->get_shipping_method(),
          'sent' => false,
          'width' => $parcel->width,
          'height' => $parcel->height,
          'length' => $parcel->length,
          'weight' => $parcel->weight,
          'insurance_attributes' => [
            'ticket_amount' => ((int)$order->total - (int)$order->shipping_total),
            'ticket_number' => $order->id,
            'detail' => ltrim($productCategories),
            'extra' => $insuranceSetting->active && ((int)$order->total - (int)$order->shipping_total) > $insuranceSetting->amount,
          ],
          'address_attributes' => [
            'commune_id' => (int)filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT),
            'street' => $order->get_shipping_address_1(),
            'number' => '',
            'complement' => $order->get_shipping_address_2()
          ],
          'inventory_activity' => ['inventory_activity_orders_attributes' => $inventory]
        ];
        $processed_ids[] = $post_id;
      } else {
        $destiny = new Destiny(
          $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
          $order->get_billing_email(),
          $order->get_billing_phone(),
          ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1(),
          $address['number'],
          $order->get_shipping_address_2(),
          (int)filter_var($order->get_billing_state(), FILTER_SANITIZE_NUMBER_INT),
          $communeName,
          'home_delivery'
        );
        $seller = new Seller($order->id, $order->order_date, get_site_url(), $order->status);
        $courier = new Courier($order->get_shipping_method(), $order->get_shipping_method(), $opitSetting->algorithm, $opitSetting->algorithm_days, false);
        $price = new Price((int)$order->shipping_total, (int)$order->shipping_total, 0, (int)$order->cart_tax, 0);
        $payment = new Payment((int)$order->total, 0, 0, '', 0, 0, '', false);
        $measure = new Measure($parcel->width, $parcel->length, $parcel->height, $parcel->weight);
        $insurance = new Insurance(
          ((int)$order->total - (int)$order->shipping_total),
          $order->id,
          ltrim($productCategories),
          ($insuranceSetting->active && (((int)$order->total - (int)$order->shipping_total) > $insuranceSetting->amount))
        );

        $order_payload = new Order($company->id, '#'.$post_id, $order->get_item_count(), $company->service->name, false, 1, $destiny->getDestiny(), $seller->getSeller(), $inventory, $courier->getCourier(), $price->getPrice(), $payment->getPayment(), $measure->getMeasure(), $insurance->getInsurance());

        $request_payload[] = $order_payload->build();
        $processed_ids[] = $post_id;
      }
    }

    if ($company->platform_version == 2) {
      $core_cli = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v2');
      $response = $core_cli->massivePackages(['packages' => $request_payload]);
    } else {
      $response = $integration->massiveOrders(['orders' => $request_payload]);
    }

    foreach ($processed_ids as $key => $order_id) {
      $order = new WC_Order($order_id);
      $order->add_order_note('Se ha enviado a Shipit correctamente mediante acción masiva'); 
    }

    return $redirect_to = add_query_arg(array(
      'send_orders' => '1',
      'processed_count' => count($processed_ids),
      'processed_ids' => implode(',', $processed_ids),
    ), $redirect_to);
  }

  add_action('admin_notices', 'shipit_bulk_action_admin_notice');
  function shipit_bulk_action_admin_notice() {
    if (empty($_REQUEST['send_orders'])) return;

    $count = intval($_REQUEST['processed_count']);
    $class = 'notice notice-success is-dismissible';
    $message = __('Se enviaron a Shipit '.$count.' ordenes.', 'sample-text-domain');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message)); 
  }
?>