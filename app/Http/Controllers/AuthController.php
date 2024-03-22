<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    $usuario = $request->get('usuario');
    $senha   = $request->get('senha');

    $result = DB::table('usuarios')->where([
      'usuario' => $usuario,
      'senha'   => md5($senha),
    ])->get();
    if ($result->count()) {
      $token = $this->generateJwt($result->first());
      return new JsonResponse(['token' => $token]);
    }

    return new JsonResponse(['token' => '', 'error' => 'Usuário ou senha incorretos'], 401);
  }

  protected function generateJwt($user)
  {
    $payload = [
      'iss' => "lumen-jwt", // Issuer of the token
      'sub' => $user->id, // Subject of the token
      'iat' => time(), // Time when JWT was issued.
      'exp' => time() + 60 * 60 * 2 // Expiration time: 2h
    ];
    return JWT::encode($payload, env('JWT_SECRET'), env('JWT_ALG'));
  }

  public function userInfo(Request $request)
  {
    $user = $request->get('user');

    // Se faltar menos de 30 minutos para o JWT expirar, gera um novo.
    $jwt = $request->get('jwt');
    $timeToExpire = $jwt->exp - time();
    if ($timeToExpire < (30 * 60)) {
      $token = $this->generateJwt($user);
    }

    return new JsonResponse([
      'user' => $user,
      'token' => $token ?? '',
    ]);
  }
}
