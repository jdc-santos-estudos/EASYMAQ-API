<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;

use \Firebase\JWT\JWT;

class APIController extends ResourceController
{
  protected $helpers = ['Http'];
}