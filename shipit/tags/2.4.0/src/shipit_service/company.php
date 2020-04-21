<?php
  class Company {
    public $id = '';
    public $name = '';
    public $platform_version = 2;
    public $cutting_hours = '';

    public function __construct($id, $name, $platform_version, $cutting_hours) {
      $this->id = $id;
      $this->name = $name;
      $this->platform_version = $platform_version;
      $this->cutting_hours = $cutting_hours;
    }

    function get_name() {
      return $this->name;
    }

    function get_platform_version() {
      return $this->platform_version;
    }

    function get_cutting_hours() {
      return $this->cutting_hours;
    }
  }

?>