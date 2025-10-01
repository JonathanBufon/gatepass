<?php
// src/Core/Auth.php

namespace GatePass\Core;

/*

  Classe para gerenciar a autenticação e verificação de sessão.

 */

class Auth
{
    public static function verificarLogin(string $redirectPage = 'login.php'): void
    {
        // Verifica se a sessão já foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o ID do usuário está setado na sessão
        if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
            // Se não estiver logado, redireciona
            header("Location: " . $redirectPage);
            exit();
        }
        // O usuário está logado, a execução do script continua normalmente.
    }
}