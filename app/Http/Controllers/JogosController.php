<?php

namespace App\Http\Controllers;

use App\Services\JogosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JogosController extends Controller {

  public function __construct(
    protected JogosService $jogosService
  ) {}

  /**
   * Endpoint que retorna a lista completa dos jogos salvos no banco.
   *
   * @return JsonResponse
   */
  public function getLista(): JsonResponse
  {
    $jogos = $this->jogosService->getLista();

    return new JsonResponse($jogos);
  }

  /**
   * Endpoint para atualizar os dados de um jogo específico.
   *
   * @param Request $request
   * @param string $id  ID do jogo a ser atualizado
   * @return JsonResponse
   */
  public function postSalvaJogo(Request $request, string $id): JsonResponse
  {
    // Os novos dados devem vir no corpo da requisição.
    $upd = $this->jogosService->salvaJogo($id, $request->input());

    return new JsonResponse(['updated' => $upd]);
  }

  /**
   * Endpoint para atualizar todos os jogos do banco com base nas informações da
   * Ludopedia.
   *
   * @return JsonResponse
   */
  public function postAtualizaJogos()
  {
    set_time_limit(300);

    try {
      $this->jogosService->atualizaDaLudopedia();
    }
    catch (\Exception $e) {
      throw new HttpException(500, 'Ocorreu um erro ao acessar a Ludopedia.', $e);
    }

    return $this->getLista();
  }



  /**
   * Importa um jogo da Ludopedia para o banco local.
   * @throws \Exception
   */
  public function importa($slug): JsonResponse
  {
    $jogo = $this->jogosService->getInfoLudopedia($slug);
    if ($jogo) {
      $this->jogosService->insere($jogo);
      return new JsonResponse([
        'sucesso' => true,
        'jogo' => $jogo,
      ]);
    } else {
      throw new HttpException(500, 'Jogo não encontrado');
    }
  }

  /**
   * Pesquisa os jogos na Ludopedia a partir de uma string.
   *
   * @param string $termo
   *
   * @return JsonResponse
   * @throws \HttpException
   */
  public function pesquisarJogosLudopedia(string $termo): JsonResponse
  {
    try {
      $jogos = $this->jogosService->pesquisarJogosLudopedia($termo);
    } catch (\Exception $e) {
      throw new \HttpException(500, $e->getMessage());
    }

    return new JsonResponse($jogos);
  }

  public function fetchBggInfo(int $id)
  {
    try {
      $bgg_id = $this->jogosService->fetchBggId($id);
      $bgg_weight = $bgg_id ? $this->jogosService->fetchBggWeight($bgg_id) : NULL;
      $response = [
        'bgg_id' => $bgg_id,
        'bgg_weight' => $bgg_weight,
      ];
    } catch (\Exception $e) {
      throw new \HttpException(500, $e->getMessage());
    }

    return new JsonResponse($response);
  }

}
