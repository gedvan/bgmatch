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
  return view('index', [
    'build' => getenv('APP_BUILD'),
  ]);
});

$router->group(['prefix' => 'api'], function () use ($router) {
  $router->post('login', 'AuthController@login');

  $router->group(['middleware' => 'auth'], function() use ($router) {
    $router->get('userinfo', 'AuthController@userInfo');

    $router->get('jogos', 'JogosController@getLista');
    $router->get('jogos/pesquisa/{termo}', 'JogosController@pesquisarJogosLudopedia');
    $router->post('jogos/importa/{slug}', 'JogosController@importa');
    $router->post('jogos/atualiza', 'JogosController@postAtualizaJogos');
    $router->post('jogos/{id}/update', 'JogosController@postSalvaJogo');
    $router->get('jogos/{id}/bgg', 'JogosController@fetchBggInfo');

    $router->get('jogadores', 'JogadoresController@getJogadores');
    $router->get('jogadores/dados', 'JogadoresController@getDadosJogadores');

    $router->get('partidas/lista[/{periodo}]', 'PartidasController@lista');
    $router->get('partidas/locais', 'PartidasController@getLocais');
    $router->post('partidas/new', 'PartidasController@postNovaPartida');
    $router->get('partidas/{id}', 'PartidasController@getPartida');
    $router->post('partidas/{id}/update', 'PartidasController@postAtualizaPartida');
    $router->post('partidas/{id}/delete', 'PartidasController@postExcluirPartida');

    $router->get('ranking/{ano}', 'RankingController@getPontuacao');
    $router->get('ranking/{ano}/pontuacao', 'RankingController@getTabelaPontuacao');
  });

//  $router->get('teste', function () {
//    /** @var \App\Services\JogosService $jogosService */
//    $jogosService = app(\App\Services\JogosService::class);
//    $jogos = $jogosService->getLista();
//    return '';
//  });

});

