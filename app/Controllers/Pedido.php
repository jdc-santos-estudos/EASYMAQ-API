<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Pedido_model;
use App\Models\Maquina_model;
use App\Models\Configuracao_model;

use \Firebase\JWT\JWT;
use Dompdf\Dompdf;

class Pedido extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function novo()
  {
    try {
      if(!$this->autenticarUsuario(['CLIENTE'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $this->validation->setRules([
        'nm_receptor' => 'required|regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]',
        'rg_receptor' => 'required|numeric',
        'tel_receptor' => 'required|numeric',
        'cd_cep' => 'required|numeric',
        'cd_cidade' => 'required|numeric',
        'nm_logradouro' => 'required|regex_match[/^([a-zA-Z0-9çáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]',
        'nr_local' => 'required|numeric',
        'nm_bairro' => 'required|regex_match[/^([a-zA-Z0-9çáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]',
        "dt_entrega" => 'required|regex_match[/^[\d]{4}-[\d]{2}-[\d]{2}\T[\d]{2}\:[\d]{2}$/]',
        "dt_devolucao" => 'required|regex_match[/^[\d]{4}-[\d]{2}-[\d]{2}\T[\d]{2}\:[\d]{2}$/]',
        'maquinas' => 'required'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      $dados = json_decode(json_encode($this->request->getVar()),1);

      $maquinasId = $dados['maquinas'];

      $dados['cd_usuario'] = $this->userData->cd_usuario;
      $dados['cd_status_pedido'] = 1;
      $dados['dt_entrega'] .= ':00';
      $dados['dt_devolucao'] .= ':00';
      $dados['dt_pedido'] = date("Y-m-d H:i:s");
      
      unset($dados['cd_estado']);
      unset($dados['maquinas']);

      $maqM = new Maquina_model();
      $maquinas = $maqM->listarPorId($maquinasId);

      $pedidoArray = [];
      $emailsFornecedores = [];
    
      if (is_array($maquinas)) {
        for ($i=0; $i < count($maquinas); $i++) {
          if (!isset($pedidoArray[$maquinas[$i]['cd_fornecedor']])) {
            $pedidoArray[$maquinas[$i]['cd_fornecedor']] = $dados;
            $pedidoArray[$maquinas[$i]['cd_fornecedor']]['maquinas'] = [];
            $pedidoArray[$maquinas[$i]['cd_fornecedor']]['maquinas'][] = $maquinas[$i];

            $emailsFornecedores[] = $maquinas[$i];
          } else {
            $pedidoArray[$maquinas[$i]['cd_fornecedor']]['maquinas'][] = $maquinas[$i];
          }
        }
      }

      $pedidoM = new Pedido_model();

      if($pedidoM->cadastrar($pedidoArray)) return $this->HttpError400([], 'Erro tentar cadastrar o pedido');

      // $resPM = $pedidoM->salvarMaquinasDoPedido($pedidoId, $maquinas);
      
      for ($i = 0; $i < count($emailsFornecedores); $i++) {
        $payload = [
          'email' => $emailsFornecedores[$i]['ds_email_fornecedor'],
          'template' => 'novoPedido',
          'replace' => [
            'nm_usuario' => $emailsFornecedores[$i]['nm_fornecedor']
          ]
        ];
  
        $msg = JWT_generate($payload);
      
        $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));
  
        try {
          $client = \Config\Services::curlrequest();
          $response = $client->request('POST', getenv('api_email').'enviar-email',['json' => ['data' => $c]]);
        } catch(\Exception $e) {
          return $this->HttpError500([], $e, json_encode($payload), 'Erro interno ao tentar enviar email para o usuário.');
        }
      }
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess($vlTotal,'pedido efetuado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar efetuar o pedido');
    }
  }

  public function atualizar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      // deve verificar o perfil do usuário e verificar quem pode atualizar o pedido para qual perfil:
      // exemplo: 
      // apenas o FORNECEDOR pode atualizar o pedido para ACEITO PELO FORNECEDOR
      // apenas o CLIENTE pode atualizar o pedido para PEDIDO ENTREGUE
      // etc...
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess([],'pedido atualizado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar o pedido');
    }
  }

  public function listar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $pedidoM =  new Pedido_model();
      $maqM = new Maquina_model();

      $filtros = json_decode(json_encode($this->request->getVar()),1);

      if ($this->userData->cd_tipo == 'FORNECEDOR') $filtros['cd_fornecedor'] = $this->userData->cd_usuario;
      if ($this->userData->cd_tipo == 'CLIENTE') $filtros['cd_cliente'] = $this->userData->cd_usuario;
      
      $pedidos = $pedidoM->listar($filtros);

      $pedidosIds = [];

      for ($i = 0; $i < count($pedidos); $i++ ) $pedidosIds[] = $pedidos[$i]['cd_pedido'];

      $maquinas = $maqM->listarPorPedido($pedidosIds);

      $maqImgs = $maqM->getImagens($maquinas);

      for ($i=0; $i < count($maqImgs); $i++) {
        for ($j=0; $j < count($maquinas); $j++) {
          if ($maqImgs[$i]['cd_maquina'] == $maquinas[$j]['cd_maquina']) {
            if(!count($maquinas[$j]['imagens'])) $maquinas[$j]['imagens'] = [];
            $maquinas[$j]['imagens'][] = $maqImgs[$i];
          }
        }
      }

      for ($i = 0; $i < count($maquinas); $i++ ) {
        for ($j = 0; $j < count($pedidos); $j++ ) {
          if ($maquinas[$i]['cd_pedido'] == $pedidos[$j]['cd_pedido']) {
            if (!count($pedidos[$j]['maquinas'])) $pedidos[$j]['maquinas'] = [];
            $pedidos[$j]['maquinas'][] = $maquinas[$i];
          } 
        }
      }

      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($pedidos,'pedidos listados com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar os pedidos');
    }
  }

  public function baixarContrato($cd_contrato)
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess([],'contrato recuperado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar recuperar o contrato');
    }
  }

  public function atualizarStatus() {
    try {
      if(!$this->autenticarUsuario(['CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $this->validation->setRules([
        'cd_pedido' => 'required|numeric',
        'nm_status_pedido' => 'required|in_list[ACE,REC,ROTA_ENTREGA,POSSE_CLI,POSSE_FORN,APR,CAN, RECOLHIDO, AGU_PGTO]'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      $dados = json_decode(json_encode($this->request->getVar()),1);

      $pedidoM =  new Pedido_model();
      if (in_array($dados['nm_status_pedido'],["ACE",'REC','ROTA_ENTREGA','POSSE_CLI','POSSE_FORN','RECOLHIDO']) && $this->userData->cd_tipo != 'FORNECEDOR') {
        return $this->HttpError400([], 'Erro ao tentar atualizar o pedido');
      }

      if (in_array($dados['nm_status_pedido'],["APR",'CAN','AGU_PGTO']) && $this->userData->cd_tipo != 'CLIENTE') {
        return $this->HttpError400([], 'Erro ao tentar atualizar o pedido');
      }

      $pedido = $pedidoM->listar(['cd_pedido' => $dados['cd_pedido']])[0];

      if ($dados['nm_status_pedido'] == 'AGU_PGTO') {
        try {
          $url = $this->pagar($pedido);
          return $this->HttpSuccess(['stripe_url' => $url],'link do pagamento criado com sucesso!');
        } catch (\Exception $e) {
          return $this->HttpError500([], json_encode($pedido),'ERRO NO STRIPE','ERRO AO CONSUMIR API DA STRIPE');
        }
      }

      if ($dados['nm_status_pedido'] == 'APR') {
        try {
          $this->gerarContrato($pedido);
        } catch (\Exception $e) {
          return $this->HttpError500([], json_encode($pedido),'ERRO AO GERAR PDF', 'Erro interno ao tentar gerar o PDF.');
        }
      }

      if(!$pedidoM->atualizarStatus($dados)) return $this->HttpError400([], 'Erro ao tentar atualizar o pedido');
      
      try {
        $this->enviarEmail($pedido);
      } catch (\Exception $e) {
        return $this->HttpError500([], json_encode($dadosEmail),'ERRO AO ENVIAR EMAIL', 'Erro interno ao tentar enviar email para o usuário.');
      }

      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<      
      return $this->HttpSuccess([],'pedido atualizado com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar o pedido');
    }
  }

  public function pagtoStripe() {
    try {
      // This is your Stripe CLI webhook secret for testing your endpoint locally.
      $endpoint_secret = 'whsec_0e5b5005d07d5a9fc6f0273f211b83f5e8cc9f797f38733150e1d2d3d8d3d967';

      $payload = @file_get_contents('php://input');
      $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
      $event = null;

      try {
        $event = \Stripe\Webhook::constructEvent(
          $payload, $sig_header, $endpoint_secret
        );
      } catch(\UnexpectedValueException $e) {
        // Invalid payload
        http_response_code(400);
        exit();
      } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        http_response_code(400);
        exit();
      }

      // Handle the event
      switch ($event->type) {
        case 'payout.paid':
          $pedidoM =  new Pedido_model();
          if(!$pedidoM->atualizarStatus(['cd_pedido' => '', 'nm_status_pedido' => 'APR'])) {
            return $this->HttpError400([], 'Erro ao tentar atualizar o pedido');
          }
          echo "OK";
        // ... handle other event types
        default:
          echo 'Received unknown event type ' . $event->type;
      }

      http_response_code(200);
    } catch(\Exception $e) {
      echo $e->getMessage();
    }
  }

  private function enviarEmail($pedido) {
    $pedidoM =  new Pedido_model();

    $dadosEmail = [];
    
    if ( $this->userData->cd_tipo == 'FORNECEDOR') {
      $dadosEmail['ds_email'] = $pedido['ds_email_cliente'];
      $dadosEmail['nm_usuario'] = $pedido['nm_cliente'];
    } else if ( $this->userData->cd_tipo == 'CLIENTE') {
      $dadosEmail['ds_email'] = $pedido['ds_email_fornecedor'];
      $dadosEmail['nm_usuario'] = $pedido['nm_fornecedor'];
    }

    $payload = [
      'email' => $dadosEmail['ds_email'],
      'template' => 'atualizacaoPedido',
      'replace' => [
        'cd_pedido' => $pedido['cd_pedido'],
        'nm_usuario' => $dadosEmail['nm_usuario'],
        'nm_status_pedido' => $pedidoM->getStatusPedido($dados['nm_status_pedido'])
      ]
    ];

    $msg = JWT_generate($payload);    
    $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));

    $client = \Config\Services::curlrequest();
    $client->request('POST', getenv('api_email').'enviar-email',['json' => ['data' => $c]]);
  }

  private function pagar($pedido) {
    try {
      $maqM = new Maquina_model();  

      $this->validation->setRules([ 'cd_pedido' => 'required|numeric']);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      $dados = json_decode(json_encode($this->request->getVar()),1);

      $maquinas = $maqM->listarPorPedido(array($pedido['cd_pedido']));
      $vlPedido = $this->getvalorLocacao($pedido,$maquinas);

      // This is your test secret API key.
      \Stripe\Stripe::setApiKey(getenv('API_STRIPE_KEY'));

      // lista os preços
      $stripe = new \Stripe\StripeClient(getenv('API_STRIPE_KEY'));
      
      $newPrice = $stripe->prices->create(
        [
          'product' => 'prod_MocA4eRlQX8kVA',
          'unit_amount' => str_replace('.','',(string)$vlPedido),
          'currency' => 'brl'
        ]
      );

      $YOUR_DOMAIN = getenv('FRONT_URL'). '/retorno-stripe?cd_pedido='.$dados['cd_pedido'];

      $checkout_session = \Stripe\Checkout\Session::create([
        'line_items' => [[
          // 'price' => 'price_1M4ywIJKTXpgmiLzxS6BOmW1',
          'price' => $newPrice['id'],
          'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN.'&res=success',
        'cancel_url' => $YOUR_DOMAIN.'&res=error',
      ]);

      return $checkout_session->url;
    } catch(\Exception $e) {
      echo $e->getMessage();
      throw $e;
    }
  }

  private function gerarContrato($pedido) {
    try {
      $dompdf = new Dompdf();
      $maqM = new Maquina_model();
      $config = new Configuracao_model();
      $pedidoM = new Pedido_model();      

      // chamando a função de logar do usuário
      $config = $config->getConfig(['config' => 'TEMPLATE_CONTRATO']);

      if (count($config) === 1) {
        $config[0]['ds_valor'] = stripslashes($config[0]['ds_valor']);
        $html = json_decode($config[0]['ds_valor'],1);
      }

      $maquinas = $maqM->listarPorPedido(array($pedido['cd_pedido']));

      $html = $html['conteudo'];

      $dadosCliente = $this->getDadosClienteContrato($pedido);
      $dadosFornecedor = $this->getDadosFornecedorContrato($pedido);
      $dadosMaquina = $this->getDadosMaquinasContrato($maquinas);
      $valorLocacao = $this->getvalorLocacao($pedido, $maquinas);

      $formatter = new \NumberFormatter('pt_BR',  \NumberFormatter::CURRENCY);
      $valorLocacao = '<b>'.$formatter->formatCurrency($valorLocacao, 'BRL').'</b>';

      $html = str_replace("#dadosCliente#", $dadosCliente, $html);
      $html = str_replace("#dadosFornecedor#", $dadosFornecedor, $html);
      $html = str_replace("#dadosMaquinas#", $dadosMaquina, $html);

      $html = str_replace("#valorLocacao#", $valorLocacao, $html);
      // salvando no servidor...
      $dompdf->loadHtml($html);

      $dompdf->setPaper('A4');
      $dompdf->render();
      $output = $dompdf->output();
      file_put_contents('../../contratos/'.$pedido['cd_pedido'].'.pdf', $output);
    } catch(\Exception $e) {
      throw $e;
    }
  }

  private function getDadosClienteContrato($p) {
    $str = '';

    if (!empty($p['cd_rg_cliente'])) {
      $str .= '<b>'.strtoupper($p['nm_cliente']).'</b>, ';
      $str .= '<b>'.formatCnpjCpf($p['cd_cpf_cliente']).'</b>, ';
      $str .= '<b>'.formatRG($p['cd_rg_cliente']). '</b> ';
    } else {
      $str .= '<b>'.strtoupper($p['razao_social_cliente']).'</b>, ';
      $str .= 'representando o CNPJ: <b>'.formatCnpjCpf($p['cd_cnpj_cliente']).'</b> ';
    }

    $str .= 'dorovante demoninado <b>LOCATÁRIO</b>';

    return $str;
  }

  private function getDadosFornecedorContrato($p) {
    $str = ' ';

    if (!empty($p['cd_rg_fornecedor'])) {
      $str .= '<b>'.strtoupper($p['nm_fornecedor']).'</b>, ';
      $str .= '<b>'.formatCnpjCpf($p['cd_cpf_fornecedor']).'</b>, ';
      $str .= '<b>'.formatRG($p['cd_rg_fornecedor']). '</b> ';
    } else {
      $str .= '<b>'.strtoupper($p['razao_social_fornecedor']).'</b>, ';
      $str .= 'representando o CNPJ: <b>'.formatCnpjCpf($p['cd_cnpj_fornecedor']).'</b> ';
    }

    $str .= 'dorovante demoninado <b>LOCADOR</b>';
    return $str;
  }

  private function getDadosMaquinasContrato($m) {
    $str = '<ul>';

    for ($i =0; $i < count($m); $i++){
      $nm_cat = strtoupper($m[$i]['nm_categoria']);
      $str .= "<li><b>{$nm_cat}</b> - PLACA: <b>{$m[$i]['cd_placa']}</b> CHASSI: <b>{$m[$i]['nr_chassi']}</b></li>";
    }

    $str .= "</ul>";

    return $str;
  }

  private function getvalorLocacao($p, $m) {
    $dt_entrega = strtotime($p['dt_entrega']);
    $dt_devolucao = strtotime($p['dt_devolucao']);
    $intervalo = $dt_devolucao - $dt_entrega;
    $minutos = $intervalo/60;
    
    $vltotMaquinas = 0;

    for ($i =0; $i < count($m); $i++){
      $vltotMaquinas = round($vltotMaquinas) +$m[$i]['vl_hora'];
    }

    $vlMin = $vltotMaquinas / 60;
    return number_format((float)$vlMin * $minutos, 2, '.', '') ;
  }
}