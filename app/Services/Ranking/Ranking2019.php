<?php

namespace App\Services\Ranking;

/**
 * Regra de 2019 a 2021: só as três primeiras posições pontuam, inclusive em
 * party/infantil. Expansões não pontuam.
 */
class Ranking2019 extends RegraTrilha
{
  public function nome(): string
  {
    return '2019';
  }

  protected function tabela(): array
  {
    return [
      1 => ['P' => 10, 'M' => 7, 'L' => 4, 'Y' => 2],
      2 => ['P' => 7,  'M' => 4, 'L' => 2, 'Y' => 1],
      3 => ['P' => 4,  'M' => 2, 'L' => 1, 'Y' => 0],
    ];
  }
}
