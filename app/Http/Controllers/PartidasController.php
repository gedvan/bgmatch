<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
      ->orderBy('g.nome', 'asc')
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

  public function postAtualizaPartida(Request $request, $id) {
    $response = ['ok' => FALSE];

    try {
      DB::beginTransaction();

      $partida = [
        'id_jogo' => $request->input('id_jogo'),
        'data'    => $request->input('data'),
        'local'   => $request->input('local'),
      ];
      DB::table('partidas')->where('id', $id)->update($partida);
      DB::table('jogadores_partidas')->where('id_partida', $id)->delete();

      foreach ($request->input('jogadores') as $jogador) {
        $jogador_partida = [
          'id_partida'  => $id,
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

  public function postExcluirPartida(Request $request, $id) {
    try {
      $delete = DB::table('partidas')->where('id', $id)->delete();
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }
    return new JsonResponse($delete);
  }

  public function bruno() {
    $json = json_decode(file_get_contents(__DIR__.'/../../../public/BGStatsExport_Bruno.json'));

    $refs = ['locations' => [], 'games' => [], 'players' => []];
    foreach (array_keys($refs) as $refkey) {
      foreach ($json->$refkey as $ref) {
        $refs[$refkey][$ref->id] = $ref;
      }
    }

    $grupo = [2, 3, 4, 6, 23];

    $plays = [];
    foreach ($json->plays as $play) {
      $data = explode(' ', $play->playDate)[0];
      $jogo = $refs['games'][$play->gameRefId];
      $local = !empty($play->locationRefId) ? $refs['locations'][$play->locationRefId]->name : '';
      $jogadores = [];
      foreach ($play->playerScores as $playerScore) {
        if (!in_array($playerScore->playerRefId, $grupo)) {
          continue 2;
        }
        $jogador = [
          'nome' => $refs['players'][$playerScore->playerRefId]->name,
          'pontuacao' => $playerScore->score,
          'posicao' => $playerScore->rank,
        ];
        $jogadores[] = $jogador['nome'] . ':' . $jogador['pontuacao'];
      }
      sort($jogadores);
      $plays[] = "$data,$jogo->name,$local," . implode(',', $jogadores);
    }
    sort($plays);

    echo implode("\n", $plays);
  }

  public function rodrigo() {
    $json = json_decode(file_get_contents(__DIR__.'/../../../public/BGStatsExport.json'));

    $refs = ['locations' => [], 'games' => [], 'players' => []];
    foreach (array_keys($refs) as $refkey) {
      foreach ($json->$refkey as $ref) {
        $refs[$refkey][$ref->id] = $ref;
      }
    }

    $grupo = [2, 9, 16, 30, 46];

    $plays = [];
    foreach ($json->plays as $play) {
      $data = explode(' ', $play->playDate)[0];
      $jogo = $refs['games'][$play->gameRefId];
      $local = !empty($play->locationRefId) ? $refs['locations'][$play->locationRefId]->name : '';
      $jogadores = [];
      foreach ($play->playerScores as $playerScore) {
        if (!in_array($playerScore->playerRefId, $grupo)) {
          continue 2;
        }
        $letra = $refs['players'][$playerScore->playerRefId]->name[0];
        $jogadores[] = "$playerScore->rank:$letra:$playerScore->score";
      }
      sort($jogadores);
      $plays[] = "$data,$jogo->name,$local," . implode(',', $jogadores);
    }
    sort($plays);

    echo '<pre>' . implode("\n", $plays) . '</pre>';
  }

  public function importa() {
    $file = new \SplFileObject(__DIR__ . '/../../../resources/jogos.csv');

    $jogos_map = [];
    $jogos_nao_encontrados = [];
    while (!$file->eof()) {
      $line = $file->fgetcsv();
      if (!$line || empty($line[1])) {
        continue;
      }
      $nome_jogo = $line[1];
      if (!isset($jogos_map[$nome_jogo])) {
        $id_jogo = DB::table('jogos')->where('nome', $nome_jogo)->value('id');
        if ($id_jogo) {
          $jogos_map[$nome_jogo] = $id_jogo;
        }
        else {
          $jogos_nao_encontrados[] = $nome_jogo;
        }
      }
    }

    if (count($jogos_nao_encontrados)) {
      var_dump($jogos_nao_encontrados);
      throw new HttpException(500, 'Um ou mais jogos não foram encontrados');
    }

    $jogadores_map = [
      'B' => 3,
      'F' => 2,
      'G' => 1,
      'M' => 5,
      'R' => 4,
    ];

    $file->rewind();
    $count = 0;
    while (!$file->eof()) {
      $line = $file->fgetcsv();
      if (empty($line) || empty($line[1])) {
        continue;
      }
      DB::transaction(function() use (&$jogos_map, &$count, &$line, &$jogadores_map) {
        $partida = [
          'id_jogo' => $jogos_map[$line[1]],
          'data' => $line[0],
          'local' => $line[2],
        ];
        $id_partida = DB::table('partidas')->insertGetId($partida);
        $count++;

        for ($i = 3; $i < count($line); $i++) {
          [$posicao, $letra, $pontuacao] = explode(':', $line[$i]);
          $id_jogador = $jogadores_map[$letra];
          $jogador_partida = [
            'id_partida' => $id_partida,
            'id_jogador' => $id_jogador,
            'pontuacao' => $pontuacao,
            'posicao' => $posicao,
          ];
          DB::table('jogadores_partidas')->insert($jogador_partida);
        }
      });
    }

    return new Response($count . ' partidas inseridas');
  }
}
