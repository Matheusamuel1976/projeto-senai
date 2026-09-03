<?php
require_once '../vendor/autoload.php';
if (session_status()===PHP_SESSION_NONE) session_start(); if (empty($_SESSION['usuario_id'])) { header('Location: ../index.php?msg=login'); exit; } if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
$db = getConexao();
$totProd = (int)$db->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$totCli = (int)$db->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totForn = (int)$db->query("SELECT COUNT(*) FROM fornecedores")->fetchColumn();
$baixo = $db->query("SELECT * FROM produtos WHERE quantidade <= estoque_minimo LIMIT 5")->fetchAll();
$pendentes = (int)$db->query("SELECT COUNT(*) FROM pedidos WHERE status='pendente'")->fetchColumn();
$transito = (int)$db->query("SELECT COUNT(*) FROM entregas WHERE status='em_transporte'")->fetchColumn();
$movs = $db->query("SELECT m.*, p.nome AS produto_nome FROM movimentacoes m JOIN produtos p ON p.id=m.produto_id ORDER BY m.id DESC LIMIT 5")->fetchAll();
require_once '_layout.php';
layoutTopo('Dashboard');
?>
<h3 class="fw-bold mb-3"><i class="bi bi-speedometer2"></i> Dashboard</h3>
<?php if(isset($_GET['erro']) && $_GET['erro']==='permissao'): ?><div class="alert alert-danger">Acesso restrito a administradores.</div><?php endif; ?>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card card-stat shadow-sm p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Produtos</div><div class="fs-4 fw-bold"><?= $totProd ?></div></div><div class="icon bg-primary text-white"><i class="bi bi-box-seam"></i></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat shadow-sm p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Clientes</div><div class="fs-4 fw-bold"><?= $totCli ?></div></div><div class="icon bg-success text-white"><i class="bi bi-people"></i></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat shadow-sm p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Fornecedores</div><div class="fs-4 fw-bold"><?= $totForn ?></div></div><div class="icon bg-warning text-dark"><i class="bi bi-building"></i></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat shadow-sm p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-muted small">Pedidos pendentes</div><div class="fs-4 fw-bold"><?= $pendentes ?></div></div><div class="icon bg-danger text-white"><i class="bi bi-receipt"></i></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Estoque baixo</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Produto</th><th>Qtd</th><th>Min</th></tr></thead>
          <tbody>
            <?php foreach($baixo as $p): ?>
            <tr><td><?=htmlspecialchars($p['nome'])?></td><td><span class="badge bg-danger"><?= $p['quantidade']?></span></td><td><?= $p['estoque_minimo']?></td></tr>
            <?php endforeach; ?>
            <?php if(!$baixo): ?><tr><td colspan="3" class="text-center text-muted py-2">Nenhum produto com estoque baixo.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm"><div class="card-header bg-white fw-bold">Ultimas movimentacoes</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Produto</th><th>Tipo</th><th>Qtd</th></tr></thead>
          <tbody>
            <?php foreach($movs as $m): ?>
            <tr><td><?=htmlspecialchars($m['produto_nome'])?></td><td><span class="badge <?= $m['tipo']==='entrada'?'bg-success':'bg-warning text-dark'?>"><?= $m['tipo']?></span></td><td><?= $m['quantidade']?></td></tr>
            <?php endforeach; ?>
            <?php if(!$movs): ?><tr><td colspan="3" class="text-center text-muted py-2">Sem movimentacoes.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="alert alert-info mt-3 mb-0">Entregas em transporte: <strong><?= $transito ?></strong></div>
  </div>
</div>
<?php layoutFim(); ?>
