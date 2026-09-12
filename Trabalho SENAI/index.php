<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require_once 'vendor/autoload.php';

use Controller\UsuarioController;

$controller = new UsuarioController();
$msg = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logout') {
        $msg = 'Sessao encerrada.';
    } elseif ($_GET['msg'] === 'cadastro') {
        $msg = 'Cadastro realizado! Faca login.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $msg = 'Token invalido.';
    } else {
        $email = filter_var(
            $_POST['email'] ?? '',
            FILTER_SANITIZE_EMAIL
        );
        $senha = $_POST['senha'] ?? '';

        if ($controller->login($email, $senha)) {
            if (($_SESSION['usuario_tipo'] ?? '') === 'admin') {
                header('Location: View/admin/painel_admin.php');
            } else {
                header('Location: View/dashboard.php');
            }
            exit;
        }
        $msg = 'E-mail ou senha invalidos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >
    <link href="templates/css/global.css" rel="stylesheet">

    <title>LogiControl | Entrar</title>
</head>
<body>
    <div class="login-wrap p-3">
        <div class="card login-card shadow p-4">
            <div class="text-center mb-3">
                <div
                    class="mx-auto d-flex align-items-center justify-content-center rounded-circle"
                    style="width:56px;height:56px;background:#0d3b66;color:#fff;font-size:24px"
                >
                    <i class="bi bi-truck"></i>
                </div>

                <h3 class="fw-bold mt-2">LogiControl</h3>

                <p class="text-muted">
                    Sistema de Gerenciamento Logistico
                </p>
            </div>
            <?php if ($msg): ?>
                <div class="alert alert-info py-2">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input
                    type="hidden"
                    name="csrf"
                    value="<?= htmlspecialchars($_SESSION['csrf']) ?>"
                >
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required
                        placeholder="admin@logistica.com"
                    >
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <input
                        type="password"
                        name="senha"
                        class="form-control"
                        required
                        placeholder="******"
                    >
                </div>
                <button
                    class="btn w-100 text-white"
                    style="background:#0d3b66"
                >
                    Entrar
                </button>
            </form>
            <p class="text-center mt-3 mb-0">
                <a href="View/register.php">Criar conta</a>
            </p>

            <div class="mt-3 p-2 bg-light rounded small">
                <strong>Demonstracao:</strong><br>
                Admin: admin@logistica.com / admin123<br>
                Usuario: usuario@logistica.com / usuario123
            </div>
        </div>
    </div>
</body>
</html>
