<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Estado_model;

class Stripe extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function init()
  {
    try {
      $stripe = new \Stripe\StripeClient("sk_test_51LsE5OJKTXpgmiLzZGvpDXn2njAsyxKuUgmCtfrACCO2qPdkBUYyU6w7uR6Yt5CfN0C9CVHv0wtPashB2OjbV12g0060SPCTRQ");

      // $res = $stripe->customers->create([
      //   'description' => 'My First Test Customer (created for API docs at https://www.stripe.com/docs/api)',
      //   'email' => 'emailtestestripe@easymaq.app'
      // ]);

      // $res = $stripe->customers->update(
      //   'cus_MbUcgA0xzJJETV',
      //   ['email' => 'emailatualizado@easymaq.app']
      // );

      $res = $stripe->customers->createSource(
        'cus_MbUcgA0xzJJETV',
        ['source' => [
          "object" => 'card',
          "number" => '1234 1234 1234 1234',
          "exp_month" => "08",
          "exp_year" => "23",
          "cvc" => "123"
        ]]
      );

      $res = json_decode(json_encode($res),1);

      return $this->HttpSuccess($res,'stripe consumido com sucesso');
    } catch(\Exception $e) {
      echo "<pre>";
      print_r($e);
      exit;
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao usar o Stripe');
    }
  }
}