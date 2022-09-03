<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use App\Models\Logs_model;
use App\Models\Configuracao_model;

class API extends ResourceController
{
  protected $helpers = ['Http','Url','ObjectToArray', 'JWT'];
  protected $request;
  protected $validation;

  public function __construct() {
    $this->request = \Config\Services::request();
    $this->validation = \Config\Services::validation();
  }

  protected function HttpSuccess($data, $message)
  {
    return $this->response->setStatusCode(200)->setJSON([
      "success" => true,
      "message" => $message,
      "data" => $data
    ]);
  }

  protected function HttpError400($data, $message)
  {
    return $this->response->setStatusCode(400)->setJSON([
      "success" => false,
      "message" => $message,
      "data" => $data
    ]);
  }

  protected function HttpError500($data, $error, $errorMsg, $message) 
  {
    $log = new Logs_model();

    $resLog = $log->salvarLog([
      'ds_url' => $this->request->getPath(),
      'nm_erro' => $errorMsg,
      'ds_erro' => $error,
    ]);
    
    if ($resLog) {
      return $this->response->setStatusCode(500)->setJSON([
        "success" => false,
        "message" => $message,
        "data" => $data
      ]);

    } else {
      return $this->response->setStatusCode(400)->setJSON([
        "success" => false,
        "message" => 'ERRO AO CONECTAR NA BASE DE DADOS',
        "data" => 'ERRO AO CONECTAR NA TABELA DE LOGS'
      ]);
    }
  }
}