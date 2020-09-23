<?php
  class Destiny {
    public $full_name = '';
    public $phone = '';
    public $email = '';
    public $kind = 'home_delivery';
    public $address = array();
    public function __construct($full_name, $phone, $email, $address_street, $address_number, $address_complement, $address_commune_id, $address_commune_name, $kind) {
      $this->full_name = $full_name;
      $this->phone = $phone;
      $this->email = $email;
      $this->$kind = $kind;
      $this->address = new Address($address_street, $address_number, $address_complement, $address_commune_id, $address_commune_name);
    }

    function getDestiny() {
      return array(
        'name' => 'predeterminado',
        'full_name' => $this->getFullName(),
        'email' => $this->getEmail(),
        'phone' => $this->getPhone(),
        'street' => $this->getAddress()->getStreet(),
        'number' => $this->getAddress()->getNumber(),
        'complement' => $this->getAddress()->getComplement(),
        'commune_id' => $this->getAddress()->getCommuneId(),
        'commune_name' => $this->getAddress()->getCommuneName(),
        'kind' => $this->getKind(),
        'destiny_id' => null,
        'courier_destiny_id' => null,
        'courier_branch_office_id' => null
      );
    }

    function getFullName() {
      return $this->full_name;
    }

    function getPhone() {
      return $this->phone;
    }

    function getEmail() {
      return $this->email;
    }

    function getAddress() {
      return $this->address;
    }

    function getKind() {
      return $this->kind;
    }
  }
?>