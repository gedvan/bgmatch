<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Authenticate {

  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   *
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {

    try {
      $authorization = $request->header('Authorization');
      if ($authorization) {
        [$name, $token] = explode(' ', $authorization);
        if ($name == 'Bearer' && $token) {
          $key = new Key(env('JWT_SECRET'), env('JWT_ALG'));
          $jwt = JWT::decode($token, $key);
          $user = DB::table('usuarios')->find($jwt->sub);
          if (!$user) {
            throw new \Exception('Usuário não encontrado');
          }
        }
        else {
          throw new \Exception('Autorização inválida');
        }
      }
      else {
        throw new \Exception('Autorização ausente');
      }
    }
    catch (\Exception $e) {
      return response($e->getMessage(), 401);
    }

    unset($user->senha);
    $request->attributes->add(['user' => $user]);
    return $next($request);
  }
}
