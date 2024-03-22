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
      ->leftJoin('jogos AS e', 'p.id_expansao', '=', 'e.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select(
        'p.*',
        'g.nome AS nome_jogo',
        'g.bgg_weight AS peso_jogo',
        'g.categoria AS categoria_jogo',
        'e.nome AS nome_expansao',
        'e.bgg_weight AS peso_expansao',
        'j.id AS id_jogador',
        'j.nome AS nome_jogador',
        'jp.posicao',
        'jp.pontuacao'
      )
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
          'ranking' => $row->ranking,
          'jogo' => [
            'id' => $row->id_jogo,
            'nome' => $row->nome_jogo,
            'peso' => $row->peso_jogo,
            'categoria' => $row->categoria_jogo,
          ],
          'expansao' => null,
          'jogadores' => [],
        ];
        if ($row->id_expansao) {
          $partidas[$id]['expansao'] = [
            'id' => $row->id_expansao,
            'nome' => $row->nome_expansao,
            'peso' => $row->peso_expansao,
          ];
        }
      }
      $partidas[$id]['jogadores'][] = [
        'id' => $row->id_jogador,
        'nome' => $row->nome_jogador,
        'posicao' => $row->posicao,
        'pontuacao' => $row->pontuacao,
      ];
    }

    return array_values($partidas);
  }

  public function getPartida(int $id): array
  {
    $query = $this->getBaseQuery()->where('p.id', '=', $id);
    return $this->agrupaPartidas($query->get());
  }

  /**
   * Consulta e retorna a lista de partidas dentro de um período de datas.
   *
   * @param string $inicio Formato: YYYY-MM-DD
   * @param string $fim Formato: YYYY-MM-DD
   * @param string $sort
   * @return array
   */
  public function getPartidasPorPeriodo(string $inicio = '', string $fim = '', $options = []): array
  {
    $sort = $options['sort'] ?? 'asc';
    $query = $this->getBaseQuery($sort);

    if ($inicio) {
      $query->where('p.data', '>=', $inicio);
    }
    if ($fim) {
      $query->where('p.data', '<=', $fim);
    }
    if (isset($options['ranking'])) {
      $query->where('p.ranking', '=', (bool) $options['ranking']);
    }

    return $this->agrupaPartidas($query->get());
  }

  public function getLocaisPartidas(): array
  {
    return DB::table('partidas')->distinct()->pluck('local')->toArray();
  }

  /**
   * @throws \Exception
   */
  public function salvaPartida(array $partida): void
  {
    try {
      DB::beginTransaction();

      $dadosPartida = [
        'id_jogo' => $partida['id_jogo'],
        'data'    => $partida['data'],
        'local'   => $partida['local'],
        'ranking' => $partida['ranking'],
        'id_expansao' => $partida['id_expansao'],
      ];

      if (empty($partida['id'])) {
        // Nova partida - insere um novo registro.
        $partida['id'] = DB::table('partidas')->insertGetId($dadosPartida);
      }
      else {
        // Partida existente - atualiza o registro existente e exclui os jogadores da partida.
        DB::table('partidas')->where('id', $partida['id'])->update($dadosPartida);
        DB::table('jogadores_partidas')->where('id_partida', $partida['id'])->delete();
      }

      // Insere os jogadores da partida.
      foreach ($partida['jogadores'] as $jogador) {
        DB::table('jogadores_partidas')->insert([
          'id_partida'  => $partida['id'],
          'id_jogador'  => $jogador['id'],
          'pontuacao'   => $jogador['pontuacao'],
          'posicao'     => $jogador['posicao'],
        ]);
      }

      DB::commit();
    }
    catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Erro ao salvar a partida.', 0, $e);
    }
  }

  public function excluiPartida(int $id): bool
  {
    return (bool) DB::table('partidas')->where('id', $id)->delete();
  }
}
