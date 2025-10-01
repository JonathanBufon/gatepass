<?php
// public/logout.php

// Inicia a sessão para poder acessá-la e destruí-la
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se a sessão for controlada por cookies, apaga o cookie de sessão.
// Isso irá invalidar a sessão também no navegador do cliente.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão.
session_destroy();

// Redireciona o usuário para a página de login
header("Location: login.php");
exit();


// KABUMMMMMMMMMMMM