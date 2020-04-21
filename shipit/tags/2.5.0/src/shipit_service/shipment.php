<?php
  include 'address.php';
  include 'product.php';
  include 'purchase.php';
  class Shipment {

    public $branch_office_id = '';
    public $reference = '';
    public $full_name = '';
    public $email = '';
    public $packing = 'Sin empaque';
    public $shipping_type = 'normal';
    public $items_count = 1;
    public $length = 10.0;
    public $width = 10.0;
    public $height = 10.0;
    public $weight = 1.0;
    public $destiny = 'Domicilio';
    public $is_payable = false;
    public $approx_size = '';
    public $courier_for_client = '';
    public $address = null;
    public $inventory_activity =  array();
    public $with_purchase_insurance = false;
    public $purchase = null;
    public $mongo_order_seller = 'woocommerce';
    public $seller_order_id = '';

    public function __construct($branch_office_id, $reference, $full_name, $email, $packing, $shipping_type, $items_count, $length, $width, $height, $weight, $destiny, $is_payable, $approx_size, $courier_for_client, $address, $inventory_activity, $with_purchase_insurance, $purchase_ticket_number, $purchase_amount, $purchase_extra, $purchase_active, $purchase_description, $mongo_order_seller, $seller_order_id, $street, $number, $complement, $commune_id, $latitude, $longitude) {
      $this->branch_office_id = $branch_office_id;
      $this->reference = $reference;
      $this->full_name = $full_name;
      $this->email = $email;
      $this->packing = $packing;
      $this->shipping_type = $shipping_type;
      $this->packing = $packing;
      $this->items_count = $items_count;
      $this->destiny = $destiny;
      $this->is_payable = $is_payable;
      $this->approx_size = $approx_size;
      $this->courier_for_client = $courier_for_client;
      $this->address = new Address($street, $number, $complement, $commune_id, $latitude, $longitude);
      $this->inventory_activity = array('inventory_activity_orders_attributes' => $inventory_activity);
      $this->with_purchase_insurance = $with_purchase_insurance;
      $this->purchase = new Purchase($purchase_amount, $purchase_ticket_number, $purchase_extra, $purchase_description, $purchase_active);
      $this->mongo_order_seller = 'woocommerce';
      $this->seller_order_id = $seller_order_id;
      $this->length = $length;
      $this->width = $width;
      $this->height = $height;
      $this->weight = $weight;
    }
  }
?>
