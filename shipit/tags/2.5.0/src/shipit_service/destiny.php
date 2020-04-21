<?php
  include 'address.php';
  class Destiny {
    public $name = '';
    public $full_name = '';
    public $phone = '';
    public $email = '';
    public $address = null;
    public function __construct($name, $full_name, $phone, $email, $address_stret, $address_complement, $address_number, $address_commune_id) {
      $this->name = $name;
      $this->full_name = $full_name;
      $this->phone = $phone;
      $this->email = $email;
      $this->address = new Address($address_stret, $address_complement, $address_number, $address_commune_id);
    }

    function get_name() {
      return $this->name;
    }

    function get_full_name() {
      return $this->full_name;
    }

    function get_phone() {
      return $this->phone;
    }

    function get_email() {
      return $this->email;
    }

    function get_address() {
      return $this->address;
    }

  }

?>