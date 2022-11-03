<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->post('/dashboard/login','Dashboard::login');

// rotas de usuário
$routes->post('/usuario/cadastrar','Usuario::cadastrar');
$routes->post('/usuario/cadastrar-admin','Usuario::cadastrarAdmin');
$routes->get('/usuario/perfil','Usuario::perfil');
$routes->get('/usuario/get-info','Usuario::getInfo');
$routes->get('/usuario/get-fornecedores','Usuario::listarFornecedores');
$routes->get('/usuario/confirm-email', "Usuario::confirmEmail");
// $routes->delete('/usuario/deletar-conta','Usuario::deletarConta'); nao será utilizado por enquanto

// categorias
$routes->post('/categorias/cadastrar','Categoria::cadastrar');
$routes->post('/categorias/atualizar','Categoria::atualizar');
$routes->get('/categorias/listar', 'Categoria::listar');
$routes->delete('/categorias/excluir/(:num)','Categoria::excluir/$1');

// perfil
// $routes->post('/perfil/cadastrar','Perfil::cadastrar'); // nao terá por enquanto
// $routes->post('/perfil/atualizar','Perfil::atualizar'); // nao terá por enquanto
$routes->get('/perfil/listar', 'Perfil::listar');
// $routes->delete('/perfil/excluir/(:num)','Perfil::excluir/$1'); // nao terá por enquanto

// pedido
$routes->post('/pedido/novo-pedido', 'Pedido::novo');
$routes->get('/pedido/listar', 'Pedido::listar');
$routes->post('/pedido/atualizar', 'Pedido::atualizar');
$routes->get('/pedido/baixar-contrato/(:num)', 'Pedido::baixarContrato/$1');

// maquina
$routes->post('/maquina/cadastrar', 'Maquina::cadastrar');
$routes->get('/maquina/listar', 'Maquina::listarDisponiveis');
$routes->get('/maquina/listar-por-perfil', 'Maquina::listarPorPerfil');
$routes->post('/maquina/atualizar', 'Maquina::atualizar');
$routes->delete('/maquina/deletar/(:num)', 'Maquina::deletar/$1');

// rotas para visualizar o contrato e gerar seu PDF
$routes->get('/contrato/show-pdf','Contrato::showPDF');
$routes->get('/contrato/download-pdf','Contrato::downloadPDF');
$routes->get('/contrato/docusign', 'Contrato::docusign');

$routes->get('/contrato/docusign-callback', 'Contrato::docusignCallback1');
$routes->post('/contrato/docusign-callback', 'Contrato::docusignCallback');

// rotas de configurações
$routes->get('/v1/get-config-front','Configuracao::getConfigFront');
$routes->get('/v1/get-configs','Configuracao::getConfigs');
$routes->get('/v1/get-termos','Configuracao::getTermos');
$routes->post('/v1/save-config','Configuracao::saveConfig');


$routes->get('/estado/listar', "Estado::listar");
$routes->get('/cidade/listar/(:num)', "Cidade::listar/$1");

$routes->get('/stripe', 'Stripe::init');

$routes->get('log/listar', 'Log::listar');

if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}