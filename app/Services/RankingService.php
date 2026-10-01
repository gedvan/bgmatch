<?php

namespace App\Services;

use App\Services\Ranking\Ranking2019;
use App\Services\Ranking\Ranking2022;
use App\Services\Ranking\Ranking2024;
use App\Services\Ranking\RegraRanking;
use Illuminate\Support\Facades\DB;

class RankingService
{
  /**
   * Primeiro ano com ranking.
   */
  const ANO_INICIAL = 2019;

  public function __construct(
    protected PartidasService $partidasService
  ) {}

  /**
   * Regra de pontuação em vigor num ano, ou null se o ano não tem ranking.
   */
  public function getRegra(int $ano): ?RegraRanking
  {
    if ($ano >= 2024) {
      return new Ranking2024();
    }
    if ($ano >= 2022) {
      return new Ranking2022();
    }
    if ($ano >= self::ANO_INICIAL) {
      return new Ranking2019();
    }
    return null;
  }

  /**
   * Calcula o ranking de um ano com a regra da época.
   *
   * @return array|null null se o ano não tem ranking.
   */
  public function getRanking(int $ano, ?\DateTimeImmutable $hoje = null): ?array
  {
    $regra = $this->getRegra($ano);
    if (!$regra) {
      return null;
    }

    $jogadores = DB::table('jogadores')
      ->orderBy('nome')
      ->get(['id', 'nome', 'cor'])
      ->map(fn($jogador) => (array) $jogador)
      ->all();

    $partidas = $this->partidasService->getPartidasPorPeriodo("$ano-01-01", "$ano-12-31", [
      'sort' => 'asc',
      'ranking' => true,
    ]);

    return ['ano' => $ano] + $regra->calcula($ano, $jogadores, $partidas, $hoje ?? new \DateTimeImmutable());
  }
}
