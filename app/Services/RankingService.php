<?php

namespace App\Services;

use App\Services\JogosService;

class RankingService
{
  public function getTabelaPontuacao($ano)
  {
    if ($ano < '2022') {
      return [
        JogosService::CATEGORIA_PESADO  => [1 => 10, 2 => 7, 3 => 4],
        JogosService::CATEGORIA_MEDIO   => [1 => 7,  2 => 4, 3 => 2],
        JogosService::CATEGORIA_LEVE    => [1 => 4,  2 => 2, 3 => 1],
        JogosService::CATEGORIA_PARTY   => [1 => 2,  2 => 1, 3 => 0],
      ];
    }
    else {
      return [
        JogosService::CATEGORIA_PESADO  => [1 => 10, 2 => 7, 3 => 4, 4 => 2, 5 => 1, 6 => 1],
        JogosService::CATEGORIA_MEDIO   => [1 => 7,  2 => 4, 3 => 2, 4 => 1, 5 => 1, 6 => 1],
        JogosService::CATEGORIA_LEVE    => [1 => 4,  2 => 2, 3 => 1, 4 => 1, 5 => 1, 6 => 1],
      ];
    }
  }
}
