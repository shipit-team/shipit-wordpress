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

    function get_total() {
      return $this->total;
    }

    function get_discount() {
      return $this->discount;
    }

    function get_tax() {
      return $this->tax;
    }

    function get_type() {
      return $this->type;
    }

    function get_subtotal() {
      return $this->subtotal;
    }

    function get_currency() {
      return $this->currency;
    }

    function get_status() {
      return $this->status;
    }

    function get_confirmed() {
      return $this->confirmed;
    }
  }

?>