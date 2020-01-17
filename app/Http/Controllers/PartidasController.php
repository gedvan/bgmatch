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
          'posicao'     => $jogador['posicao'],
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

  public function importa(Request $request) {

    $json = json_decode(file_get_contents(__DIR__.'/../../../public/BGStatsExport.json'));

    $mapJogadores = [
      16 => 1, // Gedvan
      30 => 2, // Fechine
      9  => 3, // Bruno
      2  => 4, // Rodrigo
      46 => 5, // Matheus
    ];
    $mapLocais = [
      3 => 'Casa de Rodrigo',
      4 => 'Casa de Bruno',
      7 => 'Casa de Fechine',
      8 => 'Casa de Gedvan',
    ];
    $mapJogos = require __DIR__ . '/../../../resources/map-jogos.php';

    $partidas = [];
    foreach ($json->plays as $play) {

      // Local da partida
      if (empty($play->locationRefId) || !isset($mapLocais[$play->locationRefId])) {
        // Se o jogo não foi em uma das casas dos jogadores do Grupo, ignora
        continue;
      }
      $local = $mapLocais[$play->locationRefId];

      // Data da partida
      $data = explode(' ', $play->playDate)[0];

      // Jogo
      $jogo = $mapJogos[$play->gameRefId] ?? null;
      if (!$jogo) {
        // Não foi possível determinar o jogo
        continue;
      }

      $partida = [
        'id_jogo' => $jogo,
        'data' => $data,
        'local' => $local,
      ];

      $jogadores = [];
      $ok = TRUE;
      foreach ($play->playerScores as $player) {
        if (!isset($mapJogadores[$player->playerRefId])) {
          $ok = FALSE;
          break;
        }
        $id_jogador = $mapJogadores[$player->playerRefId];
        if (preg_match_all('/[-+]?\d+/', $player->score, $m)) {
          $pontuacao = array_sum(array_map('intval', $m[0]));
        }
        else {
          $ok = FALSE;
          break;
        }
        $jogadores[] = [
          'id_jogador' => $id_jogador,
          'pontuacao'  => $pontuacao,
          'posicao'    => $player->rank,
        ];
      }
      if (!$ok || count($jogadores) < 3) {
        continue;
      }

      try {
        DB::beginTransaction();

        $id_partida = DB::table('partidas')->insertGetId($partida);

        foreach ($jogadores as &$jogador) {
          $jogador['id_partida'] = $id_partida;
          DB::table('jogadores_partidas')->insert($jogador);
        }

        DB::commit();
      }
      catch (\Exception $e) {
        DB::rollBack();
        throw new HttpException(500, $e->getMessage(), $e);
      }
      $partida['jogadores'] = $jogadores;
      $partidas[] = $partida;
    }

    return new JsonResponse($partidas);
  }
}
