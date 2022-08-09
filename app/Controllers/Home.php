<?php

namespace App\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\AccountApi;
use GuzzleHttp\Client;


class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }
}


