<?php
/*
Plugin Name: Shipit
Description: Shipit Calculator Shipping couriers
Version:     3.0.15
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
require_once dirname(__FILE__) . '/src/class.settings-api.php';
require_once dirname(__FILE__) . '/src/shipit_service/http_client.php';
require_once dirname(__FILE__) . '/src/shipit_service/core.php';
require_once dirname(__FILE__) . '/src/shipit_service/opit.php';
require_once dirname(__FILE__) . '/src/shipit_service/integration.php';
require_once dirname(__FILE__) . '/src/shipit_service/order.php';
require_once dirname(__FILE__) . '/src/shipit_service/boxify.php';
require_once dirname(__FILE__) . '/src/shipit_service/address.php';
require_once dirname(__FILE__) . '/src/shipit_service/destiny.php';
require_once dirname(__FILE__) . '/src/shipit_service/courier.php';
require_once dirname(__FILE__) . '/src/shipit_service/price.php';
require_once dirname(__FILE__) . '/src/shipit_service/seller.php';
require_once dirname(__FILE__) . '/src/shipit_service/measure.php';
require_once dirname(__FILE__) . '/src/shipit_service/measure_collection.php';
require_once dirname(__FILE__) . '/src/shipit_service/payment.php';
require_once dirname(__FILE__) . '/src/shipit_service/insurance.php';
require_once dirname(__FILE__) . '/src/shipit_service/rate.php';
require_once dirname(__FILE__) . '/src/shipit_service/woocommerce_setting_helper.php';
require_once dirname(__FILE__) . '/src/shipit-settings.php';
require_once dirname(__FILE__) . '/src/webhook.php';
require_once dirname(__FILE__) . '/src/auther.php';
require_once dirname(__FILE__) . '/src/bulk_actions.php';

new Shipit_Settings_Admin();

defined('ABSPATH') or die("Bye bye");
function shipit_script_load() {
  wp_enqueue_script('shipitjavascript', plugin_dir_url(__FILE__) . 'src/js/javascript.js', array('jquery'));
  wp_register_style('custom_wp_admin_css', plugin_dir_url(__FILE__) . 'src/css/style_shipit.css', false, '1.0.0');
  wp_enqueue_style('custom_wp_admin_css');
}
add_action('wp_head', 'shipit_script_load', 0);

function shipit_house_add_checkout_fields($fields) {
  $fields['billing_phone'] = array(
    'label' => __('Teléfono'),
    'type' => 'text',
    'class' => array('form-row-wide'),
    'placeholder' => __('+569 --------'),
    'priority' => 35,
    'required' => true
  );
  return $fields;
}

add_filter('woocommerce_billing_fields', 'shipit_house_add_checkout_fields');

function activateShipit() {
  add_option('shipit_user', '', '');
  add_option('shipit_token', '', '');
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  global $wpdb;
  $wpdb->hide_errors();
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}shipit (
          id bigint(20) NOT NULL AUTO_INCREMENT,
          package varchar(1000) NOT NULL,
          created_at datetime NOT NULL,
          PRIMARY KEY (id)) $charset_collate;";
  dbDelta($sql);
  $password = hash_password(get_bloginfo('name') . '123');
  $userdata = array(
    'user_login' => get_bloginfo('name').'_shipit',
    'nickname' => 'Shipit',
    'user_email' => 'hola@shipit.cl',
    'user_url' => get_site_url(),
    'user_pass' => $password
  );
  $user_id = wp_insert_user($userdata);
  $user_shipit_table = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}user_shipit (
                        id bigint(20) NOT NULL AUTO_INCREMENT,
                        temp varchar(1000) NOT NULL,
                        created_at datetime NOT NULL,
                        PRIMARY KEY (id)) $charset_collate;";
  dbDelta($user_shipit_table);
  $insert_user = "INSERT INTO {$wpdb->prefix}user_shipit (temp, created_at)
                  VALUES('".base64_encode(get_bloginfo('name')."_shipit" . ':' . $password)."', NOW());";
  dbDelta($insert_user);
  // here continue to sync fulfillment skus
  // shipitSyncSkus('hola@shipit.cl', $password);
  shipitUpgradeSubscriberToShopManager($user_id);
}

register_activation_hook(__FILE__, 'activateShipit');

function shipitUpgradeSubscriberToShopManager($user_id) {
  $user = new WP_User($user_id);
  if (in_array('subscriber', $user->roles)) {
    $user->set_role('shop_manager');
  }
}

function hash_password($password) {
  global $wp_hasher;
  if (empty($wp_hasher)) {
    require_once(ABSPATH . WPINC . '/class-phpass.php');
    $wp_hasher = new PasswordHash(8, true);
  }
  return $wp_hasher->HashPassword(trim($password));
}

add_action('woocommerce_thankyou', 'dispatchToShipit', 10, 1);
function dispatchToShipit($orderId) {
  if (!$orderId) return;

  if(!get_post_meta($orderId, '_thankyou_action_done', true)) {
    $order = wc_get_order($orderId);
    $destinyId = (int)filter_var($order->get_shipping_state(), FILTER_SANITIZE_NUMBER_INT);
    if ($order->status != 'cancelled' && $order->status != 'failed' && $order->status != 'on-hold' && $order->status != 'refunded' && $order->status != 'pending' && $order->status != 'pending payment') {
      $core = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v4');
      $integration = new Integration(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
      $company = $core->administrative();
      $skus = array();
      if ($company->service->name == 'fulfillment') {
        $skus = $core->skus();
      }
      $insuranceSetting = $core->insurance();
      $sellerSetting = $integration->setting();
      $request = createShipment($company, $skus, $sellerSetting, $insuranceSetting, $order, $destinyId);

      if ($request) {
        $order = new WC_Order($orderId);
        $order->add_order_note('El pedido se ha enviado a Shipit correctamente');
      } else {
        $order = new WC_Order($orderId);
        $order->add_order_note('El pedido no pudo ser enviado a Shipit');
      }
    } else {
      $order = new WC_Order($orderId);
      $order->add_order_note('No pudo ser enviado a Shipit porque el pedido todavía no está confirmado o está fallido');
    }
    $order->update_meta_data('_thankyou_action_done', true);
    $order->save();
  }
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
  function shipitMethod() {
    if (!class_exists('Shipit_Shipping')) {
      class Shipit_Shipping extends WC_Shipping_Method {
        public function __construct() {
          $this->id = 'shipit';
          $this->method_title = __('Shipit');
          $this->method_description = __('Shipit Cotizador');
          $this->countries = array('CL');
          $this->init();
          $Shipit_Shipping = $this;
        }

        function init() {
          $this->init_form_fields();
          $this->init_settings();
          add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        function init_form_fields() {
          $this->form_fields = array(
            'enabled' => array(
              'title' => __('Activar', 'dc_raq'),
              'type' => 'checkbox',
              'description' => __('Activar el metodo de envío Shipit', 'dc_raq'),
              'default' => 'yes',
            ),
            'time_despach' => array(
              'title'  => __('Tiempo de entrega', 'dc_raq'),
              'type' => 'checkbox',
              'description' => __('Mostrar el tiempo de envío de Shipit', 'dc_raq'),
              'default' => 'yes'
            ),
            'type_packing' => array(
              'title' => 'Tipo empaque',
              'description' => 'Elige el tipo de empaque que tendría tu envio',
              'type' => 'select',
              'class' => 'wc-enhanced-select',
              'options' => array(
                'Sin empaque' => 'Sin empaque',
                'Caja de cartón' => 'Caja de cartón',
                'Film plástico' => 'Film plástico',
                'Caja + Burbuja' => 'Caja + Burbuja',
                'Papel kraft' => 'Papel kraft',
                'Bolsa Courier + Burbuja' => 'Bolsa Courier + Burbuja',
                'Bolsa Courier' => 'Bolsa Courier'
              )
            ),
            'packing_set' => array(
              'title' => 'Establecer dimensiones del producto',
              'description' => 'Configure una dimensión predefinida para sus productos al momento de la cotización. Deje en blanco o &quot;0&quot; para omitir.',
              'type' => 'select',
              'class' => 'wc-enhanced-select',
              'default' => 'Sí, cuando falten las dimensiones del producto o no estén configuradas',
              'options' => array(
                '2' => 'Sí, cuando falten las dimensiones del producto o no estén configuradas',
                '1' => 'Sí, utilizar siempre las dimensiones especificadas',
                '3' => 'Sí, cuando las dimensiones del producto sean menores que las especificadas',
                '4' => 'Sí, cuando las dimensiones del producto sean mayores que las especificadas',
              )
            ),
            'width' => array(
              'title' => __('Ancho', 'woocommerce'),
              'type' => 'number',
              'description' => __('CM.', 'woocommerce'),
              'css'      => 'max-width:150px;',
              'default' => __('10', 'woocommerce')
            ),
            'height' => array(
              'title' => __('Alto', 'woocommerce'),
              'type' => 'number',
              'description' => __('CM.', 'woocommerce'),
              'css'      => 'max-width:150px;',
              'default' => __('10', 'woocommerce')
            ),
            'length' => array(
              'title' => __('Largo', 'woocommerce'),
              'type' => 'number',
              'description' => __('CM.', 'woocommerce'),
              'css'      => 'max-width:150px;',
              'default' => __('10', 'woocommerce')
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
                '4' => 'Sí, cuando el peso del producto sea mayor que el especificado'
              )
            ),
            'weight' => array(
              'title' => __('Peso', 'woocommerce'),
              'type' => 'number',
              'description' => __('KG.', 'woocommerce'),
              'css'      => 'max-width:150px;',
              'default' => __('1', 'woocommerce'),
            ),
            'calculate_shiping' => array(
              'title' => 'Configuración de envíos',
              'description' => 'Configure un valor de envío.',
              'type' => 'select',
              'class' => 'wc-enhanced-select',
              'options' => array(
                '0' => 'Mostrar couriers disponibles',
                '1' => 'Mostrar el mejor valor por defecto',
              )
            ),
            'active-setup-price' => array(
              'title' => __('Activar precio definido', 'dc_raq'),
              'type' => 'checkbox',
              'description' => __('Activar precio preconfigurado', 'dc_raq'),
              'default' => 'yes'
            ),
            'communes' => array(
              'title' => __('Comunas especificas', 'woocommerce'),
              'type' => 'multiselect',
              'description' => 'Configure comunas para valor detallado.',
              'class' => 'wc-enhanced-select',
              'options' => WC()->countries->get_states('CL'),
              'custom_attributes' => array(
                'data-placeholder' => __('Seleccione comunas', 'woocommerce'),
              )
            ),
            'price-setup' => array(
              'title' => __('Subvencionar precio de envios ', 'woocommerce'),
              'type' => 'number',
              'description' => __('Configure su valor de las comunas por %. "100% = Gratis"', 'woocommerce'),
              'css' => 'max-width:200px;',
            ),
            'free_communes' => array(
              'title' => __('Comunas especificas', 'woocommerce'),
              'type' => 'multiselect',
              'description' => 'Configure comunas con despacho gratis.',
              'class' => 'wc-enhanced-select',
              'options' => WC()->countries->get_states('CL'),
              'custom_attributes' => array(
                'data-placeholder' => __('Seleccione comunas', 'woocommerce'),
              ),
            ),
            'price' => array(
              'title' => __('Envios gratis a partir:', 'woocommerce'),
              'type' => 'number',
              'description' => __('Configure el valor de minimo de orden para despachos.', 'woocommerce'),
              'css' => 'max-width:200px;',
            ),
            'free_communes_for_price' => array(
              'title' => __('Comunas con despacho gratis segun valor:', 'woocommerce'),
              'type' => 'multiselect',
              'description' => 'Configure comunas con despacho gratis si el valor del producto es mayor.',
              'class' => 'wc-enhanced-select',
              'options' => WC()->countries->get_states('CL'),
              'custom_attributes' => array(
                'data-placeholder' => __('Seleccione comunas', 'woocommerce'),
              ),
            ),
          );
        }
        // HERE WE CALCULATE SHIPPING PRICE AND SETUP CARRIERS AVAILABLES
        public function calculate_shipping($package = array()) {
          if (WC()->cart->get_cart_contents_count() === 0) return;

          $destinyId = (int)filter_var($package["destination"]['state'], FILTER_SANITIZE_NUMBER_INT);
          $integration = new Integration(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
          $opit = new Opit(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
          $sellerSetting = $integration->setting();
          $prices = array();
          $rate = new Rate(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
          if ($sellerSetting->show_shipit_checkout === true && $destinyId != null) {
            $rate->setParcel(getMeasures());
            $rate->setDestinyId($destinyId);
            $rate->setMultiCourierEnabled((bool)$opit->setting()->courier_prices_v3_enabled);
            $prices = $rate->calculate();
          }
          $prices = count($prices) > 0 ? $prices : array();
          if (is_object($prices) && $prices->state == 'error' || !$prices) {
          } else {
            global $shows;
            $shows = new Shipit_Shipping();
            $freeShipmentByTotalOrderPrice = ((int)WC()->cart->get_subtotal() > (int)$shows->settings['price']);
            // VARIABLE TO DEFINE IF SELLER SHOW CARRIER PRICE
            $showCarrierPrice = $shows->settings['calculate_shiping'];
            // VARIABLE TO DEFINE PRICE TO SPECIFIC DESTINIES
            $specificDestinyPrice = ($shows->settings['communes'] != '') ? in_array('CL'.strval($destinyId), $shows->settings['communes'], TRUE) : 0;
            // VARIABLE TO DEFINE FREE SHIPMENT BY SPECIFIC DESTINIES OR PRICE BY DESTINY OR TOTAL ORDER PRICE
            $freeShipment = $rate->getFreeShipment($freeShipmentByTotalOrderPrice, $shows->settings);
            if (is_array($prices) || is_object($prices)) {
              $i = 0;
              foreach ($prices as $price) {
                // REVIEW: THIS LINE MAYBE IS NOT CORRECT
                if (($showCarrierPrice == 1 || $freeShipment) && (integer)$i > 0) break;
                // DEFINE CUSTOM OR DEFAULT RATE DESCRIPTION
                $rateDescription = $rate->getRateDescription($sellerSetting->checkout, $price->days, $shows->settings);
                // DEFINE CUSTOM OR DEFAULT CARRIER
                $carrierName = (($showCarrierPrice == 1 && (integer)$i === 0) || $freeShipment) ? 'Shipit' : $price->courier->name;
                // DEFINE CUSTOM OR DEFAULT PRICE
                $priceToDisplay = $rate->getRate($this->id.'-'.$i, $price, $carrierName, $rateDescription, $specificDestinyPrice, $freeShipment, $shows->settings);
                // SET RATE TO SHOW ON USER INTERFACE
                $i++;
                $this->add_rate($priceToDisplay);
              }
            }
          }
        }
      }
    }
  }

  function addShipitMethod($methods) {
    $methods[] = 'Shipit_Shipping';
    return $methods;
  }

  add_filter('woocommerce_shipping_methods', 'addShipitMethod');

  function setCourierImage($label, $methods) {
    $shipping = $methods->get_method_id();
    if ($shipping == 'shipit') {
      $label = (number_format($methods->get_cost()) == 0) ? '<img style="display:inline;max-width: 75px;vertical-align: middle;" class="shipit_icon" id="img" src="'.plugin_dir_url(__FILE__) . 'src/images/'.$methods->get_label().'.png"> <span class="woocommerce-Price-amount amount"> GRATIS</span><br><span class="text-mute">'.$methods->meta_data[0].'</span>' : '<img style="display:inline;max-width: 75px;vertical-align: middle;" class="shipit_icon" id="img" src="'.plugin_dir_url(__FILE__) . 'src/images/'.$methods->get_label().'.png"> <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">&#36;</span>'.number_format($methods->get_cost()).'</span><br><span class="text-mute">'.$methods->meta_data[0].'</span>';
    }
    return $label;
  }
  add_action('woocommerce_shipping_init', 'shipitMethod');
  add_filter('woocommerce_cart_shipping_method_full_label', 'setCourierImage', 10, 2);

  function getProduct($cartItem) {
    return $cartItem['variation_id'] != '' && isset($cartItem['variation_id']) ? wc_get_product($cartItem['variation_id']) : wc_get_product($cartItem['product_id']);
  }
  // shipit_cURL_wrapper
  function getMeasures($height = 0, $width = 0, $length = 0, $weight = 0) {
    $measuresCollection = new MeasureCollection();
    $helper = new WoocommerceSettingHelper(get_option('woocommerce_weight_unit'), get_option('woocommerce_dimension_unit'));
    $cart = WC()->cart->get_cart();
    $count = WC()->cart->get_cart_contents_count();
    $forms = new Shipit_Shipping();

    $measureConversion = $helper->getMeasureConversion();
    $weightConversion = $helper->getWeightConversion();
    foreach ($cart as $cartItem) {
      $product = getProduct($cartItem);
      $height = $helper->packingSetting($product->get_height(), $forms->settings, 'height', 'packing_set', $measureConversion);
      $width = $helper->packingSetting($product->get_width(), $forms->settings, 'width', 'packing_set', $measureConversion);
      $length = $helper->packingSetting($product->get_length(), $forms->settings, 'length', 'packing_set', $measureConversion);
      $weight = $helper->packingSetting($product->get_weight(), $forms->settings, 'weight', 'weight_set', $weightConversion);

      $measure = new Measure((float)$height, (float)$width, (float)$length, (float)$weight, (int)$cartItem['quantity']);
      $measuresCollection->setMeasures($measure->buildBoxifyRequest());
    }
    return $measuresCollection->calculate();
  }

  // shipit_cURL_wrapper_request
  function createShipment($company, $skus, $sellerSetting, $insuranceSetting, $order, $destinyId = null) {
    $measuresCollection = new MeasureCollection();
    $helper = new WoocommerceSettingHelper(get_option('woocommerce_weight_unit'), get_option('woocommerce_dimension_unit'));
    $measureConversion = $helper->getMeasureConversion();
    $weightConversion = $helper->getWeightConversion();

    $country = $order->get_shipping_country();
    $state = $order->get_shipping_state();
    $communeName = WC()->countries->get_states($country)[$state];
    $paid = $order->is_paid() ? __('yes') : __('no');
    $forms = new Shipit_Shipping();

    $inventory = array();
    $productCategories = "";

    foreach ($order->get_items() as $cartItem) {
      $product = getProduct($cartItem);
      # here iterate and insert skus from shipit
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
          $measure = new Measure((float)$skuObject['height'], (float)$skuObject['width'], (float)$skuObject['length'], (float)$skuObject['weight'], (int)$cartItem['qty']);
          $measuresCollection->setMeasures($measure->buildBoxifyRequest());
        }
      } else {
        $height = $helper->packingSetting($product->get_height(), $forms->settings, 'height', 'packing_set', $measureConversion);
        $width = $helper->packingSetting($product->get_width(), $forms->settings, 'width', 'packing_set', $measureConversion);
        $length = $helper->packingSetting($product->get_length(), $forms->settings, 'length', 'packing_set', $measureConversion);
        $weight = $helper->packingSetting($product->get_weight(), $forms->settings, 'weight', 'weight_set', $weightConversion);

        $measure = new Measure((float)$height, (float)$width, (float)$length, (float)$weight, (int)$cartItem['quantity']);
        $measuresCollection->setMeasures($measure->buildBoxifyRequest());
      }
    }

    foreach ($order->get_items('shipping') as $shipping_id => $shipping_item_obj) {
      $shipping_item_data = $shipping_item_obj->get_data()['method_id'];
    }
    $shipit = $shipping_item_data == 'shipit' ? false : true;

    $testStreets = array();
    $testStreets[] = $order->get_shipping_address_1();
    for ($i = 0, $totalTestStreets = count($testStreets); $i < $totalTestStreets; $i++) {
      $address = split_street($testStreets[$i]);
    }
    $parcel = $measuresCollection->calculate();
    if ($company->platform_version == 2) {
      $prefix = '';
      $core = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v2');
      $core_payload = [
        'mongo_order_seller' => 'woocommerce',
        'seller_order_id' => $order->id,
        'reference' => '#'.$order->id,
        'full_name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
        'email' => $order->get_billing_email(),
        'items_count' => $order->get_item_count(),
        'cellphone' => $order->get_billing_phone(),
        'is_payable' => false,
        'packing' => 'Sin empaque',
        'shipping_type' => 'Normal',
        'destiny' => 'Domicilio',
        'width' => $parcel->width,
        'height' => $parcel->height,
        'length' => $parcel->length,
        'weight' => $parcel->weight,
        'courier_for_client' => $order->get_shipping_method(),
        'sent' => $shipit,
        'insurance_attributes' => [
          'ticket_amount' => ((int)$order->total - (int)$order->shipping_total),
          'ticket_number' => $order->id,
          'detail' => ltrim($productCategories),
          'extra' => $insuranceSetting->active && $request_params['order']['insurance']['ticket_amount'] > $insuranceSetting->amount,
        ],
        'address_attributes' => [
          'commune_id' => $destinyId,
          'street' => ($address['street'] != '') ? $address['street'] : $order->get_shipping_address_1(),
          'number' => $address['number'],
          'complement' => $order->get_shipping_address_2()
        ],
        'inventory_activity' => ['inventory_activity_orders_attributes' => $inventory]
      ];
      // MAKE API CALL
      return $sellerSetting->automatic_delivery === false ? $core->orders(['order' => $core_payload]) : $core->packages(['package' => $core_payload]);
    } else {
      $opit = new Opit(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
      $opitSetting = $opit->setting();

      $destiny = new Destiny(
        $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
        $order->get_billing_phone(),
        $order->get_billing_email(),
        $address['street'] != '' ? $address['street'] : $order->get_shipping_address_1(),
        $address['number'],
        (isset($address['numberAddition']) && $address['numberAddition'] != '') ? $address['numberAddition'].'/'.$order->get_shipping_address_2() : $order->get_shipping_address_2(),
        $destinyId,
        $communeName,
        ($shipit ? 'shopping_retired' : 'home_delivery')
      );
      $seller = new Seller($order->id, $order->order_date, get_site_url(), $order->status);
      $courier = new Courier($order->get_shipping_method(), $order->get_shipping_method(), $opitSetting->algorithm, $opitSetting->algorithm_days, false);
      $price = new Price((int)$order->shipping_total, (int)$order->shipping_total, 0, (int)$order->cart_tax, 0);
      // $products_collection = new ProductsCollection($inventory);
      $payment = new Payment((int)$order->total, 0, 0, '', 0, 0, '', false);
      $measure = new Measure($parcel->height, $parcel->width, $parcel->length, $parcel->weight);
      $insurance = new Insurance(
        ((int)$order->total - (int)$order->shipping_total),
        $order->id,
        ltrim($productCategories),
        ($insuranceSetting->active && (((int)$order->total - (int)$order->shipping_total) > $insuranceSetting->amount))
      );
      $orderPayload = new Order(
        $company->id,
        '#'.$order->id,
        $order->get_item_count(),
        $company->service->name,
        false,
        ($shipit ? 2 : 1),
        $destiny->getDestiny(),
        $seller->getSeller(),
        $inventory,
        $courier->getCourier(),
        $price->getPrice(),
        $payment->getPayment(),
        $measure->getMeasure(),
        $insurance->getInsurance());
      // MAKE API CALL
      if ($sellerSetting->automatic_delivery === false) {
        $integration = new Integration(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token']);
        return $integration->massiveOrders(['orders' => [$orderPayload->build()]]);
      } else {
        $core = new Core(get_option('shipit_user')['shipit_user'], get_option('shipit_user')['shipit_token'], 'v4');
        return $core->shipments(['shipment' => $orderPayload->build()]);
      }
    }
  }
}

function split_street($streetStr) {
  $aMatch = array();
  $pattern = '/([a-z]|[!"$%&()=#,.])\s*\d{1,5}/i';
  preg_match($pattern, $streetStr, $aMatch);
  $number = preg_replace('/\D/', '', $aMatch[0]);

  $splitedAddress = explode($number, $streetStr);

  $street = ltrim(preg_replace('/[#$%-]/', '', $splitedAddress[0]));

  $numberAddition = sizeof($splitedAddress) > 1 ? $splitedAddress[1] : "";
  return array('street' => $street, 'number' => $number, 'numberAddition' => $numberAddition);
}
?>