<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RankingController extends Controller {

  /**
   * @param string $ano
   * @return mixed
   */
  public function getDados($ano = NULL) {
    if (empty($ano)) {
      $ano = date('Y');
    }
    if (!preg_match('/^\d{4}$/', $ano)) {
      throw new NotFoundHttpException('Ano inválido');
    }

    $result = DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('g.categoria', 'j.id', 'jp.posicao')
      ->where('jp.posicao', '<=', 3)
      ->where('data', '>=', "$ano-01-01")
      ->where('data', '<=', "$ano-12-31")
      ->orderBy('p.data', 'asc')
      ->orderBy('g.nome', 'asc')
      ->orderBy('jp.posicao', 'asc')
      ->get();

    $tabela_pontuacao = [
      JogosController::CATEGORIA_PESADO => [1 => 10, 2 => 7, 3 => 4],
      JogosController::CATEGORIA_MEDIO  => [1 => 7,  2 => 4, 3 => 2],
      JogosController::CATEGORIA_LEVE   => [1 => 4,  2 => 2, 3 => 1],
      JogosController::CATEGORIA_PARTY  => [1 => 2,  2 => 1, 3 => 0],
    ];

    $pontuacao = [];
    foreach ($result as $row) {
      $id_jogador = $row->id;
      if (empty($pontuacao[$id_jogador])) {
        $pontuacao[$id_jogador] = [
          'id' => $id_jogador,
          'pontuacao' => 0,
        ];
      }
      if (!isset($tabela_pontuacao[$row->categoria][$row->posicao])) {
        throw new HttpException(500, 'Erro de mapeamento de categorias/pontuação.');
      }
      $pontos = $tabela_pontuacao[$row->categoria][$row->posicao];
      $pontuacao[$id_jogador]['pontuacao'] += $pontos;
    }

    usort($pontuacao, function ($a, $b) {
      return $b['pontuacao'] - $a['pontuacao'];
    });

    return new JsonResponse(array_values($pontuacao));
  }

}
