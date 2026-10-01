<?php

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RankingController extends Controller {

  public function __construct(
    protected RankingService $rankingService
  ) {}

  /**
   * Ranking de um ano, calculado com a regra em vigor naquele ano.
   *
   * @param string $ano
   * @return JsonResponse
   */
  public function getRanking(string $ano): JsonResponse
  {
    if (!preg_match('/^\d{4}$/', $ano)) {
      throw new NotFoundHttpException('Ano inválido');
    }

    $ranking = $this->rankingService->getRanking((int) $ano);
    if ($ranking === null) {
      throw new NotFoundHttpException('Não há ranking para este ano');
    }

    return new JsonResponse($ranking);
  }

}
