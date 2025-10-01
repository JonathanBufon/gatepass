<?php
// public/index.php - Ponto de entrada que redireciona para a vitrine pública

// header('Location: public/listar_produtos_publico.php'); // Se estivesse na raiz do servidor
header('Location: listar_produtos_publico.php'); // Redireciona dentro da pasta public/
exit();