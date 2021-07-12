<?php
  class Integration {
    public $email = '';
    public $token = '';
    public $headers = '';
    public $base = '';
    public $bugsnag;

    public function __construct($email, $token) {
      $this->base = 'https://orders.shipit.cl/v';
      $this->email = $email;
      $this->token = $token;
      $this->bugsnag = new Bugsnag();
      $this->headers = array(
        'Content-Type' => 'application/json',
        'X-Shipit-Email' => $email,
        'X-Shipit-Access-Token' => $token,
        'Accept' => 'application/vnd.orders.v1'
      );
    }

    function setting() {
      $client = new HttpClient($this->base . '/integrations/seller/woocommerce', $this->headers);
      $response = $client->get();
      $setting = array();

      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $setting = json_decode($response['body'])->configuration;
      }
      return $setting;
    }

    function configure($setting = array()) {
      $client = new HttpClient($this->base . '/integrations/configure', $this->headers);
      $response = $client->put($setting);
      $setting = array();

      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $setting = json_decode($response['body']);
      }
      return $setting;
    }

    function orders($order = array()) {
      $client = new HttpClient($this->base . '/orders', $this->headers);
      $reference = $order['order']['reference'];
      $response = $client->post($order);
      $order = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
        $this->bugsnag->bugsnagWpLog($response, "orders services", $reference);
      } else {
        $order = json_decode($response['body']);
        $this->bugsnag->bugsnagLog($order, "orders services", $reference);
      }
      
      return $order;
    }

    function massiveOrders($order = array()) {
      $client = new HttpClient($this->base . '/orders/massive', $this->headers);
      $references = $this->bugsnag->getReferencesFromOrdersMassive($order);
      $response = $client->post($order);
      $order = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
        $this->bugsnag->bugsnagWpLog($response, "orders massive service", $references);
      } else {
        $order = json_decode($response['body']);
        $this->bugsnag->bugsnagLog($order, "orders massive service", $references);
      }   
      return $order;
    }
  }
?>
