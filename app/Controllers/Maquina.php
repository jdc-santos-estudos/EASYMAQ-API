<?php

namespace App\Controllers;

use App\Controllers\API;

// models
use App\Models\Maquina_model;

use \Firebase\JWT\JWT;

class Maquina extends API
{
  public function __construct() {
    parent::__construct();
  }

  public function cadastrar()
  {
    try {
      if(!$this->autenticarUsuario(['FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $this->validation->setRules([
        'cd_categoria' => 'required|numeric',
        'cd_status'    => 'required|in_list[ATIVO,INATIVO]',
        'cd_placa' => 'required|min_length[7]',
        'nr_chassi' => 'required|min_length[5]',
        'vl_hora' => 'required',
        'cd_cidade' => 'required'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // chama função de cadastro de usuário
      $dados = json_decode(json_encode($this->request->getVar()),1);

      $dados['vl_hora'] = $dados['vl_hora'] /100;

      $imagens = $dados['imagens'];
      
      // valida a extensao das imagens
      if(!$this->imagensValidas($imagens)) return $this->HttpError400($this->validation->getErrors(), 'Arquivo inválido');     
      
      unset($dados['cd_estado']);
      unset($dados['imagens']);

      $dados['cd_fornecedor'] = $this->userData->cd_usuario;
  
      $maquina = new Maquina_model();

      $cd_maquina = $maquina->cadastrar($dados);

      if(!$cd_maquina) return $this->HttpError400([], 'Erro tentar cadastrar a máquina');

      $this->atualizarImagens($cd_maquina, $imagens);

      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess(['cd_maquina' => $cd_maquina],'máquina cadastrada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar cadastrar a máquina');
    }
  }

  public function atualizar()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $this->validation->setRules([
        'cd_categoria' => 'required|numeric',
        'cd_status'    => 'required|in_list[ATIVO,INATIVO]',
        'cd_placa' => 'required|min_length[7]',
        'nr_chassi' => 'required|min_length[5]',
        'vl_hora' => 'required|numeric',
        'cd_cidade' => 'required'
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }

      // chama função de cadastro de usuário
      $dados = json_decode(json_encode($this->request->getVar()),1);
      
      $dados['vl_hora'] = $dados['vl_hora'] /100;
    
      $imagens = $dados['imagens'];

      // valida a extensao das imagens
      if(!$this->imagensValidas($imagens)) return $this->HttpError400($this->validation->getErrors(), 'Arquivo inválido');     
      
      $cd_maquina = $dados['cd_maquina'];

      unset($dados['cd_estado']);
      unset($dados['imagens']);    
      $maquina =  new Maquina_model();

      $dadosMaquina = $maquina->listarPorPerfil(['cd_maquina' => $dados['cd_maquina']]);

      $dados['cd_fornecedor'] = $dadosMaquina[0]['cd_fornecedor'];
      unset($dados['cd_maquina']);

      if(!$maquina->atualizar($cd_maquina, $dados)) return $this->HttpError400([], 'Erro tentar atualizar a máquina');

      $resImagens = $this->atualizarImagens($cd_maquina, $imagens);
      if (!$resImagens['success']) {
        return $this->HttpError400($resImagens['error'], 'Erro tentar atualizar as imagens da máquina');
      }
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      // retornando o token com a mensagem de sucesso
      return $this->HttpSuccess([],'máquina atualizada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar atualizar a máquina');
    }
  }

  private function atualizarImagens($cd_maquina, $imagens)
  {
    try {

      $maq = new Maquina_model();
      
      $dbImagens = $maq->getImagens($cd_maquina);

      $cadastrar = [];
      $deletar = [];

      for ($i = 0; $i < count($dbImagens); $i++) {
        $dbImg = $dbImagens[$i];
        $existe = false;

        for ($j = 0; $j < count($imagens); $j++) {
          if (isset($imagens[$j]['idtbl_maquina_imagem'])) {
            if ($dbImg['idtbl_maquina_imagem'] == $imagens[$j]['idtbl_maquina_imagem'] ) $existe = true;
          }
        }
        
        // se nao encontrar a imagem na lista que veio do front quer dizer de ela deve ser deletada entao adiciona no array dos itens que serão deletados.
        if(!$existe) $deletar[] = $dbImg['idtbl_maquina_imagem'];
      }

      for ($j = 0; $j < count($imagens); $j++) {
        if (isset($imagens[$j]['fileBase64'])) {
          $imgName = md5(time().$imagens[$j]['nm_imagem']).'_'. $imagens[$j]['nm_imagem'];

          $pathImg = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
          $pathImg .= getenv('usar_public_dir') == '1' ? 'public'.DIRECTORY_SEPARATOR: '';
          $pathImg .="imagens".DIRECTORY_SEPARATOR;

          if(file_put_contents($pathImg.$imgName, file_get_contents($imagens[$j]['fileBase64']))) {
            $cadastrar[] = [
              'cd_maquina' => $cd_maquina,
              'nm_imagem' => $imgName
            ];
          }
        }
      }

      // deleta as imagens que não foram enviadas pelo front
      if (count($deletar) > 0 ) $maq->deletarImagens($deletar);
      if (count($cadastrar) > 0 ) $maq->cadastrarImagens($cadastrar);

      return ['success' => true];

    } catch(\Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  public function listarDisponiveis() {
    {
      try {          
        // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        $this->validation->setRules([
          'cd_categoria' => 'permit_empty|numeric|max_length[15]',
          'cd_usuario'    => 'permit_empty|numeric|max_length[12]',
          'cd_estado' => 'permit_empty|numeric|max_length[12]',
          'cd_cidade' => 'permit_empty|numeric|max_length[12]',
          'vl_min' => 'permit_empty|numeric|max_length[15]',
          'vl_max' => 'permit_empty|numeric|max_length[12]',
          'search' => 'permit_empty|regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]',
          'cd_maquina' => 'permit_empty|numeric|max_length[15]'
        ]);

  
        // executando a validação dos erros, se encontrar, retorna os erros
        if(!$this->validation->withRequest($this->request)->run()) {
          return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
        }

        $filtros = json_decode(json_encode($this->request->getVar()),1);

        if(is_numeric($filtros['vl_min'])) $filtros['vl_min'] = $filtros['vl_min']/100;
        if(is_numeric($filtros['vl_max'])) $filtros['vl_max'] = $filtros['vl_max']/100;
        
        $maq = new Maquina_model();
        $maquinas = $maq->listarDisponiveis($filtros);

        if (count($maquinas)) {
          $maqImgs = $maq->getImagens($maquinas);

          for ($i=0; $i < count($maqImgs); $i++) {
            for ($j=0; $j < count($maquinas); $j++) {
              if ($maqImgs[$i]['cd_maquina'] == $maquinas[$j]['cd_maquina']) {
                if(!count($maquinas[$j]['imagens'])) $maquinas[$j]['imagens'] = [];
                $maquinas[$j]['imagens'][] = $maqImgs[$i];
              }
            }
          }
        }
        
        // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
  
        return $this->HttpSuccess($maquinas,'máquinas listadas com sucesso');
      } catch(\Exception $e) {
        return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar as máquinas');
      }
    }
  }

  public function listarPorPerfil()
  {
    try {
      if(!$this->autenticarUsuario(['ADMIN1', 'ADMIN2','CLIENTE','FORNECEDOR'])) {
        return $this->HttpError400([], 'token de acesso inválido ou o usuário não possui permissão');
      }

      $this->validation->setRules([
        'cd_categoria' => 'permit_empty|numeric|max_length[15]',
        'cd_usuario'    => 'permit_empty|numeric|max_length[12]',
        'cd_estado' => 'permit_empty|numeric|max_length[12]',
        'cd_cidade' => 'permit_empty|numeric|max_length[12]',
        'vl_min' => 'permit_empty|numeric|max_length[15]',
        'vl_max' => 'permit_empty|numeric|max_length[12]',
        'search' => 'permit_empty|regex_match[/^([a-zA-ZçáàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ]|\s|)+$/]',
      ]);

      // executando a validação dos erros, se encontrar, retorna os erros
      if(!$this->validation->withRequest($this->request)->run()) {
        return $this->HttpError400($this->validation->getErrors(), 'campos inválidos');
      }
        
      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $filtros = json_decode(json_encode($this->request->getVar()),1);

      if ($this->userData == 'FORNECEDOR') $filtros['cd_fornecedor'] = $this->userData->cd_usuario;
      if ($this->userData == 'CLIENTE') $filtros['cd_cliente'] = $this->userData->cd_usuario;

      $maq = new Maquina_model();
      
      $maquinas = $maq->listarPorPerfil($filtros);
      $maqImgs = $maq->getImagens($maquinas);

      for ($i=0; $i < count($maqImgs); $i++) {
        for ($j=0; $j < count($maquinas); $j++) {
          if ($maqImgs[$i]['cd_maquina'] == $maquinas[$j]['cd_maquina']) {
            if(!count($maquinas[$j]['imagens'])) $maquinas[$j]['imagens'] = [];
            $maquinas[$j]['imagens'][] = $maqImgs[$i];
          }
        }
      }

      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess($maquinas,'máquinas listadas com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar listar as máquinas');
    }
  }

  public function deletar($cd_maquina)
  {
    try {
      $logado = !$this->autenticarUsuario(['ADMIN1', 'ADMIN2','FORNECEDOR']);
        
      // resto do código >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
      $maq = new Maquina_model();

      if ($this->userData->tipo_perfil == 'FORNECEDOR') {
        $maquina = $maq->listar(['cd_maquina' => $cd_maquina]);

        if (count($maquina) && $maquina[0]['cd_fornecedor'] != $this->userData->cd_usuario) {
          return $this->HttpError400([], 'permissão negada. a máquina não pertence a este fornecedor');
        }
      }
    
      if(!$maq->excluir($cd_maquina)) return $this->HttpError400([], 'Erro ao excluir a máquina');
      // resto do código <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      return $this->HttpSuccess([],'máquina deletada com sucesso');
    } catch(\Exception $e) {
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno ao tentar deletar a máquina');
    }
  }

  private function imagensValidas($imagens)
  {
    $arquivosValidos = true;
    if (count($imagens)) {
      for ($i=0; $i < count($imagens); $i++) {
        if (isset($imagens[$i]['fileBase64'])) {
          if(!fileBase64Ext($imagens[$i]['fileBase64'],['png','jpg','jpeg'])) $arquivosValidos = false;
        } else if ($imagens[$i]['nm_imagem']) {
          $arrImgName = explode('.',$imagens[$i]['nm_imagem']);
          if(!in_array(end($arrImgName),['png','jpg','jpeg'])) $arquivosValidos = false;
        }
      }
    }

    return $arquivosValidos;
  }
}