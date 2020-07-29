<?php
  class Measure {
    public $width = 10.0;
    public $length = 10.0;
    public $height = 10.0;
    public $quantity = 1;
    public $weight = 1.0;

    public function __construct($width, $length, $height, $weight, $quantity = 1) {
      $this->width = $width;
      $this->length = $length;
      $this->height = $height;
      $this->weight = $weight;
      $this->quantity = $quantity;
    }

    function getMeasure() {
      return array(
        'width' => $this->getWidth(),
        'height' => $this->getHeight(),
        'length' => $this->getLength(),
        'weight' => $this->getWeight(),
        'volumetric_weight' => $this->getVolumetricWeight()
      );
    }

    function buildBoxifyRequest() {
      return array(
        'width' => $this->getWidth(),
        'height' => $this->getHeight(),
        'length' => $this->getLength(),
        'weight' => $this->getWeight(),
        'quantity' => $this->getQuantity()
      );
    }

    function getWidth() {
      return $this->width;
    }

    function getLength() {
      return $this->length;
    }

    function getHeight() {
      return $this->height;
    }

    function getWeight() {
      return $this->weight;
    }

    function getQuantity() {
      return $this->quantity;
    }

    function getVolumetricWeight() {
      return ($this->getWidth() * $this->getHeight() * $this->getLength()) / 4000;
    }
  }
?>