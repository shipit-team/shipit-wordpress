<?php
  class Address {
    public $street = '';
    public $commune_id = '';
    public $complement = '';
    public $number = '';
    public $latitude = 0.0;
    public $longitude = 0.0;
    
    public function __construct($street, $number, $complement, $commune_id, $latitude = 0.0, $longitude = 0.0) {
      $this->street = $street;
      $this->number = $number;
      $this->complement = $complement;
      $this->commune_id = $commune_id;
      $this->latitude = $latitude;
      $this->longitude = $longitude;
    }


    function get_street() {
      return $this->street;
    }

    function get_number() {
      return $this->number;
    }

    function get_complement() {
      return $this->complement;
    }

    function get_commune_id() {
      return $this->commune_id;
    }

    function get_latitude() {
      return $this->latitude;
    }

    function get_longitude() {
      return $this->longitude;
    }

  }
?>