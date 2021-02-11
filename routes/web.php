<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
  return view('index');
});

$router->group(['prefix' => 'api'], function () use ($router) {
  $router->post('login', 'AuthController@login');

  $router->group(['middleware' => 'auth'], function() use ($router) {
    $router->get('userinfo', 'AuthController@userInfo');

    $router->get('jogos', 'JogosController@getLista');
    $router->get('jogos/pesquisa/{termo}', 'JogosController@pesquisarJogosLudopedia');
    $router->post('jogos/importa/{slug}', 'JogosController@importa');
    $router->post('jogos/atualiza', 'JogosController@postAtualizaJogos');
    $router->post('jogos/salva/{id}', 'JogosController@postSalvaJogo');

    $router->get('jogadores', 'JogadoresController@getJogadores');
    $router->get('jogadores/dados', 'JogadoresController@getDadosJogadores');

    $router->get('partidas', 'PartidasController@getLista');
    $router->get('partidas/locais', 'PartidasController@getLocais');
    $router->get('partidas/importa', 'PartidasController@importa');
    $router->post('partidas/nova', 'PartidasController@postNovaPartida');
    $router->get('partida/{id}', 'PartidasController@getPartida');
    $router->post('partida/{id}/update', 'PartidasController@postAtualizaPartida');
    $router->post('partida/{id}/delete', 'PartidasController@postExcluirPartida');

    $router->get('ranking/{ano}', 'RankingController@getDados');
  });

//  $router->get('bruno', 'PartidasController@bruno');
//  $router->get('rodrigo', 'PartidasController@rodrigo');

});

