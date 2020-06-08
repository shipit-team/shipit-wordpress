<?php
  class Order {
    public $company_id = '';
    public $reference = '';
    public $kind = 3;
    public $platform = 2;
    public $items = 0;
    public $service = '';
    public $sandbox = false;
    public $state = 0;
    public $destiny = array();
    public $seller = array();
    public $products = array();
    public $courier = array();
    public $prices = array();
    public $payment = array();
    public $sizes = array();
    public $insurance = array();

    public function __construct($company_id, $reference, $items, $service, $sandbox, $state, $destiny, $seller, $products, $courier, $prices, $payment, $sizes, $insurance) {
      $this->company_id = $company_id;
      $this->reference = $reference;
      $this->items = $items;
      $this->service = $service;
      $this->sandbox = $sandbox;
      $this->state = $state;
      $this->destiny = $destiny;
      $this->seller =  $seller;
      $this->products = $products;
      $this->courier = $courier;
      $this->prices = $prices;
      $this->payment = $payment;
      $this->sizes = $sizes;
      $this->insurance = $insurance;
    }
    
    function build() {
      return array(
        'company_id' => $this->getCompanyId(),
        'reference' => $this->getReference(),
        'items' => $this->getItems(),
        'service' => $this->getService(),
        'kind' => $this->getKind(),
        'platform' => 2,
        'sandbox' => $this->getSandBox(),
        'state' => $this->getState(),
        'destiny' => $this->getDestiny(),
        'seller' => $this->getSeller(),
        'products' => $this->getProducts(),
        'courier' => $this->getCourier(),
        'prices' => $this->getPrice(),
        'payment' => $this->getPayment(),
        'sizes' => $this->getMeasure(),
        'insurance' => $this->getInsurance()
      );
    }

    function getCompanyId() {
      return $this->company_id;
    }

    function getReference() {
      return $this->reference;
    }

    function getItems() {
      return $this->items;
    }

    function getService() {
      return $this->service;
    }

    function getSandBox() {
      return $this->sandbox;
    }

    function getState() {
      return $this->state;
    }

    function getDestiny() {
      return $this->destiny;
    }

    function getSeller() {
      return $this->seller;
    }

    function getProducts() {
      return $this->products;
    }

    function getCourier() {
      return $this->courier;
    }
  
    function getPrice() {
      return $this->prices;
    }
  
    function getPayment() {
      return $this->payment;
    }

    function getMeasure() {
      return $this->sizes;
    }

    function getInsurance() {
      return $this->insurance;
    }

    function getKind() {
      return $this->kind;
    }
  }
?>