<?php

// backend/config/routes.php

use GatePass\Api\v1\controllers\HomeController;
use GatePass\Api\v1\controllers\StatusController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Este arquivo define todas as rotas da aplicação.
 * Ele deve retornar um objeto RouteCollection.
 */
return function() {
    $routes = new RouteCollection();

    // --- Rotas WEB (renderizam HTML com Twig) ---
    // Mapeia a URL raiz ("/") para o método "index" do HomeController.
    $routes->add(
        'homepage', // Nome único da rota
        new Route(
            '/', // O caminho da URL
            ['_controller' => [HomeController::class, 'index']], // O controller e método a serem executados
            [], // Requisitos de parâmetros
            [], // Opções
            '', // Host
            [], // Schemes (http, https)
            ['GET'] // Métodos HTTP permitidos
        )
    );


    // --- Rotas da API (retornam JSON) ---
    // Mapeia a URL "/api/v1/status" para o método "check" do StatusController.
    $routes->add(
        'api_status', // Nome único da rota
        new Route(
            '/api/v1/status', // O caminho da URL
            ['_controller' => [StatusController::class, 'check']],
            [],
            [],
            '',
            [],
            ['GET']
        )
    );

    // ... Adicione suas futuras rotas aqui ...

    return $routes;
};

