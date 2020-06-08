<?php
  class Product {
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

    function getProduct() {
      return array(
        'sku_id' => $this->getId(),
        'amount' => $this->getAmount(),
        'description' => $this->getDescription(),
        'warehouse_id' => $this->getWarehouseId(),
      );
    }

    function getId() {
      return $this->id;
    }
  
    function getAmount() {
      return $this->amount;
    }

    function getDescription() {
      return $this->description;
    }

    function getWarehouseId() {
      return $this->warehouse_id;
    }
  }
?>