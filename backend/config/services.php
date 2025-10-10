<?php

// backend/config/services.php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Este arquivo é o "cérebro" da injeção de dependência.
 * Ele retorna uma função que recebe o ContainerBuilder e o configura.
 */
return function(ContainerBuilder $container) {

    // --- PARÂMETROS ---
    $container->setParameter('mysql_host', getenv('MYSQL_HOST'));
    $container->setParameter('mysql_port', getenv('MYSQL_PORT'));
    $container->setParameter('mysql_db', getenv('MYSQL_DATABASE'));
    $container->setParameter('mysql_user', getenv('MYSQL_USER'));
    $container->setParameter('mysql_pass', getenv('MYSQL_PASSWORD'));
    $container->setParameter('jwt_secret', getenv('JWT_SECRET_KEY'));
    $container->setParameter('app_env', getenv('APP_ENV'));

    // --- SERVIÇOS MANUAIS ---

    // 1. Serviço de Conexão com o Banco de Dados (PDO)
    $container->register('database_connection', PDO::class)
        ->setFactory([
            function () use ($container) {
                // ... (código do PDO continua o mesmo)
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $container->getParameter('mysql_host'),
                    $container->getParameter('mysql_port'),
                    $container->getParameter('mysql_db')
                );
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                try {
                    return new PDO($dsn, $container->getParameter('mysql_user'), $container->getParameter('mysql_pass'), $options);
                } catch (PDOException $e) {
                    die("Erro de conexão com o banco de dados: " . $e->getMessage());
                }
            }
        ])
        ->setPublic(true);

    // 2. Serviço do Twig (Template Engine)
    $container->register('twig', \Twig\Environment::class)
        ->setFactory([
            function () use ($container) {
                $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
                $twig = new \Twig\Environment($loader, [
                    'debug' => $container->getParameter('app_env') === 'development',
                ]);
                if ($container->getParameter('app_env') === 'development') {
                    $twig->addExtension(new \Twig\Extension\DebugExtension());
                }
                return $twig;
            }
        ])
        ->setPublic(true);

    // --- ALIASES PARA AUTOWIRING ---
    // A LINHA MÁGICA ESTÁ AQUI!
    // Dizemos ao container: "Quando a injeção de dependência pedir pela classe Twig\Environment,
    // entregue o serviço que registramos com o nome 'twig'".
    $container->setAlias(\Twig\Environment::class, 'twig');
    $container->setAlias(PDO::class, 'database_connection');


    // --- REGISTRO DE SERVIÇOS DA APLICAÇÃO (COM AUTOWIRING) ---
    $container->register(\GatePass\Api\v1\controllers\StatusController::class)
        ->setAutowired(true)
        ->setPublic(true)
        ->addTag('controller.service_arguments');

    $container->register(\GatePass\Api\v1\controllers\HomeController::class)
        ->setAutowired(true)
        ->setPublic(true)
        ->addTag('controller.service_arguments');
};

