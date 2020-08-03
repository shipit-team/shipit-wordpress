<?php
  class Price {
    public $total = 0.0;
    public $price = 0.0;
    public $cost = 0.0;
    public $tax = 0.0;
    public $overcharge = 0.0;

    public function __construct($total, $price, $cost, $tax, $overcharge) {
      $this->total = $total;
      $this->price = $price;
      $this->cost = $cost;
      $this->tax = $tax;
      $this->overcharge = $overcharge;
    }

    function getPrice() {
      return array(
        'price' => $this->getTotal(),
        'tax' => $this->getTax(),
        'cost' => $this->getCost(),
        'overcharge' => $this->getOvercharge()
      );
    }

    function getTotal() {
      return $this->total;
    }

    function getShippingPrice() {
      return $this->price;
    }

    function getTax() {
      return $this->tax;
    }

    function getCost() {
      return $this->cost;
    }

    function getOvercharge() {
      return $this->overcharge;
    }
  }
?>