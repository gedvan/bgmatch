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
    $router->get('jogos', 'JogosController@getLista');
    $router->post('jogos/{id}/tipo', 'JogosController@postAtualizaTipo');

    $router->get('jogos/ludopedia', 'JogosController@getJogosLudopedia');
    $router->post('jogos/atualiza/{slug}', 'JogosController@postAtualizaJogo');
});

