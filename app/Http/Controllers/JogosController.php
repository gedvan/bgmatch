<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ParentNotFoundException;

class JogosController extends Controller {

  const LUDOPEDIA_URL = 'https://www.ludopedia.com.br';

  const CATEGORIA_PESADO    = 'P';
  const CATEGORIA_MEDIO     = 'M';
  const CATEGORIA_LEVE      = 'L';
  const CATEGORIA_PARTY     = 'F';
  const CATEGORIA_INFANTIL  = 'I';
  const CATEGORIA_COOP      = 'C';
  const CATEGORIA_EXPANSAO  = 'X';

  const CATEGORIAS = [
    self::CATEGORIA_PESADO    => 'Pesado',
    self::CATEGORIA_MEDIO     => 'Médio',
    self::CATEGORIA_LEVE      => 'Leve',
    self::CATEGORIA_PARTY     => 'Party game',
    self::CATEGORIA_INFANTIL  => 'Infantil',
    self::CATEGORIA_COOP      => 'Cooperativo',
  ];

  /**
   * Endpoint que retorna a lista completa dos jogos salvos no banco próprio.
   *
   * @param  Request  $request
   * @return JsonResponse
   */
  public function getLista(Request $request): JsonResponse
  {
    $rows = DB::table('jogos')->orderByRaw('id_base NULLS FIRST')->orderBy('nome')->get();

    $jogos = [];
    foreach ($rows as $row) {
      if (empty($row->id_base)) {
        $jogos[$row->id] = $row;
        $jogos[$row->id]->expansoes = [];
      } elseif (isset($jogos[$row->id_base])) {
        $jogos[$row->id_base]->expansoes[] = $row;
      }
    }

    return new JsonResponse(array_values($jogos));
  }

  /**
   * Endpoint para atualizar a categoria de um jogo específico.
   *
   * @param  Request  $request
   * @param  string   $id  ID do jogo a ser atualizado
   * @return JsonResponse
   */
  public function postAtualizaCategoria(Request $request, $id): JsonResponse
  {
    // A nova categoria deve vir no corpo da requisição
    $categoria = $request->input('categoria');

    $upd = DB::table('jogos')->where('id', $id)->update(['categoria' => $categoria]);

    return new JsonResponse(['updated' => $upd]);
  }

  /**
   * Endpoint que retorna a lista completa de jogos do grupo na Ludopedia.
   *
   * @param  Request  $request
   * @return JsonResponse
   */
  public function getJogosLudopedia(Request $request): JsonResponse
  {
    $jogos = [];
    $dom = new Dom();

    $usuarios = DB::table('jogadores')->pluck('user_ludo');

    foreach (['base', 'expansao'] as $tipo) {
      $jogos[$tipo] = [];
      foreach ($usuarios as $usuario) {
        $pagina = 1;
        while ($pagina) {
          $url = self::LUDOPEDIA_URL."/colecao?usuario=$usuario&tipo_jogo=$tipo";
          if ($pagina > 1) {
            $url .= "&pagina=$pagina";
          }

          $dom->loadFromUrl($url);

          $links = $dom->find('#page-content .panel-body .media .media-heading a');
          foreach ($links as $link) {
            $urlJogo = $link->getAttribute('href');
            $jogos[$tipo][] = array_slice(explode('/', $urlJogo), -1)[0];
          }

          $pagina = false;
          if ($pagination = $dom->find('#page-content ul.pagination li.active', 0)) {
            try {
              $nextPageUrl = $pagination->nextSibling()->find('a', 0)->getAttribute('href');

              if (preg_match('/pagina=(\d+)/', $nextPageUrl, $matches)) {
                $pagina = (int) $matches[1];
              }
            } catch (ParentNotFoundException $exception) {
            }
          }
        }
      }
      $jogos[$tipo] = array_unique($jogos[$tipo]);
      sort($jogos[$tipo]);
    }

    return new JsonResponse($jogos);
  }

  /**
   * Endpoint para atualizar os dados de um jogo no banco de dados local, com base nas informações da Ludopedia.
   *
   * @param  string  $slug  Identificador textual do jogo na ludopedia (utilizado nas URLs)
   * @return JsonResponse
   */
  public function postAtualizaJogo(string $slug): JsonResponse
  {
    $urlJogo = self::LUDOPEDIA_URL.'/jogo/'.$slug;

    $dom = new Dom();
    $dom->loadFromUrl($urlJogo);

    // Informações básicas

    $jogo = [
      'id' => (int) $dom->find('#id_jogo', 0)->getAttribute('value'),
      'nome' => $dom->find('#nm_jogo', 0)->getAttribute('value'),
      'imagem' => $dom->find('#img-capa', 0)->getAttribute('src'),
      'slug' => $slug,
    ];

    // Número de jogadores

    $jogadores = $dom->find('#page-content .jogo-top-main ul.list-inline li', 2)->text;

    if (preg_match('/(\d+) a (\d+) jogadores/', $jogadores, $m)) {
      $jogo['min'] = $m[1];
      $jogo['max'] = $m[2];
    } elseif (preg_match('/(\d+) jogador(es)?/', $jogadores, $m)) {
      $jogo['min'] = $m[1];
      $jogo['max'] = $m[1];
    }

    // Categoria / Expansão

    $linkExpansao = $dom->find('#page-content .jogo-top-main > h5 a', 0);
    if ($linkExpansao) {
      $slugExpansao = array_slice(explode('/', $linkExpansao->getAttribute('href')), -1)[0];
      $idJogoBase = DB::table('jogos')->where('slug', $slugExpansao)->value('id');

      $jogo['categoria'] = self::CATEGORIA_EXPANSAO;
      $jogo['id_base'] = $idJogoBase;
    } else {
      $boxInfo = $dom->find('#bloco-descricao-sm .col-sm-3 .bg-gray-light', 0);

      if ($boxInfo->find('a[href$="mecanica/20"]', 0)) { // Mecânica: Cooperativo
        $jogo['categoria'] = self::CATEGORIA_COOP;
      } elseif ($boxInfo->find('a[href$="dominio/6"]', 0)) { // Domínio: Jogos Infantis
        $jogo['categoria'] = self::CATEGORIA_INFANTIL;
      } elseif ($boxInfo->find('a[href$="categoria/113"]', 0)) { // Categoria: Jogos Festivos
        $jogo['categoria'] = self::CATEGORIA_PARTY;
      } elseif ($boxInfo->find('a[href$="dominio/9"]', 0)) { // Domínio: Jogos Expert
        $jogo['categoria'] = self::CATEGORIA_PESADO;
      } else {
        $jogo['categoria'] = self::CATEGORIA_MEDIO;
      }
    }

    DB::table('jogos')->updateOrInsert(['id' => $jogo['id']], $jogo);

    return new JsonResponse($jogo);
  }

}
