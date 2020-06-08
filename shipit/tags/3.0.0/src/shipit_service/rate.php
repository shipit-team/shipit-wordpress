
<?php
  class Rate {
    public $email = '';
    public $token = '';
    public $multiCourierEnabled = false;
    public $parcel = array();
    public $destinyId = 0;

    public function __construct($email, $token) {
      $this->base = 'https://api.shipit.cl/v';
      $this->email = $email;
      $this->token = $token;
      $this->headers = array( 
        'Accept' => 'application/vnd.shipit.v4',
        'Content-Type' => 'application/json',
        'X-Shipit-Email' => $email,
        'X-Shipit-Access-Token' => $token
      );
    }

    function calculate() {
      return $this->getMultiCourierEnabled() ? $this->rates() : $this->prices();
    }

    function prices() {
      $client = new HttpClient($this->base . '/rates', $this->headers);
      $response = $client->post($this->getParcel());
      $prices = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $prices = json_decode($response['body'])->prices;
      }
      return $prices;
    }

    function rates() {
      $client = new HttpClient($this->base . '/rates', $this->headers);
      $response = $client->post($this->getParcel());
      $prices = array();
      $data = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $prices = json_decode($response['body'])->prices;
        $couriers = array_unique(array_map(function($price) {
          return $price->original_courier;
        }, $prices));
        for ($index = 0; $index < count($couriers); $index++) {
          foreach ($prices as $price) {
            if ($price->original_courier == $couriers[$index]) {
              $couriers = array_slice($couriers, $index + 1);
              $data[] = $price;
              if (count($couriers) === 0) break;
            }
          }
        }
      }
      return $data;
    }

    function getDestinyId() {
      return $this->destinyId;
    }

    function setDestinyId($destinyId) {
      $this->destinyId = $destinyId;
    }

    function getMultiCourierEnabled() {
      return $this->multiCourierEnabled;
    }

    function setMultiCourierEnabled($multiCourierEnabled = false) {
      $this->multiCourierEnabled = $multiCourierEnabled;
    }


    function getParcel() {
      return array(
        'parcel' => array(
          'length' => $this->parcel->length,
          'width' => $this->parcel->width,
          'height' => $this->parcel->height,
          'weight' => $this->parcel->weight,
          'origin_id' => 308,
          'destiny_id' => $this->getDestinyId(),
          'type_of_destiny' => 'domicilio'
        )
      );
    }
  
    function setParcel($parcel = array()) {
      $this->parcel = $parcel;
    }

    function getRateDescription($checkout, $days, $woocommerceSetting) {
      if ($checkout->show_days && $woocommerceSetting['time_despach'] == 'yes') {
        $defaultMessage = ($days == 1 ? array('Tiempo de entrega aproximado: ' .$days. ' día.') : array('Tiempo de entrega aproximado: ' .$days. ' días.'));
        if ($checkout->custom_delivery_promise->active === false) {
          return $defaultMessage;
        } elseif ((int)$checkout->custom_delivery_promise->type == 1) {
          return array(" ");  
        } elseif ((int)$checkout->custom_delivery_promise->type == 2) {
          return !empty($checkout->custom_delivery_promise->custom_message) ? array($checkout->custom_delivery_promise->custom_message) : array('Despacho a domicilio');
        } else {
          $minDaysPlus = ((int)$checkout->custom_delivery_promise->min_days_plus);
          $maxDaysPlus = ((int)$checkout->custom_delivery_promise->max_days_plus);
          if ($minDaysPlus == 0 && $maxDaysPlus == 0) {
            return $defaultMessage; 
          } else {
            $minDaysModified = $days + $minDaysPlus;
            $maxDaysModified = $minDaysModified + $maxDaysPlus;
            return $maxDaysPlus == 0 ? array('Estimado '.$minDaysModified.' días hábiles.') : array('Tiempo estimado entre ' .$minDaysModified. ' y ' .$maxDaysModified. ' días hábiles.');
          }
        }
      } else {
        return array(" ");
      }
    }

    function getFreeShipment($freeShipmentByTotalOrderPrice, $woocommerceSetting) {
      $freeDestinies = $woocommerceSetting['free_communes'];
      $freeDestiniesByPrice = $woocommerceSetting['free_communes_for_price'];
      // RETURN NON FREE PRICE IF FREE DESTINIES OR FREE DESTINIES BY PRICES IS EMPTY
      if ($freeDestinies == '' && $freeDestiniesByPrice == '') return false;
      // RETURN FREE SHIPMENT PRICE BASED ON SPECIFIC COMMUNES OR TOTAL CART PRICE OR SPECIFIC COMMUNES WITH PRICE
      if ($freeDestinies != '') {
        return in_array('CL'.strval($this->getDestinyId()), $freeDestinies, TRUE);
      } elseif ($freeDestiniesByPrice != '' && $freeShipmentByTotalOrderPrice == true) {
        return in_array('CL'.strval($this->getDestinyId()), $freeDestiniesByPrice, TRUE);
      } else {
        return false;
      }
    }

    function getRate($id, $price, $carrierName, $rateDescription, $specificDestinyPrice, $freeShipment, $woocommerceSetting) {
      $rate = $price->price;
      if ($freeShipment) {
        $rate = 0;
      } elseif ($woocommerceSetting['active-setup-price'] == 'yes') {
        if ($specificDestinyPrice == true) {
          $rate = $price->price - (($price->price * $woocommerceSetting['price-setup']) / 100);
        }
      }

      return array(
        'id'    => $id,
        'label' => $carrierName,
        'cost'  => $rate,
        'meta_data' => $rateDescription
      );
    }
  }
?>