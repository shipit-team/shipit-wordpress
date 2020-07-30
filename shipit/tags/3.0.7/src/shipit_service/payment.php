<?php
  class Payment {
    public $total = 0.0;
    public $discount = 0.0;
    public $tax = 0.0;
    public $type = '';
    public $subtotal = 0.0;
    public $currency = 'CLP';
    public $status = 'no_paid';
    public $confirmed = false;

    public function __construct($total, $discount, $tax, $type, $subtotal, $currency, $status, $confirmed) {
      $this->total = $total;
      $this->discount = $discount;
      $this->tax = $tax;
      $this->type = $type;
      $this->subtotal = $subtotal;
      $this->currency = $currency;
      $this->status = $status;
      $this->confirmed = $confirmed;
    }

    function getPayment() {
      return array(
        'total' => $this->getTotal(),
        'discount' => $this->getDiscount(),
        'tax' => $this->getTax(),
        'type' => $this->getType(),
        'sub_total' => $this->getSubtotal(),
        'status' => $this->getStatus(),
        'confirmed' => $this->getConfirmed()
      );
    }

    function getTotal() {
      return $this->total;
    }

    function getDiscount() {
      return $this->discount;
    }

    function getTax() {
      return $this->tax;
    }

    function getType() {
      return $this->type;
    }

    function getSubtotal() {
      return $this->subtotal;
    }

    function get_currency() {
      return $this->currency;
    }

    function getStatus() {
      return $this->status;
    }

    function getConfirmed() {
      return $this->confirmed;
    }
  }
?>