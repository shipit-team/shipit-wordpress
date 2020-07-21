<?php
  class HttpClient {
    public $url = '';
    public $headers = array();
    
    public function __construct($url, $headers) {
      $this->url = $url;
      $this->headers = $headers;
    }

    function post($body = array()) {
      return wp_remote_post($this->url, array(
        'method' => 'POST',
        'timeout' => 60,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => $this->headers,
        'cookies' => array(),
        'body' => wp_json_encode($body)
      ));
    }

    function get() {
      return wp_remote_get($this->url, array(
        'method' => 'GET',
        'timeout' => 60,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'cookies' => array(),
        'headers' => $this->headers
      ));
    }

    function patch($body = array()) {
      return wp_remote_request($this->url, array(
        'method' => 'PATCH',
        'timeout' => 60,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'cookies' => array(),
        'headers' => $this->headers,
        'body' => wp_json_encode($body)
      ));
    }

    function put($body = array()) {
      return wp_remote_request($this->url, array(
        'method' => 'PUT',
        'timeout' => 60,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'cookies' => array(),
        'headers' => $this->headers,
        'body' => wp_json_encode($body)
      ));
    }
  }
?>