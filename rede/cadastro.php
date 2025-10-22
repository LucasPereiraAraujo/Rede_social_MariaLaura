<?php
$error = '';
$success = '';

// Variáveis já inicializadas para evitar "undefined"
$name = $username = $email = $birth = $gender = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ----- Coleta bruta -----
    $name_raw             = $_POST['name'] ?? '';
    $username_raw         = $_POST['username'] ?? '';
    $email_raw            = $_POST['email'] ?? '';
    $password             = $_POST['password'] ?? '';
    $password_confirm     = $_POST['password_confirm'] ?? '';
    $birth_raw            = $_POST['birthdate'] ?? '';
    $gender_raw           = $_POST['gender'] ?? '';

    // ----- Sanitização -----
    $name     = trim(strip_tags($name_raw));
    $username = trim(strip_tags($username_raw));
    $email    = filter_var(trim($email_raw), FILTER_SANITIZE_EMAIL);
    $birth    = trim(strip_tags($birth_raw));
    $gender   = trim(strip_tags($gender_raw));

    // ----- Validações -----
    if ($name === '' || $username === '' || $email === '' || $password === '' || $password_confirm === '' || $birth === '' || $gender === '') {
        $error = "Preencha todos os campos";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido";
    } elseif ($password !== $password_confirm) {
        $error = "As senhas não conferem";
    } elseif (strlen($password) < 6) {
        $error = "A senha deve ter pelo menos 6 caracteres";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "A senha deve ter pelo menos uma letra maiúscula";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "A senha deve ter pelo menos um número";
    } elseif (!in_array($gender, ['feminino', 'masculino', 'outro'], true)) {
        $error = "Gênero inválido";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $birth);
        $dateErrors = DateTime::getLastErrors();
        if (!$d || ($dateErrors['warning_count'] + $dateErrors['error_count'] > 0)) {
            $error = "Data de nascimento inválida";
        } else {
            // Hash da senha (exemplo)
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Sucesso -> redireciona para feed.php
            echo '
            <form id="redir" method="POST" action="feed.php">
                <input type="hidden" name="name" value="' . htmlspecialchars($name) . '">
                <input type="hidden" name="username" value="' . htmlspecialchars($username) . '">
            </form>
            <script>document.getElementById("redir").submit();</script>
            ';
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link href="https://fonts.googleapis.com/css2?family=Livvic&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Cadastro</h1>
        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

        <form method="POST">
            <label>Nome completo*:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label>Nome de usuário (username)*:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <label>Email*:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label>Senha*:</label>
            <input type="password" name="password" required>

            <label>Confirmar Senha*:</label>
            <input type="password" name="password_confirm" required>

            <label>Data de nascimento:</label>
            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($birth); ?>" required>

            <label>Gênero:</label>
            <select name="gender" required>
                <option value="">Selecione</option>
                <option value="feminino" <?php if ($gender === 'feminino') echo 'selected'; ?>>Feminino</option>
                <option value="masculino" <?php if ($gender === 'masculino') echo 'selected'; ?>>Masculino</option>
                <option value="outro" <?php if ($gender === 'outro') echo 'selected'; ?>>Outro</option>
            </select>

            <button type="submit">Cadastrar</button>
        </form>

        <p><a href="index.php">Voltar ao login</a></p>
    </div>
</body>

</html>