<?php

namespace App\Http\Controllers;

use App\Services\PartidasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PartidasController extends Controller {

  public function __construct(
    protected PartidasService $partidasService)
  { }

  public function lista(Request $request, string $periodo = ''): HttpResponse
  {
    $sort = $request->input('sort');
    if (!$sort) {
      $sort = 'asc';
    } elseif (!in_array($sort, ['asc', 'desc'])) {
      throw new HttpException(500, 'Invalid sort parameter.');
    }

    $data_inicio = '';
    $data_fim = '';

    if ($periodo) {
      if (str_contains($periodo, ':')) {
        [$inicio, $fim] = explode(':', $periodo);

        if ($inicio) {
          if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $inicio, $m)) {
            throw new HttpException(500, 'Formato de data inválido (data inicial).');
          }
          $ano_inicio = $m[1];
          $mes_inicio = $m[2] ?? '01';
          $dia_inicio = $m[3] ?? '01';

          if (!checkdate($mes_inicio, $dia_inicio, $ano_inicio)) {
            throw new HttpException(500, 'Data inválida (data inicial).');
          }
          $data_inicio = "$ano_inicio-$mes_inicio-$dia_inicio";
        }

        if ($fim) {
          if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $fim, $m)) {
            throw new HttpException(500, 'Formato de data inválido (data final).');
          }
          $ano_fim = $m[1];
          $mes_fim = $m[2] ?? '12';
          $dia_fim = $m[3] ?? (new \DateTime("$ano_fim-$mes_fim-01"))->format('t');

          if (!checkdate($mes_fim, $dia_fim, $ano_fim)) {
            throw new HttpException(500, 'Data inválida (data final).');
          }
          $data_fim = "$ano_fim-$mes_fim-$dia_fim";
        }
      }
      else {
        if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $periodo, $m)) {
          throw new HttpException(500, 'Formato de data inválido.');
        }
        $ano_inicio = $m[1];
        $mes_inicio = $m[2] ?? '01';
        $dia_inicio = $m[3] ?? '01';

        if (!checkdate($mes_inicio, $dia_inicio, $ano_inicio)) {
          throw new HttpException(500, 'Data inválida.');
        }

        $ano_fim = $ano_inicio;
        $mes_fim = $m[2] ?? '12';
        $dia_fim = $m[3] ?? (new \DateTime("$ano_fim-$mes_fim-01"))->format('t');

        $data_inicio = "$ano_inicio-$mes_inicio-$dia_inicio";
        $data_fim = "$ano_fim-$mes_fim-$dia_fim";
      }
    }
    $partidas = $this->partidasService->getPartidasPorPeriodo($data_inicio, $data_fim, $sort);

    return new JsonResponse($partidas);
  }

  public function getLista() {
    $result = DB::table('partidas AS p')
      ->join('jogos AS g', 'p.id_jogo', '=', 'g.id')
      ->leftJoin('jogos AS e', 'p.id_expansao', '=', 'e.id')
      ->join('jogadores_partidas AS jp', 'jp.id_partida', '=', 'p.id')
      ->join('jogadores AS j', 'jp.id_jogador', '=', 'j.id')
      ->select('p.*', 'g.nome AS nome_jogo', 'e.nome AS nome_expansao',
        'j.id AS id_jogador', 'j.nome AS nome_jogador', 'jp.posicao')
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
          'id_jogo'     => $row->id_jogo,
          'nome_jogo' => $row->nome_jogo,
          'id_expansao' => $row->id_expansao,
          'nome_expansao' => $row->nome_expansao,
          'jogadores' => [],
        ];
      }
      $partidas[$id]['jogadores'][] = [
        'id' => $row->id_jogador,
        'nome' => $row->nome_jogador,
        'posicao' => $row->posicao,
      ];
    }

    return new JsonResponse(array_values($partidas));
  }

  public function getPartida($id)
  {
    $partida = $this->partidasService->getPartida($id);

    if (empty($partida)) {
      throw new NotFoundHttpException();
    }

    return new JsonResponse($partida);
  }

  public function getLocais() {
    $locais = $this->partidasService->getLocaisPartidas();
    return new JsonResponse($locais);
  }

  public function postNovaPartida(Request $request) {
    $response = ['ok' => FALSE];

    try {
      $this->partidasService->salvaPartida($request->input());
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }

    return new JsonResponse($response);
  }

  public function postAtualizaPartida(Request $request, $id) {
    $response = ['ok' => FALSE];

    try {
      $partida = $request->input() + ['id' => $id];
      $this->partidasService->salvaPartida($partida);
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }

    return new JsonResponse($response);
  }

  public function postExcluirPartida(int $id) {
    try {
      $delete = $this->partidasService->excluiPartida($id);
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
