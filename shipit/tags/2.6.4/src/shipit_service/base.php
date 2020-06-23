<?php
class Base {

  private $kind = '';
  private $order_id = '';
  private $phone = '';
  private $items = array();
  private $name = '';
  private $inventories = array();
  private $with_purchase_insurance = false;
  private $packing = 'Sin empaque';
  private $is_payable = false;
  private $total_price = 0.0;
  private $mongo_order_seller = 'shopify';
  private $seller_order_id = '';
  private $width = 10.0;
  private $height = 10.0;
  private $length = 10.0;
  private $weight = 1.0;
  private $referring_site = '';
  private $commune_id = '';
  private $volumetric_weight = 1.0;
  // create a class 
  // private $company = new Company();
  // private $seller = new Seller();
  // private $skus = array(new Sku());
  // private $destiny = new Destiny();
  // private $origin = new Origin();
  // private origin = new Sizes();
  // private $courier = new Courier();
  // private $purchase = new Purchase();{ detail: 'Orden from Shopify', ticket_number: order_id, amount: total_price, extra_insurance: false }
  // private $customer = new Customer();
  // private $payment = new Payment();
  // private $products = array(new Product());
  private $algorithm = '1';
  private $algorithm_days = '0';

}

?>