<?php
  class MeasureCollection {
    public $measures = array();

    public function __construct() {}

    function setMeasures($measure = array()) {
      array_push($this->measures, $measure);
    }

    function getMeasuresCollection() {
      return $this->measures;
    }

    function calculate() {
      $boxify = new Boxify();
      return $boxify->calculate(array('packages' => $this->getMeasuresCollection()));
    }
  }
?>