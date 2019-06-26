<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PHPHtmlParser\Dom;
use Illuminate\Http\Response;

class JogosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function lista()
    {
        $jogos = DB::table('jogos')->get();
        return $this->sendJson($jogos);
    }

    public function atualiza()
    {
        ini_set('max_execution_time', 300);
        $jogos = [];

        $dom = new Dom();
        $dom->loadFromUrl('https://www.ludopedia.com.br/grupo/981/acervo');
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
