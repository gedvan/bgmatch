<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JogadoresController extends Controller {

  public function getJogadores() {
    $jogadores = DB::table('jogadores')->get();
    return new JsonResponse($jogadores);
  }

  public function getDadosJogadores() {

    $queryJogadores = <<<SQL
SELECT
    j.id, j.nome,
    COUNT(jp.id_partida) AS num_partidas,
    COUNT(jp.posicao) FILTER (WHERE jp.posicao = 1) AS num_vitorias
FROM jogadores j
LEFT JOIN jogadores_partidas jp ON jp.id_jogador = j.id
GROUP BY j.id, j.nome
ORDER BY j.nome
SQL;

    $queryVitorias = <<<SQL
SELECT COUNT(p.id) AS qtd, j.nome AS nome_jogo
FROM jogadores_partidas jp
JOIN partidas p ON jp.id_partida = p.id
JOIN jogos j ON p.id_jogo = j.id
WHERE jp.id_jogador = ? AND jp.posicao = 1
GROUP BY j.nome
ORDER BY qtd DESC, max(p.data) DESC
LIMIT 5;
SQL;

    $queryPosicoes = <<<SQL
SELECT jp.posicao, COUNT(p.id) AS quantidade
FROM jogadores_partidas jp
JOIN partidas p ON jp.id_partida = p.id
WHERE jp.id_jogador = ?
GROUP BY jp.posicao
ORDER BY jp.posicao ASC
SQL;

    $jogadores = DB::select($queryJogadores);

    foreach ($jogadores as &$jogador) {
      $jogador->vitorias = DB::select($queryVitorias, [$jogador->id]);
      $jogador->resultados = DB::select($queryPosicoes, [$jogador->id]);
    }

    return new JsonResponse($jogadores);
  }
}
