<?php
  class ProductsCollection {
    public $collection = array();

    public function __construct($collection) {
      $this->collection = $collection;
    }

    function getProducts() {
      $products = array();
        foreach ($this->collection as $product) {
        array_push($products, [new Product($product->id, 
                                           $product->amount,
                                           $product->description,
                                           $product->warehouse_id)]);
      }
      return $products;
    }
  }
?>