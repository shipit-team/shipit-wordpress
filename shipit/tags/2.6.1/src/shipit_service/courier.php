<?php
  class Courier {
    public $client = '';
    public $entity = '';
    public $payable = false;
    public $shipment_type = 'normal';
    public $selected = false;
    public $algorithm = '1';
    public $algorithm_days = '0';
    public $delivery_time = '';

    public function __construct($client, $entity, $payable, $shipment_type, $tracking, $selected, $algorithm, $algorithm_days, $delivery_time) {
      $this->client = $client;
      $this->entity = $entity;
      $this->payable = $payable;
      $this->shipment_type = $shipment_type;
      $this->selected = $selected;
      $this->algorithm = $algorithm;
      $this->algorithm_days = $algorithm_days;
      $this->delivery_time = $delivery_time;
    }

    function get_client() {
      return $this->client;
    }
    function get_entity() {
      return $this->entity;
    }
    function get_payable() {
      return $this->payable;
    }
    function get_shipment_type() {
      return $this->shipment_type;
    }
    function get_algorithm() {
      return $this->algorithm;
    }
    function get_algorithm_days() {
      return $this->algorithm_days;
    }
    function get_delivery_time() {
      return $this->delivery_time;
    }
  }
?>