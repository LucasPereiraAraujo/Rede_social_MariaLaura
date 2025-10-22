<?php
// CONFIGURAÇÕES DE SESSÃO SEGURA
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1); // comente no localhost, use apenas em HTTPS real
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

session_start();

// TIMEOUT POR INATIVIDADE (15 min)
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > 900)) {
    session_unset();
    session_destroy();
    header('Location: index.php?timeout');
    exit;
}
$_SESSION['ultimo_acesso'] = time();

// REDIRECIONA SE NÃO ESTIVER LOGADO
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

// FUNÇÃO PARA REGENERAR ID DE SESSÃO
function regenerarSessaoSegura() {
    if (!isset($_SESSION['regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = true;
    }
}

// REGENERAR ID APÓS LOGIN (uma vez)
regenerarSessaoSegura();

// ---------------------- FUNÇÕES DE POST ----------------------
function carregarPosts($dados) {
    if (!isset($dados['posts'])) return [];
    $posts = @unserialize($dados['posts']);
    return is_array($posts) ? $posts : [];
}

function adicionarPost($posts, $nome, $usuario, $texto) {
    $texto = trim($texto);
    if ($texto !== '') {
        $posts[] = [
            'autor'    => $nome ?? 'Usuário',
            'username' => $usuario ?? 'usuario',
            'conteudo' => $texto,
            'likes'    => 0
        ];
    }
    return $posts;
}

function curtirPost($posts, $id) {
    if (isset($posts[$id])) {
        $posts[$id]['likes']++;
    }
    return $posts;
}

// ---------------------- EXECUÇÃO ----------------------
$name = explode('@', $_SESSION['usuario'])[0];
$username = $name;

// Carrega posts do formulário, garantindo array seguro
$posts = carregarPosts($_POST);

// Adiciona novo post, se houver
if (isset($_POST['novoPost'])) {
    $novoTexto = filter_input(INPUT_POST, 'novoPost', FILTER_SANITIZE_SPECIAL_CHARS);
    $posts = adicionarPost($posts, $name, $username, $novoTexto);
}

// Curte post, se solicitado
if (isset($_POST['curtir'])) {
    $postId = filter_input(INPUT_POST, 'curtir', FILTER_VALIDATE_INT);
    if ($postId !== false) {
        $posts = curtirPost($posts, $postId);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil - Rede Social</title>
    <link href="https://fonts.googleapis.com/css2?family=Livvic&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Mantém o mesmo estilo do seu código original */
        body { font-family: 'Livvic', sans-serif; margin: 0; display: flex; background: #f7f7f7; }
        aside { width: 60px; background: #f0f0f0; padding: 10px; display: flex; flex-direction: column; justify-content: space-between; align-items: center; }
        .nav-icons { display: flex; flex-direction: column; gap: 20px; align-items: center; }
        aside img { width: 30px; cursor: pointer; }
        .logout-btn { background: none; border: none; cursor: pointer; color: #d00; font-weight: bold; margin-top: 20px; }
        .logout-btn:hover { text-decoration: underline; }
        main { flex: 1; padding: 20px; }
        .perfil { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; background: #fff; padding: 10px; border-radius: 8px; }
        .perfil img { width: 50px; border-radius: 50%; }
        .campo-postagem { margin-bottom: 20px; }
        .campo-postagem textarea { width: 100%; padding: 10px; resize: none; height: 60px; }
        .campo-postagem button { margin-top: 10px; padding: 10px 20px; cursor: pointer; border-radius: 6px; border: none; background: #007bff; color: #fff; }
        .campo-postagem button:hover { background: #0056b3; }
        .postagens { display: flex; flex-direction: column; gap: 20px; }
        .postagem { border: 1px solid #ddd; background: #fff; padding: 15px; border-radius: 10px; }
        .cabecalho { display: flex; align-items: center; gap: 10px; font-weight: bold; margin-bottom: 8px; }
        .cabecalho img { width: 40px; border-radius: 50%; }
        .conteudo { margin-bottom: 8px; }
        .interacoes button { border: none; background: none; color: #555; cursor: pointer; padding: 0; font-size: 14px; }
        .interacoes button:hover { color: #007bff; }
    </style>
</head>
<body>
    <aside>
        <div class="nav-icons">
            <img src="https://cdn-icons-png.flaticon.com/512/25/25694.png" alt="Início" />
            <img src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Buscar" />
            <img src="https://cdn-icons-png.flaticon.com/512/1828/1828817.png" alt="Curtir" />
            <img src="https://cdn-icons-png.flaticon.com/512/1077/1077063.png" alt="Perfil" />
        </div>
        <form action="logout.php" method="post">
            <button class="logout-btn" type="submit">Sair</button>
        </form>
    </aside>

    <main>
        <div class="perfil">
            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Foto de Perfil" />
            <div>
                <div class="nome"><?php echo htmlspecialchars($name); ?></div>
                <div class="usuario">@<?php echo htmlspecialchars($username); ?></div>
            </div>
            <div style="margin-left:auto;">
                <button>Editar Perfil</button>
            </div>
        </div>

        <div class="campo-postagem">
            <form method="POST">
                <textarea name="novoPost" placeholder="Quais são as novidades?"></textarea>
                <input type="hidden" name="posts" value="<?php echo htmlspecialchars(serialize($posts)); ?>">
                <button type="submit">Postar</button>
            </form>
        </div>

        <div class="postagens">
            <?php foreach ($posts as $id => $post): ?>
                <div class="postagem">
                    <div class="cabecalho">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Usuário" />
                        <div><?php echo htmlspecialchars($post['autor']); ?> (@<?php echo htmlspecialchars($post['username']); ?>)</div>
                    </div>
                    <div class="conteudo"><?php echo htmlspecialchars($post['conteudo']); ?></div>
                    <div class="interacoes">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="posts" value="<?php echo htmlspecialchars(serialize($posts)); ?>">
                            <button type="submit" name="curtir" value="<?php echo $id; ?>">❤️ Curtir (<?php echo $post['likes']; ?>)</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
