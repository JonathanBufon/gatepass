<?php
// public/logout_cliente.php

// Inicia a sessão para poder acessá-la e destruí-la
session_start();

// Remove as variáveis de sessão específicas do cliente
unset($_SESSION['id_cliente_logado']);
unset($_SESSION['email_cliente_logado']);
unset($_SESSION['nome_cliente_logado']);

// Se a sessão for controlada por cookies, apaga o cookie de sessão.
// Isso irá invalidar a sessão também no navegador do cliente.
// Importante: session_name() retorna o nome do cookie de sessão (geralmente 'PHPSESSID')
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão no servidor.
// Isso é seguro, pois já limpamos as chaves específicas do cliente.
session_destroy();

// Redireciona o cliente para a página de login de cliente
header("Location: login_cliente.php");
exit();


// KABUMMMMMMMMMMMM