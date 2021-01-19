<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller {

  public function login(Request $request) {
    $usuario = $request->get('usuario');
    $senha   = $request->get('senha');

    $result = DB::table('usuarios')->where([
      'usuario' => $usuario,
      'senha'   => md5($senha),
    ])->get();
    if ($result->count()) {
      $token = $this->jwt($result->first());
      return new JsonResponse(['token' => $token]);
    }

    return new JsonResponse(['token' => '', 'error' => 'Usuário ou senha incorretos'], 401);
  }

  protected function jwt($user) {
    $payload = [
      'iss' => "lumen-jwt", // Issuer of the token
      'sub' => $user->id, // Subject of the token
      'iat' => time(), // Time when JWT was issued.
      'exp' => time() + 60*60 // Expiration time
    ];

    return JWT::encode($payload, env('JWT_SECRET'), env('JWT_ALG'));
  }

  public function userInfo(Request $request) {
    $user = $request->get('user');
    return new JsonResponse($user);
  }

}
