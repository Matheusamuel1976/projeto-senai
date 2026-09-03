<?php
if (session_status()===PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
require_once '../vendor/autoload.php';
use Controller\UsuarioController;
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf']??''))) $msg='Token invalido.';
    else {
        $nome=trim($_POST['nome']??''); $email=filter_var($_POST['email']??'',FILTER_SANITIZE_EMAIL); $senha=$_POST['senha']??'';
        if (!$nome||!$email||strlen($senha)<4) $msg='Preencha todos os campos (senha min 4).';
        else {
            $c=new UsuarioController();
            if ($c->cadastrar($nome,$email,$senha,'comum')) { header('Location: ../index.php?msg=cadastro'); exit; }
            else $msg='E-mail ja cadastrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../templates/css/global.css" rel="stylesheet">
<title>Criar conta | LogiControl</title>
</head>
<body><div class="login-wrap p-3">
<div class="card login-card shadow p-4">
<h4 class="text-center fw-bold">Criar conta</h4>
<?php if($msg): ?><div class="alert alert-warning py-2"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
<div class="mb-2"><label class="form-label">Nome</label><input name="nome" class="form-control" required></div>
<div class="mb-2"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Senha</label><input type="password" name="senha" class="form-control" required></div>
<button class="btn w-100 text-white" style="background:#0d3b66">Cadastrar</button>
</form>
<p class="text-center mt-3"><a href="../index.php">Voltar ao login</a></p>
</div></div></body></html>
