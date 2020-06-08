<?php
  class Origin {
    public $name = '';
    public $full_name = '';
    public $phone = '';
    public $email = '';
    public $address = null;
    public $default = false;
    public $branch_office_id = 0;

    public function __construct($name, $full_name, $phone, $email, $address_stret, $address_complement, $address_number, $address_commune_id) {
      $this->name = $name;
      $this->full_name = $full_name;
      $this->phone = $phone;
      $this->email = $email;
      $this->address = new Address($address_stret, $address_complement, $address_number, $address_commune_id);
    }

    function getName() {
      return $this->name;
    }

    function getFullName() {
      return $this->full_name;
    }

    function getPhone() {
      return $this->phone;
    }

    function getEmail() {
      return $this->email;
    }

    function getAddress() {
      return $this->address;
    }
  }
?>