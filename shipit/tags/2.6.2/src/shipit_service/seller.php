<?php
  class Seller {
    public $id = '';
    public $name = 'woocommerce';
    public $referring_site = '';
    public $status = 'draft';

    public function __construct($id, $referring_site = '', $status = '', $name = 'woocommerce') {
      $this->id = $id;
      $this->name = $name;
      $this->referring_site = $referring_site;
      $this->status = $status;
    }

    function get_id() {
      return $this->id;
    }

    function get_name() {
      return $this->name;
    }

    function get_referring_site() {
      return $this->referring_site;
    }

    function get_status() {
      return $this->status;
    }
  }
?>