<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ParentNotFoundException;

class JogosController extends Controller
{
    const LUDOPEDIA_URL = 'https://www.ludopedia.com.br';
    const LUDOPEDIA_ID_GRUPO = '981';
    const LUDOPEDIA_USUARIOS = ['gedvan', 'fechine', 'brunobrujah', 'rodrigor', 'matheuslaureano'];

    const TIPO_INFANTIL = 'I';
    const TIPO_COOP     = 'C';
    const TIPO_PARTY    = 'P';
    const TIPO_MEDIO    = 'M';
    const TIPO_EXPERT   = 'E';
    const TIPO_EXPANSAO = 'X';

    const TIPOS = [
        self::TIPO_INFANTIL => 'Infantil',
        self::TIPO_COOP     => 'Cooperativo',
        self::TIPO_PARTY    => 'Party game',
        self::TIPO_MEDIO    => 'Médio',
        self::TIPO_EXPERT   => 'Expert'
    ];

    /**
     * Endpoint que retorna a lista completa dos jogos salvos no banco próprio.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getLista(Request $request): JsonResponse
    {
        $jogos = DB::table('jogos')->where('tipo', '<>', self::TIPO_EXPANSAO)->get();

        return new JsonResponse($jogos);
    }

    /**
     * Endpoint para atualizar o tipo de um jogo específico.
     *
     * @param  Request  $request
     * @param  string   $id_ludo    ID do jogo a ser atualizado
     * @return JsonResponse
     */
    public function postAtualizaTipo(Request $request, $id_ludo): JsonResponse
    {
        // O novo tipo deve vir no corpo da requisição
        $tipo = $request->input('tipo');

        $upd = DB::table('jogos')->where('id_ludo', $id_ludo)->update(['tipo' => $tipo]);

        return new JsonResponse(['updated' => $upd]);
    }

    /**
     * Endpoint que retorna a lista de jogos do acervo do grupo na Ludopedia. A lista consiste em um array com apenas
     * o identificador textual de cada jogo, utilizado na URL do mesmo (ex: ['7-wonders', 'black-stories', ...]).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getJogosLudopedia(Request $request): JsonResponse
    {
        $jogos = [];
        $dom = new Dom();

        $pagina = 1;
        while ($pagina) {
            $url = self::LUDOPEDIA_URL . '/grupo/' . self::LUDOPEDIA_ID_GRUPO . '/acervo' . ($pagina > 1 ? "?pagina=$pagina" : '');
            $dom->loadFromUrl($url);

            $links = $dom->find('#page-content .panel-body ul.row li a');
            foreach ($links as $link) {
                $urlJogo = $link->getAttribute('href');
                $jogos[] = array_slice(explode('/', $urlJogo), -1)[0];
            }

            try {
                $nextPageUrl = $dom->find('#page-content ul.pagination li.active', 0)
                    ->nextSibling()->find('a', 0)->getAttribute('href');

                if (preg_match('/pagina=(\d+)/', $nextPageUrl, $matches)) {
                    $pagina = (int) $matches[1];
                } else {
                    $pagina = FALSE;
                }
            }
            catch (ParentNotFoundException $exception) {
                $pagina = FALSE;
            }
        }

        return new JsonResponse($jogos);
    }

    public function getJogosLudopedia2(Request $request): JsonResponse
    {
        $jogos = [];
        $dom = new Dom();

        foreach (['base', 'expansao'] as $tipo) {
            $jogos[$tipo] = [];
            foreach (self::LUDOPEDIA_USUARIOS as $usuario) {
                $pagina = 1;
                while ($pagina) {
                    $url = self::LUDOPEDIA_URL."/colecao?usuario=$usuario&tipo_jogo=$tipo";
                    if ($pagina > 1) $url .= "&pagina=$pagina";

                    $dom->loadFromUrl($url);

                    $links = $dom->find('#page-content .panel-body .media .media-heading a');
                    foreach ($links as $link) {
                        $urlJogo = $link->getAttribute('href');
                        $jogos[$tipo][] = array_slice(explode('/', $urlJogo), -1)[0];
                    }

                    $pagina = FALSE;
                    if ($pagination = $dom->find('#page-content ul.pagination li.active', 0)) {
                        try {
                            $nextPageUrl = $pagination->nextSibling()->find('a', 0)->getAttribute('href');

                            if (preg_match('/pagina=(\d+)/', $nextPageUrl, $matches)) {
                                $pagina = (int) $matches[1];
                            }
                        }
                        catch (ParentNotFoundException $exception) {}
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
     * @param  string  $slug    Identificador textual do jogo na ludopedia (utilizado nas URLs)
     * @return JsonResponse
     */
    public function postAtualizaJogo(string $slug): JsonResponse
    {
        $urlJogo = self::LUDOPEDIA_URL . '/jogo/' . $slug;

        $dom = new Dom();
        $dom->loadFromUrl($urlJogo);

        // Informações básicas

        $jogo = [
            'id_ludo'   => (int) $dom->find('#id_jogo', 0)->getAttribute('value'),
            'nome'      => $dom->find('#nm_jogo', 0)->getAttribute('value'),
            'img_ludo'  => $dom->find('#img-capa', 0)->getAttribute('src'),
            'slug'      => $slug,
        ];

        // Número de jogadores

        $jogadores = $dom->find('#page-content .jogo-top-main ul.list-inline li', 2)->text;

        if (preg_match('/(\d+) a (\d+) jogadores/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[2];
        }
        elseif (preg_match('/(\d+) jogador(es)?/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[1];
        }

        // Tipo / Expansão

        $linkExpansao = $dom->find('#page-content .jogo-top-main > h5 a', 0);
        if ($linkExpansao) {
            $slugExpansao = array_slice(explode('/', $linkExpansao->getAttribute('href')), -1)[0];
            $idJogoBase = DB::table('jogos')->where('slug', $slugExpansao)->value('id_ludo');

            $jogo['tipo'] = self::TIPO_EXPANSAO;
            $jogo['id_base'] = $idJogoBase;
        }
        else {
            $boxInfo = $dom->find('#bloco-descricao-sm .col-sm-3 .bg-gray-light', 0);

            if ($boxInfo->find('a[href$="mecanica/20"]', 0)) { // Mecânica: Cooperativo
                $jogo['tipo'] = self::TIPO_COOP;
            }
            elseif ($boxInfo->find('a[href$="dominio/6"]', 0)) { // Domínio: Jogos Infantis
                $jogo['tipo'] = self::TIPO_INFANTIL;
            }
            elseif ($boxInfo->find('a[href$="categoria/113"]', 0)) { // Categoria: Jogos Festivos
                $jogo['tipo'] = self::TIPO_PARTY;
            }
            elseif ($boxInfo->find('a[href$="dominio/9"]', 0)) { // Domínio: Jogos Expert
                $jogo['tipo'] = self::TIPO_EXPERT;
            }
            else {
                $jogo['tipo'] = self::TIPO_MEDIO;
            }
        }

        DB::table('jogos')->updateOrInsert(['id_ludo' => $jogo['id_ludo']], $jogo);

        return new JsonResponse($jogo);
    }

}
