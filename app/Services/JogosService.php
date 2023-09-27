<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use Psr\Http\Client\ClientExceptionInterface;

class JogosService
{
  const LUDOPEDIA_URL = 'https://ludopedia.com.br';

  const CATEGORIA_PESADO    = 'P';
  const CATEGORIA_MEDIO     = 'M';
  const CATEGORIA_LEVE      = 'L';
  const CATEGORIA_PARTY_INFANTIL  = 'Y';
  const CATEGORIA_EXPANSAO  = 'X';

  // Obsoleto
  const CATEGORIA_PARTY     = 'F';
  const CATEGORIA_INFANTIL  = 'I';

  /**
   * @return array
   */
  public function getLista(): array
  {
    $rows = DB::table('jogos AS j')
      ->select(['j.id', 'j.nome', 'j.categoria', 'j.min', 'j.max', 'j.imagem', 'j.slug', 'j.id_base', 'j.coop', 'j.editado', 'j.excluido'])
      ->leftJoin('partidas AS p', 'p.id_jogo', '=', 'j.id')
      ->selectRaw('COUNT(p.id) AS num_partidas')
      ->selectRaw('MAX(p.data) AS ultima_partida')
      ->groupBy('j.id', 'j.nome', 'j.categoria', 'j.min', 'j.max', 'j.imagem', 'j.slug', 'j.id_base', 'j.coop', 'j.editado', 'j.excluido')
      ->orderByRaw('j.id_base NULLS FIRST')
      ->orderBy('j.nome')
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

    return array_values($jogos);
  }

  public function getById($id)
  {
    return DB::table('jogos as j')
      ->select()
      ->find($id);
  }

  public function salvaJogo($id, $coop, $categoria)
  {
    return DB::table('jogos')->where('id', $id)->update([
      'coop' => (bool) $coop,
      'categoria' => $categoria,
      'editado' => true,
    ]);
  }

  public function insere($jogo)
  {
    DB::table('jogos')->insert($jogo);
  }

  /**
   * Consulta as informações de um jogo específico na Ludopedia.
   *
   * @param string $slug
   *
   * @return array
   *
   * @throws \Exception
   */
  public function getInfoLudopedia(string $slug): array
  {
    $urlJogo = self::LUDOPEDIA_URL.'/jogo/'.$slug;
    $jogo = [];

    try {
      $dom = new Dom();
      $dom->loadFromUrl($urlJogo);

      // Informações básicas

      $jogo['id'] = (int)$dom->find('#id_jogo', 0)->getAttribute('value');
      $jogo['nome'] = $dom->find('#nm_jogo', 0)->getAttribute('value');
      $jogo['imagem'] = $dom->find('#img-capa', 0)->getAttribute('src');
      $jogo['slug'] = $slug;

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
        } else {
          $jogo['coop'] = false;
        }

        if ($boxInfo->find('a[href$="dominio/6"]', 0)) { // Domínio: Jogos Infantis
          $jogo['categoria'] = self::CATEGORIA_PARTY_INFANTIL;
        } elseif ($boxInfo->find('a[href$="categoria/113"]', 0)) { // Categoria: Jogos Festivos
          $jogo['categoria'] = self::CATEGORIA_PARTY_INFANTIL;
        } elseif ($boxInfo->find('a[href$="dominio/9"]', 0)) { // Domínio: Jogos Expert
          $jogo['categoria'] = self::CATEGORIA_PESADO;
        } else {
          $jogo['categoria'] = self::CATEGORIA_MEDIO;
          $duracao = trim($dom->find('#page-content .jogo-top-main > ul.list-inline li', 1)->text);
          if (preg_match('/^(\d+) min/', $duracao, $m) && $m[1] < 60) {
            $jogo['categoria'] = self::CATEGORIA_LEVE;
          }
        }
      }
    }
    catch (\Throwable $e) {
      throw new \Exception('Erro ao acessar a Ludopedia', 0, $e);
    }

    return $jogo;
  }

  /**
   * Pesquisa os jogos na Ludopedia a partir de uma string.
   *
   * @param string $termo
   * @return array
   * @throws \Exception
   */
  public function pesquisarJogosLudopedia(string $termo): array
  {
    $cadastrados = DB::table('jogos')->pluck('id')->all();
    $jogos = [];

    try {
      $urlPesquisa = self::LUDOPEDIA_URL . '/search_jogo?search=' . $termo;
      $dom = new Dom();
      $dom->loadFromUrl($urlPesquisa);

      $results = $dom->find('.main-panel-body .media.bord-btm.pad-btm');
      if ($results->count()) {
        foreach ($results as $result) {
          $image = $result->find('img.img-capa', 0)->getAttribute('src');
          $id = preg_match('|ludopedia-capas/(\d+)_s\.jpg|', $image, $m) ? $m[1] : NULL;
          $link = $result->find('a.full-link', 0);
          $jogos[] = [
            'id' => $id,
            'link' => $link->getAttribute('href'),
            'slug' => explode('/', $link->getAttribute('href'))[4],
            'title' => trim($link->find('h4')->text),
            'image' => $image,
            'cadastrado' => in_array($id, $cadastrados),
          ];
        }
      }
    }
    catch (\Throwable $e) {
      throw new \Exception('Erro ao acessar a Ludopedia', 0, $e);
    }

    return $jogos;
  }

  /**
   * Consulta a lista completa dos jogos de cada usuário do grupo na Ludopedia.
   *
   * @return array  Array com os slugs dos jogos
   *
   * @throws \Exception
   */
  public function getListaJogosLudopedia(): array
  {
    $usuarios = DB::table('jogadores')->pluck('user_ludo');
    $jogos = [];

    try {
      $dom = new Dom();

      foreach (['base', 'expansao'] as $tipo) {
        foreach ($usuarios as $usuario) {
          $pagina = 1;
          while ($pagina) {
            $url = self::LUDOPEDIA_URL . "/colecao?usuario=$usuario&tipo_jogo=$tipo";
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
                $pagina = (int)$matches[1];
              }
            }
          }
        }
      }
      $jogos = array_unique($jogos);
      sort($jogos);
    }
    catch (\Throwable $e) {
      throw new \Exception('Erro ao acessar a Ludopedia', 0, $e);
    }

    return $jogos;
  }

  /**
   * Atualiza todos os jogos do banco com base nas informações da Ludopedia.
   */
  public function atualizaDaLudopedia()
  {
    $jogosLocal = DB::table('jogos')->orderBy('slug')->pluck('slug')->toArray();

    $jogosLudopedia = $this->getListaJogosLudopedia();

    // Insere novos jogos cadastrados na coleção na Ludopedia, mas ainda não estão no banco local
    $novos = array_diff($jogosLudopedia, $jogosLocal);
    foreach ($novos as $slug) {
      try {
        $jogo = $this->getInfoLudopedia($slug);
        $this->insere($jogo);
      }
      catch (\Exception $e) {
        continue;
      }
    }

    // Marca como excluídos os jogos que estão no banco local, mas não vieram na lista da Ludopedia
    $excluidos  = array_diff($jogosLocal, $jogosLudopedia);
    foreach ($excluidos as $slug) {
      DB::table('jogos')
        ->where(['slug' => $slug, 'excluido' => false])
        ->update(['excluido' => true]);
    }

    // Certifica-se que os demais jogos que vieram não estão marcados como excluídos no banco local
    $existentes = array_intersect($jogosLocal, $jogosLudopedia);
    foreach ($existentes as $slug) {
      DB::table('jogos')
        ->where(['slug' => $slug, 'excluido' => true])
        ->update(['excluido' => false]);
    }
  }

}
