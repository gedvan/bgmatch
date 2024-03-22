<?php

namespace App\Http\Controllers;

use App\Services\PartidasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PartidasController extends Controller {

  public function __construct(
    protected PartidasService $partidasService)
  { }

  public function lista(Request $request, string $periodo = ''): HttpResponse
  {
    $sort = $request->input('sort');
    if (!$sort) {
      $sort = 'asc';
    } elseif (!in_array($sort, ['asc', 'desc'])) {
      throw new HttpException(500, 'Invalid sort parameter.');
    }

    $data_inicio = '';
    $data_fim = '';

    if ($periodo) {
      if (str_contains($periodo, ':')) {
        [$inicio, $fim] = explode(':', $periodo);

        if ($inicio) {
          if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $inicio, $m)) {
            throw new HttpException(500, 'Formato de data inválido (data inicial).');
          }
          $ano_inicio = $m[1];
          $mes_inicio = $m[2] ?? '01';
          $dia_inicio = $m[3] ?? '01';

          if (!checkdate($mes_inicio, $dia_inicio, $ano_inicio)) {
            throw new HttpException(500, 'Data inválida (data inicial).');
          }
          $data_inicio = "$ano_inicio-$mes_inicio-$dia_inicio";
        }

        if ($fim) {
          if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $fim, $m)) {
            throw new HttpException(500, 'Formato de data inválido (data final).');
          }
          $ano_fim = $m[1];
          $mes_fim = $m[2] ?? '12';
          $dia_fim = $m[3] ?? (new \DateTime("$ano_fim-$mes_fim-01"))->format('t');

          if (!checkdate($mes_fim, $dia_fim, $ano_fim)) {
            throw new HttpException(500, 'Data inválida (data final).');
          }
          $data_fim = "$ano_fim-$mes_fim-$dia_fim";
        }
      }
      else {
        if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $periodo, $m)) {
          throw new HttpException(500, 'Formato de data inválido.');
        }
        $ano_inicio = $m[1];
        $mes_inicio = $m[2] ?? '01';
        $dia_inicio = $m[3] ?? '01';

        if (!checkdate($mes_inicio, $dia_inicio, $ano_inicio)) {
          throw new HttpException(500, 'Data inválida.');
        }

        $ano_fim = $ano_inicio;
        $mes_fim = $m[2] ?? '12';
        $dia_fim = $m[3] ?? (new \DateTime("$ano_fim-$mes_fim-01"))->format('t');

        $data_inicio = "$ano_inicio-$mes_inicio-$dia_inicio";
        $data_fim = "$ano_fim-$mes_fim-$dia_fim";
      }
    }

    $options = ['sort' => $sort];
    if ($request->has('ranking')) {
      $options['ranking'] = (bool) $request->input('ranking');
    }
    $partidas = $this->partidasService->getPartidasPorPeriodo($data_inicio, $data_fim, $options);

    return new JsonResponse($partidas);
  }

  public function getPartida($id)
  {
    $partida = $this->partidasService->getPartida($id);

    if (empty($partida)) {
      throw new NotFoundHttpException();
    }

    return new JsonResponse($partida);
  }

  public function getLocais() {
    $locais = $this->partidasService->getLocaisPartidas();
    return new JsonResponse($locais);
  }

  public function postNovaPartida(Request $request) {
    $response = ['ok' => FALSE];

    try {
      $this->partidasService->salvaPartida($request->input());
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }

    return new JsonResponse($response);
  }

  public function postAtualizaPartida(Request $request, $id) {
    $response = ['ok' => FALSE];

    try {
      $partida = $request->input() + ['id' => $id];
      $this->partidasService->salvaPartida($partida);
      $response['ok'] = TRUE;
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }

    return new JsonResponse($response);
  }

  public function postExcluirPartida(int $id) {
    try {
      $delete = $this->partidasService->excluiPartida($id);
    }
    catch (\Exception $e) {
      throw new HttpException(500, $e->getMessage(), $e);
    }
    return new JsonResponse($delete);
  }
}
