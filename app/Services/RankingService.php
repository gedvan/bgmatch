<?php

namespace App\Services;

use App\Services\JogosService;
use Illuminate\Support\Facades\DB;

class RankingService
{
  public function getTabelaPontuacao(string $ano): array
  {
    if ($ano < '2022') {
      return [
        JogosService::CATEGORIA_PESADO  => [1 => 10, 2 => 7, 3 => 4],
        JogosService::CATEGORIA_MEDIO   => [1 => 7,  2 => 4, 3 => 2],
        JogosService::CATEGORIA_LEVE    => [1 => 4,  2 => 2, 3 => 1],
        JogosService::CATEGORIA_PARTY_INFANTIL => [1 => 2,  2 => 1, 3 => 0],
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

  public function getPontuacaoJogadores(string $ano): array
  {
    $pontuacao = [];

    $result = DB::table('jogadores')->orderBy('nome')->get(['id', 'nome', 'cor']);
    foreach ($result as $row) {
      $pontuacao[$row->id] = [
        'id' => $row->id,
        'nome' => $row->nome,
        'cor' => $row->cor,
        'total' => 0,
        'semanal' => ["01-01" => 0],
        'mensal' => [],
      ];
    }

    $result = DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('p.data', 'g.categoria', 'j.id', 'jp.posicao')
      ->where('data', '>=', "$ano-01-01")
      ->where('data', '<=', "$ano-12-31")
      ->orderBy('p.data', 'asc')
      ->orderBy('g.nome', 'asc')
      ->orderBy('jp.posicao', 'asc')
      ->get();

    $tabela_pontuacao = $this->getTabelaPontuacao($ano);

    $dia_anterior = false;
    $mes_anterior = false;
    foreach ($result as $row) {
      $dia_jogo = date('m-d', strtotime("{$row->data} 12:00:00"));
      if ($dia_anterior && $dia_jogo != $dia_anterior) {
        foreach ($pontuacao as &$jogador) {
          $jogador['semanal'][$dia_anterior] = $jogador['total'];
        }
      }
      $dia_anterior = $dia_jogo;

      $mes_jogo = strftime('%b', strtotime("{$row->data} 12:00:00"));
      if ($mes_anterior && $mes_jogo != $mes_anterior) {
        foreach ($pontuacao as &$jogador) {
          $jogador['mensal'][$mes_anterior] = $jogador['total'];
        }
      }
      $mes_anterior = $mes_jogo;

      $pontos = $tabela_pontuacao[$row->categoria][$row->posicao] ?? 0;
      $pontuacao[$row->id]['total'] += $pontos;
    }
    foreach ($pontuacao as &$jogador) {
      $jogador['semanal'][$dia_anterior] = $jogador['total'];
      $jogador['mensal'][$mes_anterior] = $jogador['total'];
    }

    usort($pontuacao, function ($a, $b) {
      return $b['total'] - $a['total'];
    });

    return $pontuacao;
  }
}
