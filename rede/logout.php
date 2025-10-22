<?php
//  Inicia a sessão
session_start();

//  Limpa todas as variáveis de sessão
session_unset();

//  Destroi o cookie de sessão no navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

//  Destroi a sessão no servidor
session_destroy();

// Redireciona para a página de login
header('Location: index.php');
exit;
