<?php
require_once '../../vendor/autoload.php';
if (session_status()===PHP_SESSION_NONE) session_start(); if (empty($_SESSION['usuario_id'])) { header('Location: ../../index.php?msg=login'); exit; } if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') { header('Location: ../dashboard.php?erro=permissao'); exit; } if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
$db = getConexao();
$totProd = (int)$db->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totCli = (int)$db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totForn = (int)$db->query("SELECT COUNT(*) FROM fornecedores")->fetchColumn();
$totUsers = (int)$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$baixo = $db->query("SELECT * FROM produtos WHERE quantidade <= estoque_minimo ORDER BY quantidade ASC LIMIT 5")->fetchAll();
$pendentes = (int)$db->query("SELECT COUNT(*) FROM pedidos WHERE status='pendente'")->fetchColumn();
$transito = (int)$db->query("SELECT COUNT(*) FROM entregas WHERE status='em_transporte'")->fetchColumn();
$usuarios = $db->query("SELECT id,nome,email,tipo FROM usuarios ORDER BY id DESC LIMIT 5")->fetchAll();
$movs = $db->query("SELECT m.*, p.nome AS produto_nome FROM movimentacoes m JOIN produtos p ON p.id=m.produto_id ORDER BY m.id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../../templates/css/global.css" rel="stylesheet">
<title>Painel Admin | LogiControl</title>
</head>
<body>
<nav class="navbar navbar-dark navbar-logistica px-3">
  <a class="navbar-brand" href="painel_admin.php"><i class="bi bi-shield-lock"></i> LogiControl Admin</a>
  <div class="d-flex align-items-center gap-3 text-white">
    <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?> (admin)</span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Sair</a>
  </div>
</nav>
<div class="container-fluid"><div class="row">
<div class="col-md-2 sidebar p-3">
  <nav class="nav flex-column">
    <a class="nav-link active" href="painel_admin.php"><i class="bi bi-speedometer2"></i> Dashboard Admin</a>
    <a class="nav-link" href="../dashboard.php"><i class="bi bi-house"></i> Dashboard Geral</a>
    <a class="nav-link" href="usuarios.php"><i class="bi bi-people-fill"></i> Usuarios</a>
    <a class="nav-link" href="../produtos.php"><i class="bi bi-box-seam"></i> Produtos</a>
    <a class="nav-link" href="../categorias.php"><i class="bi bi-tags"></i> Categorias</a>
    <a class="nav-link" href="../fornecedores.php"><i class="bi bi-building"></i> Fornecedores</a>
    <a class="nav-link" href="../clientes.php"><i class="bi bi-people"></i> Clientes</a>
    <a class="nav-link" href="../estoque.php"><i class="bi bi-stack"></i> Estoque</a>
    <a class="nav-link" href="../movimentacoes.php"><i class="bi bi-arrow-left-right"></i> Movimentacoes</a>
    <a class="nav-link" href="../pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
    <a class="nav-link" href="../entregas.php"><i class="bi bi-geo-alt"></i> Entregas</a>
  </nav>
</div>
<div class="col-md-10 p-4">
<h3 class="fw-bold mb-3"><i class="bi bi-shield-lock"></i> Painel Administrativo</h3>
<div class="row g-3 mb-4">
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Produtos</div><div class="fs-4 fw-bold"><?= $totProd ?></div></div></div>
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Clientes</div><div class="fs-4 fw-bold"><?= $totCli ?></div></div></div>
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Fornecedores</div><div class="fs-4 fw-bold"><?= $totForn ?></div></div></div>
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Usuarios</div><div class="fs-4 fw-bold"><?= $totUsers ?></div></div></div>
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Pendentes</div><div class="fs-4 fw-bold"><?= $pendentes ?></div></div></div>
  <div class="col-md-2"><div class="card card-stat shadow-sm p-3"><div class="text-muted small">Em transporte</div><div class="fs-4 fw-bold"><?= $transito ?></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm mb-3"><div class="card-header bg-white fw-bold">Estoque baixo</div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Produto</th><th>Qtd</th><th>Min</th></tr></thead><tbody><?php foreach($baixo as $p): ?><tr><td><?=htmlspecialchars($p['nome'])?></td><td><span class="badge bg-danger"><?= $p['quantidade']?></span></td><td><?= $p['estoque_minimo']?></td></tr><?php endforeach; ?><?php if(!$baixo): ?><tr><td colspan="3" class="text-center text-muted py-2">Nenhum produto com estoque baixo.</td></tr><?php endif; ?></tbody></table></div></div>
    <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Ultimas movimentacoes</div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Produto</th><th>Tipo</th><th>Qtd</th></tr></thead><tbody><?php foreach($movs as $m): ?><tr><td><?=htmlspecialchars($m['produto_nome'])?></td><td><span class="badge <?= $m['tipo']==='entrada'?'bg-success':'bg-warning text-dark'?>"><?= $m['tipo']?></span></td><td><?= $m['quantidade']?></td></tr><?php endforeach; ?></tbody></table></div></div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Usuarios recentes</div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th></tr></thead><tbody><?php foreach($usuarios as $u): ?><tr><td><?=htmlspecialchars($u['nome'])?></td><td><?=htmlspecialchars($u['email'])?></td><td><span class="badge <?= $u['tipo']==='admin'?'bg-dark':'bg-secondary'?>"><?=htmlspecialchars($u['tipo'])?></span></td></tr><?php endforeach; ?></tbody></table></div></div>
    <a href="usuarios.php" class="btn btn-sm text-white mt-3" style="background:#0d3b66">Gerenciar usuarios</a>
  </div>
</div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
