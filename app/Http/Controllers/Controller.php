<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Envia uma resposta no forma JSON.
     *
     * @param $content
     * @return Response
     */
    protected function sendJson($content): Response
    {
        $response = new Response(json_encode($content));
        $response->header('Content-Type', 'application/json');

        return $response;
    }
}
