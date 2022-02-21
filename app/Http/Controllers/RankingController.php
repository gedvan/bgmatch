<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RankingController extends Controller {

  /**
   * @var RankingService
   */
  protected $rankingService;

  public function __construct(RankingService $rankingService)
  {
    $this->rankingService = $rankingService;
  }

  /**
   * Valida o ano e retorna o valor default (ano atual) caso esteja vazio.
   *
   * @param string $ano
   * @return string
   */
  protected function getAnoDefault(string $ano = ''): string
  {
    if (empty($ano)) {
      $ano = date('Y');
    }
    if (!$ano || !preg_match('/^\d{4}$/', $ano)) {
      throw new NotFoundHttpException('Ano inválido');
    }
    return $ano;
  }

  /**
   * @param string $ano
   * @return JsonResponse
   */
  public function getPontuacao(string $ano = '')
  {
    $ano = $this->getAnoDefault($ano);
    $pontuacao = $this->rankingService->getPontuacaoJogadores($ano);

    return response()->json(array_values($pontuacao));
  }

  /**
   * @param string $ano
   * @return JsonResponse
   */
  public function getTabelaPontuacao(string $ano = '')
  {
    $ano = $this->getAnoDefault($ano);

    $tabela = $this->rankingService->getTabelaPontuacao($ano);
    $pontuacao = array();

    // Transpõe a tabela de pontuação de [peso][posicao] para [posicao][peso]
    foreach($tabela as $peso => $pontosPeso) {
      foreach ($pontosPeso as $posicao => $pontos) {
        $index = $posicao - 1;
        if (!isset($pontuacao[$index])) {
          $pontuacao[$index] = [
            'posicao' => $posicao,
            'pontuacao' => [],
          ];
        }
        $pontuacao[$index]['pontuacao'][$peso] = $pontos;
      }
    }

    return response()->json($pontuacao);
  }

}
