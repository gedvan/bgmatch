<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PartidasController extends Controller {

  public function getLista() {
    $result = DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('p.*', 'g.nome AS nome_jogo', 'j.nome AS jogador', 'jp.posicao')
      ->orderBy('p.data', 'desc')
      ->orderBy('jp.posicao', 'asc')
      ->get();

    $partidas = [];
    foreach ($result as $row) {
      $id = $row->id;
      if (!isset($partidas[$id])) {
        $partidas[$id] = [
          'id'    => $row->id,
          'data'  => $row->data,
          'local' => $row->local,
          'id_jogo'   => $row->id_jogo,
          'nome_jogo' => $row->nome_jogo,
          'jogadores' => [],
        ];
      }
      $partidas[$id]['jogadores'][] = [
        'nome' => $row->jogador,
        'posicao' => $row->posicao,
      ];
    }

    return new JsonResponse(array_values($partidas));
  }

  public function getPartida(Request $request, $id) {

    $result = DB::table('partidas AS p')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->select('p.*', 'jp.id_jogador', 'jp.pontuacao', 'jp.posicao')
      ->where('p.id', $id)
      ->get();

    if ($result->count() == 0) {
      throw new NotFoundHttpException();
    }

    $partida = [];

    foreach ($result as $row) {
      if (!$partida) {
        $partida = [
          'id'      => $row->id,
          'id_jogo' => $row->id_jogo,
          'data'    => $row->data,
          'local'   => $row->local,
          'jogadores' => [],
        ];
      }
      $partida['jogadores'][] = [
        'id'        => $row->id_jogador,
        'pontuacao' => $row->pontuacao,
        'posicao'   => $row->posicao,
      ];
    }

    return new JsonResponse($partida);
  }

  public function getLocais() {
    $locais = DB::table('partidas')->distinct()->pluck('local');
    return new JsonResponse($locais);
  }

  public function postNovaPartida(Request $request) {
    $response = ['ok' => FALSE];

    try {
      DB::beginTransaction();

      $partida = [
        'id_jogo' => $request->input('id_jogo'),
        'data'    => $request->input('data'),
        'local'   => $request->input('local'),
      ];
      $partida['id'] = DB::table('partidas')->insertGetId($partida);

      foreach ($request->input('jogadores') as $jogador) {
        $jogador_partida = [
          'id_partida'  => $partida['id'],
          'id_jogador'  => $jogador['id'],
          'pontuacao'   => $jogador['pontuacao'],
          'posicao'     => $jogador['posicao']
        ];
        DB::table('jogadores_partidas')->insert($jogador_partida);
      }

      DB::commit();
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      DB::rollBack();
      throw new HttpException(500, $e->getMessage(), $e);
    }

    return new JsonResponse($response);
  }
}
