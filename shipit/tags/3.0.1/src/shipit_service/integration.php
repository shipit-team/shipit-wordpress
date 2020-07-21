<?php
  class Integration {
    public $email = '';
    public $token = '';
    public $headers = '';
    public $base = '';
    
    public function __construct($email, $token) {
      $this->base = 'http://orders.shipit.cl/v';
      $this->email = $email;
      $this->token = $token;
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
      $response = $client->post($order);
      $order = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $order = json_decode($response['body']);
      }
      return $order;
    }

    function massiveOrders($order = array()) {
      $client = new HttpClient($this->base . '/orders/massive', $this->headers);
      $response = $client->post($order);
      $order = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $order = json_decode($response['body']);
      }
      return $order;
    }
  }
?>