<?php

namespace App\Controllers;

use App\Controllers\APIController;

use App\Models\Usuario_model;

class Home extends APIController
{
    public function index()
    {
        
      // $userModel = new Usuario_model();

      // $userModel->teste();


      HttpSuccess([
          "success" => true,
          "data" => [
            "token" => '213'
          ]
        ], $this->respond);

      // return view('welcome_message');
    }
}


