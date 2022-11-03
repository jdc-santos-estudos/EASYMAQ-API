<?php

namespace App\Controllers;

use App\Controllers\API;

use App\Models\Configuracao_model;

use Dompdf\Dompdf;

class Contrato extends API
{
  public function showPDF()
  {
    return view('contrato/contrato');
  }

  public function downloadPDF()
  {
    $dompdf = new Dompdf();

    // instanciando um objeto da classe Usuario_model
    $config = new Configuracao_model();

    // chamando a função de logar do usuário
    $config = $config->getConfig(['config' => 'TEMPLATE_CONTRATO']);

    if (count($config) === 1) {
      $config[0]['ds_valor'] = stripslashes($config[0]['ds_valor']);
      $html = json_decode($config[0]['ds_valor'],1);
    }

    // para carregar no navegador
    $dompdf->loadHtml($html);
    // $dompdf->setPaper('A4');
    $dompdf->render();
    $dompdf->stream('EasyMAQ.pdf', array("Attachment" => false));

    // para salvar no servidor
    // $dompdf->loadHtml($html);
    // $dompdf->setPaper('A4');
    // $dompdf->render();
    // $output = $dompdf->output();
    // file_put_contents('../../contratos/algumacoisa.pdf', $output);   
  }

  public function docusign() {
    
  }

  public function docusignCallback() {
    try {
      return $this->HttpSuccess([],'callback OK');
    } catch(\Exception $e) {
      //retornando mensagem de erro interno
      return $this->HttpError500([], $e, $e->getMessage(), 'Erro interno callback do docusign.');
    }
  }
}