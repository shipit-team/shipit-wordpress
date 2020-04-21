<?php
  class Sizes {

    public $width = 10.0;
    public $length = 10.0;
    public $height = 10.0;
    public $quantity = 1;
    public $weight = 1.0;

    public function __construct($width, $length, $height, $weight, $quantity) {
      $this->width = $width;
      $this->length = $length;
      $this->height = $height;
      $this->weight = $weight;
      $this->quantity = $quantity;
    }

    function get_width() {
      return $this->width;
    }

    function get_length() {
      return $this->length;
    }

    function get_height() {
      return $this->height;
    }

    function get_weight() {
      return $this->weight;
    }

    function get_quantity() {
      return $this->quantity;
    }

    function set_sizes() {
      // here call boxify
    }
  }

?>
