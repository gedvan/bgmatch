<?php

namespace App\Services\Ranking;

/**
 * Regra de 2022 e 2023: pontuam jogos pesados, médios e leves, até a 6ª
 * posição. Party/infantil e expansões deixam de pontuar.
 */
class Ranking2022 extends RegraTrilha
{
  public function nome(): string
  {
    return '2022';
  }

  protected function tabela(): array
  {
    return [
      1 => ['P' => 10, 'M' => 7, 'L' => 4],
      2 => ['P' => 7,  'M' => 4, 'L' => 2],
      3 => ['P' => 4,  'M' => 2, 'L' => 1],
      4 => ['P' => 2,  'M' => 1, 'L' => 1],
      5 => ['P' => 1,  'M' => 1, 'L' => 1],
      6 => ['P' => 1,  'M' => 1, 'L' => 1],
    ];
  }
}
