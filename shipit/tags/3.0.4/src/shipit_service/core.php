<?php
  class Core {
    public $url = '';
    public $email = '';
    public $token = '';
    public $headers = '';
    public $base = '';

    public function __construct($email, $token, $version) {
      $this->base = 'http://api.shipit.cl/v';
      $this->email = $email;
      $this->token = $token;
      $this->headers = array(
        'Content-Type' => 'application/json',
        'X-Shipit-Email' => $email,
        'X-Shipit-Access-Token' => $token,
        'Accept' => 'application/vnd.shipit.' . $version
      );
    }

    function packages($package = array()) {
      $client = new HttpClient($this->base . '/packages', $this->headers);
      $response = $client->post($package);
      $package = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $package = json_decode($response['body']);
      }
      return $package;
    }

    function massivePackages($packages = array()) {
      $client = new HttpClient($this->base . '/packages/mass_create', $this->headers);
      $response = $client->post($packages);
      $package = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $package = json_decode($response['body']);
      }
      return $package;
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

    function shipments($shipment = array()) {
      $client = new HttpClient($this->base . '/shipments', $this->headers);
      $response = $client->post($shipment);
      $shipment = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $shipment = json_decode($response['body']);
      }
      return $shipment;
    }

    function administrative() {
      $client = new HttpClient($this->base . '/setup/administrative', $this->headers);
      $response = $client->get();
      $company = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $company = json_decode($response['body']);
      }
      return $company;
    }

    function communes($shipment = array()) {
      $client = new HttpClient($this->base . '/communes', $this->headers);
      $response = $client->get();
      $communes = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $communes = (array)json_decode($response['body'], true);
      }
      return $communes;
    }

    function skus() {
      $client = new HttpClient($this->base . '/fulfillment/skus/all', $this->headers);
      $response = $client->get();
      $skus = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $skus = (array) json_decode($response['body'], true);
      }
      return $skus;
    }

    function insurance() {
      $client = new HttpClient($this->base . '/settings/9', $this->headers);
      $response = $client->get();
      $setting = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $setting = json_decode($response['body'])->configuration->automatizations->insurance;
      }
      return $setting;
    }

    function setWebhook($webhook = array()) {
      $client = new HttpClient($this->base . '/integrations/webhook', $this->headers);
      $response = $client->patch($webhook);
      $webhook_response = array();
      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $webhook_response = json_decode($response['body']);
      }
      return $webhook_response;
    }
  }
?>