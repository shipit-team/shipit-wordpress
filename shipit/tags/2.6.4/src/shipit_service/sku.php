<?php
  class Sku {
    public $id = '';
    public $amount = '';
    public $description = '';
    public $warehouse_id = '';

    public function __construct($id, $amount, $description, $warehouse_id) {
      $this->id = $id;
      $this->amount = $amount;
      $this->description = $description;
      $this->warehouse_id = $warehouse_id;
    }

    function get_id() {
      return $this->id;
    }
    function get_amount() {
      return $this->amount;
    }
    function get_description() {
      return $this->description;
    }
    function get_warehouse_id() {
      return $this->warehouse_id;
    }

  }
?>