<?php
require_once '../vendor/autoload.php';
if (session_status()===PHP_SESSION_NONE) session_start(); if (empty($_SESSION['usuario_id'])) { header('Location: ../index.php?msg=login'); exit; } if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
use Model\Produto;
use Model\Categoria;
use Model\Fornecedor;
$db = getConexao();
$produtoModel = new Produto($db);
$catModel = new Categoria($db);
$forModel = new Fornecedor($db);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) $msg = 'Token invalido.';
    else {
        $acao = $_POST['acao'] ?? '';
        if ($acao === 'excluir') {
            $produtoModel->excluir((int)$_POST['id']);
            $msg = 'Produto excluido.';
        } else {
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'descricao' => trim($_POST['descricao'] ?? ''),
                'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
                'fornecedor_id' => (int)($_POST['fornecedor_id'] ?? 0),
                'preco' => (float)($_POST['preco'] ?? 0),
                'quantidade' => (int)($_POST['quantidade'] ?? 0),
                'estoque_minimo' => (int)($_POST['estoque_minimo'] ?? 5),
                'status' => $_POST['status'] ?? 'ativo',
            ];
            if (!$dados['nome']) $msg = 'Nome obrigatorio.';
            elseif ($acao === 'editar') {
                $produtoModel->atualizar((int)$_POST['id'], $dados);
                $msg = 'Produto atualizado.';
            } else {
                $produtoModel->cadastrar($dados);
                $msg = 'Produto cadastrado.';
            }
        }
    }
}
$editando = null;
if (isset($_GET['editar'])) $editando = $produtoModel->porId((int)$_GET['editar']);
$produtos = $produtoModel->listar();
$categorias = $catModel->listar();
$fornecedores = $forModel->listar();
require_once '_layout.php';
layoutTopo('Produtos');
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0"><i class="bi bi-box-seam"></i> Produtos</h4>
  <a href="produtos.php" class="btn btn-sm text-white" style="background:#0d3b66">Novo produto</a>
</div>
<?php if($msg): ?><div class="alert alert-info py-2"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="POST" class="row g-2">
      <input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
      <input type="hidden" name="acao" value="<?= $editando ? 'editar' : 'cadastrar' ?>">
      <?php if($editando): ?><input type="hidden" name="id" value="<?=$editando['id']?>"><?php endif; ?>
      <div class="col-md-4"><label class="form-label">Nome *</label><input name="nome" class="form-control" required value="<?=htmlspecialchars($editando['nome']??'')?>"></div>
      <div class="col-md-4"><label class="form-label">Categoria</label>
        <select name="categoria_id" class="form-select"><option value="">--</option><?php foreach($categorias as $c): ?><option value="<?=$c['id']?>" <?=isset($editando['categoria_id'])&&$editando['categoria_id']==$c['id']?'selected':''?>><?=htmlspecialchars($c['nome'])?></option><?php endforeach; ?></select>
      </div>
      <div class="col-md-4"><label class="form-label">Fornecedor</label>
        <select name="fornecedor_id" class="form-select"><option value="">--</option><?php foreach($fornecedores as $f): ?><option value="<?=$f['id']?>" <?=isset($editando['fornecedor_id'])&&$editando['fornecedor_id']==$f['id']?'selected':''?>><?=htmlspecialchars($f['nome'])?></option><?php endforeach; ?></select>
      </div>
      <div class="col-md-3"><label class="form-label">Preco</label><input type="number" step="0.01" name="preco" class="form-control" value="<?=htmlspecialchars($editando['preco']??'0')?>"></div>
      <div class="col-md-3"><label class="form-label">Quantidade</label><input type="number" name="quantidade" class="form-control" value="<?=htmlspecialchars($editando['quantidade']??'0')?>"></div>
      <div class="col-md-3"><label class="form-label">Estoque minimo</label><input type="number" name="estoque_minimo" class="form-control" value="<?=htmlspecialchars($editando['estoque_minimo']??'5')?>"></div>
      <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="ativo" <?=($editando['status']??'')==='ativo'?'selected':''?>>ativo</option><option value="inativo" <?=($editando['status']??'')==='inativo'?'selected':''?>>inativo</option></select></div>
      <div class="col-12"><label class="form-label">Descricao</label><input name="descricao" class="form-control" value="<?=htmlspecialchars($editando['descricao']??'')?>"></div>
      <div class="col-12"><button class="btn text-white" style="background:#0d3b66"><?= $editando ? 'Atualizar' : 'Cadastrar' ?></button> <?php if($editando): ?><a href="produtos.php" class="btn btn-secondary">Cancelar</a><?php endif; ?></div>
    </form>
  </div>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<table class="table table-striped mb-0">
<thead><tr><th>Nome</th><th>Categoria</th><th>Fornecedor</th><th>Preco</th><th>Qtd</th><th>Status</th><th>Acoes</th></tr></thead>
<tbody>
<?php foreach($produtos as $p): ?>
<tr>
  <td><?=htmlspecialchars($p['nome'])?></td>
  <td><?=htmlspecialchars($p['categoria_nome']??'-')?></td>
  <td><?=htmlspecialchars($p['fornecedor_nome']??'-')?></td>
  <td>R$ <?=number_format($p['preco'],2,',','.')?></td>
  <td><span class="badge <?= $p['quantidade'] <= $p['estoque_minimo'] ? 'bg-danger' : 'bg-success' ?>"><?= $p['quantidade']?></span></td>
  <td><?=htmlspecialchars($p['status'])?></td>
  <td class="d-flex gap-1">
    <a href="produtos.php?editar=<?=$p['id']?>" class="btn btn-sm btn-warning">Editar</a>
    <form method="POST" onsubmit="return confirm('Excluir?')"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="btn btn-sm btn-danger">Excluir</button></form>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>
<?php layoutFim(); ?>
