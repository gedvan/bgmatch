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

use Illuminate\Support\Facades\DB;

$router->get('/', function () use ($router) {
  return view('index');
});

$router->group(['prefix' => 'api'], function () use ($router) {
  $router->get('jogos', 'JogosController@getLista');
  $router->post('jogos/{id}/tipo', 'JogosController@postAtualizaCategoria');

  $router->post('jogos/atualiza', 'JogosController@postAtualizaJogos');

  $router->get('jogadores', 'JogadoresController@getJogadores');

  $router->get('partidas', 'PartidasController@getLista');
  $router->get('partidas/locais', 'PartidasController@getLocais');
  $router->post('partidas/nova', 'PartidasController@postNovaPartida');

  $router->get('teste', function () {
    return 'teste';
  });
});

