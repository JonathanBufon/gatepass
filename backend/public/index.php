<?php

// backend/public/index.php

use GatePass\Core\Kernel;
use Symfony\Component\HttpFoundation\Response;

// Envolvemos tudo em um try/catch para a mensagem de erro simplificada.
try {
    // Carrega o autoloader do Composer
    require_once dirname(__DIR__) . '/vendor/autoload.php';

    // Inicializa o "coração" da nossa aplicação
    $kernel = new Kernel(dirname(__DIR__));
    $kernel->boot();

    // Pega o container de serviços que o Kernel preparou
    $container = $kernel->getContainer();

    // Pede diretamente ao container pelo serviço do Twig que configuramos
    /** @var \Twig\Environment $twig */
    $twig = $container->get('twig');

    // Renderiza o template da homepage com dados de exemplo
    $html = $twig->render('home/index.html.twig', [
        'nome_do_evento' => 'Show de Rock Incrível',
        'data' => '25 de Dezembro'
    ]);

    // Cria e envia a resposta HTML para o navegador
    $response = new Response($html);
    $response->send();

} catch (\Throwable $e) {
    // Se qualquer erro ocorrer em qualquer um dos passos acima,
    // exibe a mensagem de debug que você pediu.
    // Para ver o erro real, você pode descomentar a linha abaixo.
    echo '<pre>' . $e->getMessage() . '<br>' . $e->getTraceAsString() . '</pre>';
    // echo "Deu um erro, debug para achar";
}

