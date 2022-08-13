<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use Tests\Support\Libraries\ConfigReader;


use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class JdcTest extends CIUnitTestCase
{

    use ControllerTestTrait;
    // use DatabaseTestTrait;

    public function testIsDefinedAppPath()
    {
        $this->assertTrue(defined('APPPATH'));
    }

    public function test123()
    {               
        $this->assertTrue(true, 'deu erro aqui');
    }

    public function test3444()
    {               
        $this->assertTrue(true, 'deu erro aqui');
    }

    public function testRequest() {
        $config              = new App();
        $results = $this->controller(\App\Controllers\Dashboard::class)->execute('login');
    }
}