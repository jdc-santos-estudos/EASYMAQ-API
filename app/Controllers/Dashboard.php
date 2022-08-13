<?php

namespace App\Controllers;

use App\Controllers\APIController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use \Firebase\JWT\JWT;

class Dashboard extends APIController
{
  public function login()
  {
    try {

      // $iat = time();
      // $nbf = $iat + 10;
      // $exp = $iat + 3600;
     
      // $payload = array(
      //     "iss" => "The_claim",
      //     "aud" => "The_Aud",
      //     "iat" => $iat,
      //     "nbf" => $nbf,
      //     "exp" => $exp,
      //     "data" => [
      //       "nome" => "Gustavo",
      //       "perfil" => 'ADMIN'
      //     ]
      // );
     
      // $token = JWT::encode($payload, 'segredosecreto', "HS256");

      $response = [
        'status'   => 200,
        'error'    => null,
        'messages' => [
            'success' => 'Employee created successfully'
        ]
      ];

      echo "OK";

      //$this->respond($response);
    } catch(Exception $e) {
      $this->respond(['msg' => 'error']);
    }
  }
}