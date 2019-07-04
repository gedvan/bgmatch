<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use Illuminate\Http\Response;

class JogosController extends Controller
{
    const LUDOPEDIA_URL = 'https://www.ludopedia.com.br';
    const LUDOPEDIA_ID_GRUPO = '981';

    const TIPO_INFANTIL = 'I';
    const TIPO_COOP     = 'C';
    const TIPO_PARTY    = 'P';
    const TIPO_MEDIO    = 'M';
    const TIPO_EXPERT   = 'E';

    protected static $_tiposValidos = [
        self::TIPO_INFANTIL,
        self::TIPO_COOP,
        self::TIPO_PARTY,
        self::TIPO_MEDIO,
        self::TIPO_EXPERT
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function lista(Request $request)
    {
        $jogos = DB::table('jogos')->get();

        return $this->sendJson($jogos);
    }

    /**
     * Retorna a lista de jogos do acervo do grupo na Ludopedia.
     *
     * @param  Request  $request
     * @return Response
     * @throws \PHPHtmlParser\Exceptions\ParentNotFoundException
     */
    public function ludopedia(Request $request)
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

            $nextPageUrl = $dom->find('#page-content ul.pagination li.active', 0)
                ->nextSibling()->find('a', 0)->getAttribute('href');

            if (preg_match('/pagina=(\d+)/', $nextPageUrl, $matches)) {
                $pagina = (int) $matches[1];
            } else {
                $pagina = FALSE;
            }
        }

        return $this->sendJson($jogos);
    }

    public function atualiza($nome)
    {
        $urlJogo = self::LUDOPEDIA_URL . '/jogo/' . $nome;

        $dom = new Dom();
        $dom->loadFromUrl($urlJogo);

        $id_ludo = $dom->find('#id_jogo', 0)->getAttribute('value');
        $jogo = [
            'nome'      => $dom->find('#nm_jogo', 0)->getAttribute('value'),
            'img_ludo'  => $dom->find('#img-capa', 0)->getAttribute('src'),
            'link_ludo' => $urlJogo,
        ];

        $jogadores = $dom->find('#page-content .jogo-top-main ul.list-inline li', 2)->text;

        if (preg_match('/(\d+) a (\d+) jogadores/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[2];
        }
        elseif (preg_match('/(\d+) jogador(es)?/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[1];
        }

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

        DB::table('jogos')->updateOrInsert(['id_ludo' => $id_ludo], $jogo);

        $jogo['id_ludo'] = $id_ludo;

        return $jogo;
    }

    protected function mapDominioLudopediaParaTipo($idDominio) {

    }

    public function atualizar()
    {
        ini_set('max_execution_time', 300);
        $jogos = [];

        $dom = new Dom();
        $dom->loadFromUrl(self::LUDOPEDIA_URL . '/grupo/' . self::LUDOPEDIA_ID_GRUPO . '/acervo');
        $links = $dom->find('#page-content .panel-body ul.row li a');

        foreach ($links as $link) {
            $url = $link->getAttribute('href');
            $jogos[] = $this->consultaJogoLudopedia($url);
        }

        return $this->sendJson($jogos);
    }

    private function consultaJogoLudopedia($url)
    {
        $jogo = [];

        $dom = new Dom();
        $dom->loadFromUrl($url);

        $jogo['id_ludo']   = $dom->find('#id_jogo', 0)->getAttribute('value');
        $jogo['nome']      = $dom->find('#nm_jogo', 0)->getAttribute('value');
        $jogo['img_ludo']  = $dom->find('#img-capa', 0)->getAttribute('src');
        $jogo['link_ludo'] = $url;

        $jogadores = $dom->find('#page-content .jogo-top-main ul.list-inline li', 2)->text;

        if (preg_match('/(\d+) a (\d+) jogadores/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[2];
        }
        elseif (preg_match('/(\d+) jogador(es)?/', $jogadores, $m)) {
            $jogo['min'] = $m[1];
            $jogo['max'] = $m[1];
        }

        return $jogo;
    }
}
