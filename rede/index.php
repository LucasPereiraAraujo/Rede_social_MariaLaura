
<?php
// CONFIGURAÇÕES DE SESSÃO SEGURA (ajustadas para localhost)
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

session_start();

// TIMEOUT POR INATIVIDADE
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > 900)) {
    session_unset();
    session_destroy();
    header('Location: index.php?timeout');
    exit;
}
$_SESSION['ultimo_acesso'] = time();

// FUNÇÃO PARA REGENERAR ID DE SESSÃO
function regenerarSessaoSegura() {
    if (!isset($_SESSION['regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = true;
    }
}

// LOGIN
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
    $password = $_POST['password'] ?? '';

    if ($email === 'teste@teste.com' && $password === '123') {
        $_SESSION['usuario'] = $email;
        regenerarSessaoSegura();
        header('Location: feed.php');
        exit;
    } else {
        $error = "Email ou senha inválidos";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Livvic&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Senha:</label>
        <input type="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="cadastro.php">Cadastrar-se</a></p>
</div>
</body>
</html>
