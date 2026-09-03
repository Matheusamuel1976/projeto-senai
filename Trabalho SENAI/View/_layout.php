<?php
function layoutTopo(string $titulo): void { ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="../templates/css/global.css" rel="stylesheet">
<title><?= htmlspecialchars($titulo) ?> | Logistica</title>
</head>
<body>
<nav class="navbar navbar-dark navbar-logistica px-3">
  <a class="navbar-brand" href="dashboard.php"><i class="bi bi-truck"></i> LogiControl</a>
  <div class="d-flex align-items-center gap-3 text-white">
    <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nome'] ?? '') ?> (<?= htmlspecialchars($_SESSION['usuario_tipo'] ?? '') ?>)</span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
  </div>
</nav>
<div class="container-fluid">
<div class="row">
<div class="col-md-2 sidebar p-3">
  <nav class="nav flex-column">
    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a class="nav-link" href="produtos.php"><i class="bi bi-box-seam"></i> Produtos</a>
    <a class="nav-link" href="categorias.php"><i class="bi bi-tags"></i> Categorias</a>
    <a class="nav-link" href="fornecedores.php"><i class="bi bi-building"></i> Fornecedores</a>
    <a class="nav-link" href="clientes.php"><i class="bi bi-people"></i> Clientes</a>
    <a class="nav-link" href="estoque.php"><i class="bi bi-stack"></i> Estoque</a>
    <a class="nav-link" href="movimentacoes.php"><i class="bi bi-arrow-left-right"></i> Movimentacoes</a>
    <a class="nav-link" href="pedidos.php"><i class="bi bi-receipt"></i> Pedidos</a>
    <a class="nav-link" href="entregas.php"><i class="bi bi-geo-alt"></i> Entregas</a>
    <?php if(($_SESSION['usuario_tipo'] ?? '')==='admin'): ?><a class="nav-link text-primary fw-bold" href="admin/painel_admin.php"><i class="bi bi-shield-lock"></i> Painel Admin</a><?php endif; ?>
  </nav>
</div>
<div class="col-md-10 p-4">
<?php }
function layoutFim(): void { ?>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
<?php }
