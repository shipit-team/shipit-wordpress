<?php
  class Seller {
    public $id = '';
    public $name = 'woocommerce';
    public $referring_site = '';
    public $status = 'draft';
    public $created_at = '';

    public function __construct($id, $created_at = '', $referring_site = '', $status = '', $name = 'woocommerce') {
      $this->id = $id;
      $this->name = $name;
      $this->referring_site = $referring_site;
      $this->status = $status;
      $this->created_at= $created_at;
    }

    function getSeller() {
      return array(
        'id' => $this->getId(),
        'name' => $this->getName(),
        'reference_site' => $this->getReferringSite(),
        'created_at' => $this->getCreatedAt(),
        'status' => $this->getStatus()
      );
    }

    function getId() {
      return $this->id;
    }

    function getName() {
      return $this->name;
    }

    function getReferringSite() {
      return $this->referring_site;
    }

    function getStatus() {
      return $this->status;
    }

    function getCreatedAt() {
      return $this->created_at;
    }
  }
?>