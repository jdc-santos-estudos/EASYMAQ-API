<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Pedido_model;
use App\Models\Maquina_model;

use \Firebase\JWT\JWT;

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
          // 'email' => $emailsFornecedores[$i]['ds_email_fornecedor'],
          'email' => 'jdc.santos93@gmail.com',
          'template' => 'novoPedido',
          'replace' => [
            'nm_usuario' => $emailsFornecedores[$i]['nm_fornecedor']
          ]
        ];
  
        $msg = JWT_generate($payload);
      
        $c = base64_encode($this->encrypter->encrypt($msg, getenv('api_email_key')));
  
        try {
          $client = \Config\Services::curlrequest();
          $response = $client->request('POST', getenv('API_EMAIL').'enviar-email',['json' => ['data' => $c]]);
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

      if ($this->userData == 'FORNECEDOR') $filtros['cd_fornecedor'] = $this->userData->cd_usuario;
      if ($this->userData == 'CLIENTE') $filtros['cd_cliente'] = $this->userData->cd_usuario;
      
      $pedidos = $pedidoM->listar($filtros);

      $pedidosIds = [];

      for ($i = 0; $i < count($pedidos); $i++ ) $pedidosIds[] = $pedidos[$i]['cd_pedido'];

      $maquinas = $maqM->listarPorPedido($pedidosIds);

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
}