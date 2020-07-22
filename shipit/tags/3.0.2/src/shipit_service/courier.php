<?php
  class Courier {
    public $client = '';
    public $entity = '';
    public $payable = false;
    public $algorithm = '1';
    public $algorithm_days = '0';

    public function __construct($client, $entity, $algorithm, $algorithm_days, $payable = false) {
      $this->client = $client;
      $this->entity = $entity;
      $this->payable = $payable;
      $this->algorithm = $algorithm;
      $this->algorithm_days = $algorithm_days;
    }

    function getCourier() {
      return array(
        'client' => $this->getClient(),
        'entity' => $this->getEntity(),
        'selected' => $this->getClient() != '',
        'payable' => $this->getPayable(),
        'shipment_type' => 'Normal',
        'algorithm' => $this->getAlgorithm(),
        'algorithm_days' => $this->getAlgorithmDays()
      );
    }

    function getClient() {
      return $this->client;
    }

    function getEntity() {
      return $this->entity;
    }

    function getPayable() {
      return $this->payable;
    }

    function getAlgorithm() {
      return $this->algorithm;
    }

    function getAlgorithmDays() {
      return $this->algorithm_days;
    }
  }
?>