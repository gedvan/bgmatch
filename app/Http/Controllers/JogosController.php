<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JogosController extends Controller {

  const LUDOPEDIA_URL = 'https://www.ludopedia.com.br';

  const CATEGORIA_PESADO    = 'P';
  const CATEGORIA_MEDIO     = 'M';
  const CATEGORIA_LEVE      = 'L';
  const CATEGORIA_PARTY     = 'F';
  const CATEGORIA_INFANTIL  = 'I';
  const CATEGORIA_EXPANSAO  = 'X';

  const CATEGORIAS = [
    self::CATEGORIA_PESADO    => 'Pesado',
    self::CATEGORIA_MEDIO     => 'Médio',
    self::CATEGORIA_LEVE      => 'Leve',
    self::CATEGORIA_PARTY     => 'Party game',
    self::CATEGORIA_INFANTIL  => 'Infantil',
    self::CATEGORIA_EXPANSAO  => 'Expansão',
  ];

  /**
   * Endpoint que retorna a lista completa dos jogos salvos no banco próprio.
   *
   * @param  Request  $request
   * @return JsonResponse
   */
  public function getLista(Request $request): JsonResponse
  {
    $rows = DB::table('jogos')
      ->where('excluido', false)
      ->orderByRaw('id_base NULLS FIRST')
      ->orderBy('nome')
      ->get();

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
   * @param  \Illuminate\Http\Request  $request
   * @param  string   $id  ID do jogo a ser atualizado
   * @return \Illuminate\Http\JsonResponse
   */
  public function postAtualizaCategoria(Request $request, $id): JsonResponse
  {
    // A nova categoria deve vir no corpo da requisição
    $categoria = $request->input('categoria');

    $upd = DB::table('jogos')->where('id', $id)->update(['categoria' => $categoria]);

    return new JsonResponse(['updated' => $upd]);
  }

  /**
   * Endpoint para atualizar todos os jogos do banco com base nas informações da
   * Ludopedia.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function postAtualizaJogos(Request $request) {

    $jogosLocal = DB::table('jogos')->orderBy('slug')->pluck('slug')->toArray();

    try {
      $jogosLudopedia = $this->getListaJogosLudopedia();
    }
    catch (\Exception $e) {
      throw new HttpException(500, 'Ocorreu um erro ao consultar a Ludopedia.', $e);
    }

    $novos = array_diff($jogosLudopedia, $jogosLocal);
    foreach ($novos as $slug) {
      try {
        $jogo = $this->getInfoJogoLudopedia($slug);
        DB::table('jogos')->insert($jogo);
      }
      catch (\Exception $e) {
        continue;
      }
    }

    $excluidos  = array_diff($jogosLocal, $jogosLudopedia);
    foreach ($excluidos as $slug) {
      DB::table('jogos')->where('slug', $slug)->update(['excluido' => true]);
    }

    $existentes = array_intersect($jogosLocal, $jogosLudopedia);
    $editados = DB::table('jogos')->where('editado', true)->pluck('slug')->toArray();
    foreach ($existentes as $slug) {
      if (!in_array($slug, $editados)) {
        try {
          $jogo = $this->getInfoJogoLudopedia($slug);
          unset($jogo['id'], $jogo['slug']);
          DB::table('jogos')->where('slug', $slug)->update($jogo);
        }
        catch (\Exception $e) {
          continue;
        }
      }
    }

    return $this->getLista();
  }

  /**
   * Consulta a lista completa dos jogos de cada usuário do grupo na Ludopedia.
   *
   * @return array  Array com os slugs dos jogos
   *
   * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
   * @throws \PHPHtmlParser\Exceptions\CircularException
   * @throws \PHPHtmlParser\Exceptions\CurlException
   * @throws \PHPHtmlParser\Exceptions\NotLoadedException
   * @throws \PHPHtmlParser\Exceptions\StrictException
   */
  protected function getListaJogosLudopedia() {
    $usuarios = DB::table('jogadores')->pluck('user_ludo');
    $dom = new Dom();
    $jogos = [];

    foreach (['base', 'expansao'] as $tipo) {
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
            $jogos[] = array_slice(explode('/', $urlJogo), -1)[0];
          }

          $pagina = false;
          if ($pagination = $dom->find('#page-content ul.pagination li.active', 0)) {
            $nextPageUrl = $pagination->nextSibling()->find('a', 0)->getAttribute('href');

            if (preg_match('/pagina=(\d+)/', $nextPageUrl, $matches)) {
              $pagina = (int) $matches[1];
            }
          }
        }
      }
    }
    $jogos = array_unique($jogos);
    sort($jogos);

    return $jogos;
  }

  /**
   * Consulta as informações de um jogo específico na Ludopedia.
   *
   * @param string $slug
   *
   * @return array
   *
   * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
   * @throws \PHPHtmlParser\Exceptions\CircularException
   * @throws \PHPHtmlParser\Exceptions\CurlException
   * @throws \PHPHtmlParser\Exceptions\NotLoadedException
   * @throws \PHPHtmlParser\Exceptions\StrictException
   */
  protected function getInfoJogoLudopedia($slug) {
    $urlJogo = self::LUDOPEDIA_URL.'/jogo/'.$slug;

    $dom = new Dom();
    $dom->loadFromUrl($urlJogo);

    // Informações básicas

    $jogo = [
      'id'      => (int) $dom->find('#id_jogo', 0)->getAttribute('value'),
      'nome'    => $dom->find('#nm_jogo', 0)->getAttribute('value'),
      'imagem'  => $dom->find('#img-capa', 0)->getAttribute('src'),
      'slug'    => $slug,
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

      // Mecânica: Cooperativo ou Jogo em Equipe
      if ($boxInfo->find('a[href$="mecanica/20"]', 0) || $boxInfo->find('a[href$="mecanica/37"]', 0)) {
        $jogo['coop'] = true;
      }

      if ($boxInfo->find('a[href$="dominio/6"]', 0)) { // Domínio: Jogos Infantis
        $jogo['categoria'] = self::CATEGORIA_INFANTIL;
      }
      elseif ($boxInfo->find('a[href$="categoria/113"]', 0)) { // Categoria: Jogos Festivos
        $jogo['categoria'] = self::CATEGORIA_PARTY;
      }
      elseif ($boxInfo->find('a[href$="dominio/9"]', 0)) { // Domínio: Jogos Expert
        $jogo['categoria'] = self::CATEGORIA_PESADO;
      }
      else {
        $jogo['categoria'] = self::CATEGORIA_MEDIO;
        $duracao = trim($dom->find('#page-content .jogo-top-main > ul.list-inline li', 1)->text);
        if (preg_match('/^(\d+) min/', $duracao, $m) && $m[1] < 60) {
          $jogo['categoria'] = self::CATEGORIA_LEVE;
        }
      }
    }

    return $jogo;
  }

}
