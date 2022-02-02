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
   * @param string $ano
   * @return mixed
   */
  public function getDados($ano = NULL)
  {
    if (empty($ano)) {
      $ano = date('Y');
    }
    if (!preg_match('/^\d{4}$/', $ano)) {
      throw new NotFoundHttpException('Ano inválido');
    }

    $jogadores = [];
    $result = DB::table('jogadores')->orderBy('nome')->get(['id', 'nome', 'cor']);
    foreach ($result as $row) {
      $jogadores[$row->id] = [
        'id' => $row->id,
        'nome' => $row->nome,
        'cor' => $row->cor,
        'total' => 0,
        'semanal' => [],
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

    $tabela_pontuacao = $this->rankingService->getTabelaPontuacao($ano);

    $dia_anterior = false;
    $mes_anterior = false;
    foreach ($result as $row) {
      $dia_jogo = date('m-d', strtotime("{$row->data} 12:00:00"));
      if ($dia_anterior && $dia_jogo != $dia_anterior) {
        foreach ($jogadores as &$jogador) {
          $jogador['semanal'][$dia_anterior] = $jogador['total'];
        }
      }
      $dia_anterior = $dia_jogo;

      $mes_jogo = strftime('%b', strtotime("{$row->data} 12:00:00"));
      if ($mes_anterior && $mes_jogo != $mes_anterior) {
        foreach ($jogadores as &$jogador) {
          $jogador['mensal'][$mes_anterior] = $jogador['total'];
        }
      }
      $mes_anterior = $mes_jogo;

      $pontos = $tabela_pontuacao[$row->categoria][$row->posicao] ?? 0;
      $jogadores[$row->id]['total'] += $pontos;
    }
    foreach ($jogadores as &$jogador) {
      $jogador['semanal'][$dia_anterior] = $jogador['total'];
      $jogador['mensal'][$mes_anterior] = $jogador['total'];
    }

    usort($jogadores, function ($a, $b) {
      return $b['total'] - $a['total'];
    });

    return new JsonResponse(array_values($jogadores));
  }

}
