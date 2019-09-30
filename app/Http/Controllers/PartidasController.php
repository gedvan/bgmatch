<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartidasController extends Controller {

  public function getLista() {
    $result = DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id_ludo')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('p.*', 'g.nome AS nome_jogo', 'j.nome AS jogador', 'jp.vencedor')
      ->orderBy('p.data', 'desc')
      ->orderBy('jp.vencedor', 'desc')
      ->orderBy('j.nome', 'asc')
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
        'vencedor' => $row->vencedor,
      ];
    }

    return new JsonResponse(array_values($partidas));
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
          'vencedor'    => $jogador['vencedor']
        ];
        DB::table('jogadores_partidas')->insert($jogador_partida);
      }

      DB::commit();
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      DB::rollBack();
      $response['ok'] = FALSE;
      $response['msg'] = $e->getMessage();
    }

    return new JsonResponse($response);
  }
}
