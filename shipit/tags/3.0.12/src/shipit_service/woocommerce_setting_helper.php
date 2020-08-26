<?php
  class WoocommerceSettingHelper {
    public $weightUnit;
    public $measureUnit;

    public function __construct($weightUnit, $measureUnit) {
      $this->weightUnit = $weightUnit;
      $this->measureUnit = $measureUnit;
    }

    // DIVIDER FACTOR TO SET UNIT OF WEIGHT
    function getWeightConversion($unit = 1) {
      switch ($this->weightUnit) {
        case 'oz':
          $unit = 35.274;
          break;
        case 'lbs':
          $unit = 2.2046;
          break;
        case 'g':
          $unit = 1000;
          break;
        default:
          break;
      }
      return $unit;
    }

    // DIVIDER FACTOR TO SET UNIT OF MEASURE
    function getMeasureConversion($unit = 1) {
      switch ($this->measureUnit) {
        case 'mm':
          $unit = 10;
          break;
        case 'yd':
          $unit = 0.010936;
          break;
        case 'm':
          $unit = 0.01;
          break;
        case 'in':
          $unit = 0.39370;
          break;
        default:
          break;
      }
      return $unit;
    }

    function convert($value, $divider) {
      return $value / $divider;
    }

    function packingSetting($value, $settings, $valueName, $settingName, $unit = 1) {
      $data = 0;
      switch ($settings[$settingName]) {
        case 1:
          $data = $settings[$valueName];
          break;
        case 2:
          $data = in_array($value, array('', 0)) ? $settings[$valueName] : $this->convert($value, $unit);
          break;
        case 3:
        case 4:
          $tmp = $this->convert($value, $unit);
          if ($settings[$settingName] === 3) {
            $data = $tmp < $settings[$valueName] ? $settings[$valueName] : $tmp;
          } else {
            $data = $tmp > $settings[$valueName] ? $settings[$valueName] : $tmp;
          }
          break;
        default:
          $data = $this->convert($value, $unit);
          break;
      }
      return $data;
    }
  }
?>