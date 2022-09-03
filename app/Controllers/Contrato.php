<?php

namespace App\Controllers;

use App\Controllers\API;

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

    // $html = file_get_contents($viewDirectory.'contrato/contrato.html');
    $html = view('contrato/contrato');

    // html que será transformado em PDF
    $dompdf->loadHtml($html);
    // (Opcional) Tipo do papel e orientação
    $dompdf->setPaper('A4');
    // Render HTML para PDF
    $dompdf->render();
    // Download do arquivo
    $dompdf->stream('EasyMAQ.pdf');
  }
}