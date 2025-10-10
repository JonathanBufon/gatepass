<?php

namespace GatePass\Api\v1\controllers;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class HomeController
{
    private TwigEnvironment $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function index(): Response
    {
        // Renderiza um arquivo de template e passa variáveis para ele
        $html = $this->twig->render('home/index.html.twig', [
            'nome_do_evento' => 'Show de Rock Incrível',
            'data' => '25 de Dezembro'
        ]);

        return new Response($html);
    }
}
