<?php
require_once '../../vendor/autoload.php';
if (session_status()===PHP_SESSION_NONE) session_start(); if (empty($_SESSION['usuario_id'])) { header('Location: ../../index.php?msg=login'); exit; } if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') { header('Location: ../dashboard.php?erro=permissao'); exit; } if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
use Model\Usuario;
$db = getConexao();
$m = new Usuario($db);
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf']??''))) $msg='Token invalido.';
    elseif (($_POST['acao']??'')==='excluir') {
        $id=(int)$_POST['id'];
        if ($id===(int)$_SESSION['usuario_id']) $msg='Nao e possivel excluir o proprio usuario.';
        else { $m->excluir($id); $msg='Usuario excluido.'; }
    }
}
$lista=$m->listar();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../../templates/css/global.css" rel="stylesheet">
<title>Usuarios | Admin</title>
</head>
<body>
<nav class="navbar navbar-dark navbar-logistica px-3">
  <a class="navbar-brand" href="painel_admin.php"><i class="bi bi-shield-lock"></i> LogiControl Admin</a>
  <div class="d-flex align-items-center gap-2 text-white">
    <span><?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?> (admin)</span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Sair</a>
  </div>
</nav>
<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar p-3">
  <nav class="nav flex-column">
    <a class="nav-link" href="painel_admin.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
    <a class="nav-link active" href="usuarios.php"><i class="bi bi-people-fill"></i> Usuarios</a>
    <a class="nav-link" href="../dashboard.php"><i class="bi bi-house"></i> Dashboard Geral</a>
    <a class="nav-link" href="../produtos.php"><i class="bi bi-box-seam"></i> Produtos</a>
    <a class="nav-link" href="../categorias.php"><i class="bi bi-tags"></i> Categorias</a>
    <a class="nav-link" href="../fornecedores.php"><i class="bi bi-building"></i> Fornecedores</a>
    <a class="nav-link" href="../clientes.php"><i class="bi bi-people"></i> Clientes</a>
    <a class="nav-link" href="../pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
    <a class="nav-link" href="../entregas.php"><i class="bi bi-geo-alt"></i> Entregas</a>
  </nav>
</div>
<div class="col-md-10 p-4">
<h4 class="fw-bold"><i class="bi bi-people-fill"></i> Usuarios</h4>
<?php if($msg): ?><div class="alert alert-info py-2"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table mb-0">
<thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Acoes</th></tr></thead>
<tbody>
<?php foreach($lista as $u): ?>
<tr>
  <td><?= $u['id']?></td>
  <td><?=htmlspecialchars($u['nome'])?></td>
  <td><?=htmlspecialchars($u['email'])?></td>
  <td><span class="badge <?= $u['tipo']==='admin'?'bg-dark':'bg-secondary'?>"><?=htmlspecialchars($u['tipo'])?></span></td>
  <td>
    <?php if((int)$u['id'] !== (int)$_SESSION['usuario_id']): ?>
    <form method="POST" onsubmit="return confirm('Excluir usuario?')" class="d-inline"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= $u['id']?>"><button class="btn btn-sm btn-danger">Excluir</button></form>
    <?php else: ?><span class="text-muted small">voce</span><?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
