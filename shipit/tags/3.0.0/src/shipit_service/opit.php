<?php
  class Opit {
    public $email = '';
    public $token = '';
    public $headers = '';
    public $base = '';

    public function __construct($email, $token) {
      $this->base = 'https://api.shipit.cl/v';
      $this->email = $email;
      $this->token = $token;
      $this->headers = array( 
        'Content-Type' => 'application/json',
        'X-Shipit-Email' => $email,
        'X-Shipit-Access-Token' => $token,
        'Accept' => 'application/vnd.shipit.v4'
      );
    }

    function setting() {
      $client = new HttpClient($this->base . '/settings/1', $this->headers);
      $response = $client->get();
      $setting = array();

      if (is_wp_error($response)) {
        echo 'Error al conectar con API.';
      } else {
        $setting = json_decode($response['body'])->configuration->opit;
      }
      return $setting;
    }
  }
?>