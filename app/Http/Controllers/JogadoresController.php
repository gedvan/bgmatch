<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JogadoresController extends Controller {

  public function getJogadores() {
    $jogadores = DB::table('jogadores')->get();
    return new JsonResponse($jogadores);
  }
}
