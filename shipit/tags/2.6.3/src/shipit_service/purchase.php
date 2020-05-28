<?php
  class Purchase {

    public $amount = '';
    public $ticket_number = '';
    public $extra = false;
    public $description = 10.0;
    public $active = false;

    public function __construct($amount, $ticket_number, $extra, $description, $active) {
      $this->amount = $amount;
      $this->ticket_number = $ticket_number;
      $this->extra = $extra;
      $this->description = $description;
      $this->active = $active;
    }

    function get_amount() {
      return $this->amount;
    }

    function get_ticket_number() {
      return $this->ticket_number;
    }

    function get_extra() {
      return $this->extra;
    }

    function get_description() {
      return $this->description;
    }

    function get_active() {
      return $this->active;
    }
  }

?>
