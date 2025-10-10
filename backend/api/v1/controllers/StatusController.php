<?php


namespace GatePass\Api\v1\controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StatusController
{
    /**
     * Verifica a saúde da API e retorna um status de sucesso.
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        return new JsonResponse(
            [
                'status' => 'ok',
                'message' => 'API está funcionando corretamente!',
                'timestamp' => date('Y-m-d H:i:s')
            ],
            Response::HTTP_OK // Define o código de status HTTP para 200 OK
        );
    }
}
