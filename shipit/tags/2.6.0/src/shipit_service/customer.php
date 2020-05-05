<?php
  class Customer {
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $phone = '';
    public $shipping_address = null;

    public function __construct($id, $first_name, $last_name, $email, $phone, $shipping_address) {
      $this->first_name = $first_name;
      $this->last_name = $last_name;
      $this->email = $email;
      $this->phone = $phone;
      $this->shipping_address = $shipping_address;
    }

    function get_first_name() {
      return $this->first_name;
    }

    function get_last_name() {
      return $this->last_name;
    }

    function get_email() {
      return $this->email;
    }

    function get_phone() {
      return $this->phone;
    }

    function get_shipping_address() {
      return $this->shipping_address;
    }
  }
?>