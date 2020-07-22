<?php
  class Address {
    public $street = '';
    public $number = '';
    public $complement = '';
    public $commune_id = '';
    public $commune_name = '';
    public $latitude = 0.0;
    public $longitude = 0.0;

    public function __construct($street, $number, $complement, $commune_id, $commune_name = '', $latitude = 0.0, $longitude = 0.0) {
      $this->street = $street;
      $this->number = $number;
      $this->complement = $complement;
      $this->commune_id = $commune_id;
      $this->latitude = $latitude;
      $this->longitude = $longitude;
    }

    function getStreet() {
      return $this->street;
    }

    function getNumber() {
      return $this->number;
    }

    function getComplement() {
      return $this->complement;
    }

    function getCommuneId() {
      return $this->commune_id;
    }

    function getCommuneName() {
      return $this->commune_name;
    }

    function getLatitude() {
      return $this->latitude;
    }

    function getLongitude() {
      return $this->longitude;
    }
  }
?>