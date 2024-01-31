<?php

namespace App\Services;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PartidasService
{
  protected function getBaseQuery($sort = 'asc'): QueryBuilder
  {
    return DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('p.*', 'g.nome AS nome_jogo', 'g.categoria AS categoria_jogo', 'j.id AS id_jogador', 'j.nome AS nome_jogador', 'jp.posicao')
      ->orderBy('p.data', $sort)
      ->orderBy('g.nome', 'asc')
      ->orderBy('jp.posicao', 'asc');
  }

  protected function agrupaPartidas(Collection $rows): array
  {
    $partidas = [];

    foreach ($rows as $row) {
      $id = $row->id;
      if (!isset($partidas[$id])) {
        $partidas[$id] = [
          'id'    => $row->id,
          'data'  => $row->data,
          'local' => $row->local,
          'jogo' => [
            'id' => $row->id_jogo,
            'nome' => $row->nome_jogo,
            'categoria' => $row->categoria_jogo,
          ],
          'jogadores' => [],
        ];
      }
      $partidas[$id]['jogadores'][] = [
        'id' => $row->id_jogador,
        'nome' => $row->nome_jogador,
        'posicao' => $row->posicao,
      ];
    }

    return array_values($partidas);
  }

  /**
   * Consulta e retorna a lista de partidas dentro de um período de datas.
   *
   * @param string $inicio  Formato: YYYY-MM-DD
   * @param string $fim     Formato: YYYY-MM-DD
   * @return array
   */
  public function getPartidasPorPeriodo(string $inicio = '', string $fim = '', $sort = 'asc'): array
  {
    $query = $this->getBaseQuery($sort);

    if ($inicio) {
      $query->where('p.data', '>=', $inicio);
    }
    if ($fim) {
      $query->where('p.data', '<=', $fim);
    }

    return $this->agrupaPartidas($query->get());
  }
}
