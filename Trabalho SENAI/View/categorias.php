<?php
require_once '../vendor/autoload.php';
if (session_status()===PHP_SESSION_NONE) session_start(); if (empty($_SESSION['usuario_id'])) { header('Location: ../index.php?msg=login'); exit; } if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
use Model\Categoria;
$db = getConexao();
$m = new Categoria($db);
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf']??''))) $msg='Token invalido.';
    else {
        $acao=$_POST['acao']??'';
        if ($acao==='excluir') { $m->excluir((int)$_POST['id']); $msg='Categoria excluida.'; }
        else {
            $nome=trim($_POST['nome']??''); $desc=trim($_POST['descricao']??'');
            if(!$nome) $msg='Nome obrigatorio.';
            elseif($acao==='editar'){ $m->atualizar((int)$_POST['id'],$nome,$desc); $msg='Categoria atualizada.'; }
            else { $m->cadastrar($nome,$desc); $msg='Categoria cadastrada.'; }
        }
    }
}
$edit=null; if(isset($_GET['editar'])) $edit=$m->porId((int)$_GET['editar']);
$lista=$m->listar();
require_once '_layout.php'; layoutTopo('Categorias');
?>
<h4 class="fw-bold"><i class="bi bi-tags"></i> Categorias</h4>
<?php if($msg): ?><div class="alert alert-info py-2"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<div class="card shadow-sm mb-3"><div class="card-body">
<form method="POST" class="row g-2">
<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>">
<input type="hidden" name="acao" value="<?= $edit?'editar':'cadastrar'?>">
<?php if($edit): ?><input type="hidden" name="id" value="<?=$edit['id']?>"><?php endif; ?>
<div class="col-md-4"><label class="form-label">Nome *</label><input name="nome" class="form-control" required value="<?=htmlspecialchars($edit['nome']??'')?>"></div>
<div class="col-md-5"><label class="form-label">Descricao</label><input name="descricao" class="form-control" value="<?=htmlspecialchars($edit['descricao']??'')?>"></div>
<div class="col-md-3 d-flex align-items-end gap-2"><button class="btn text-white" style="background:#0d3b66"><?= $edit?'Atualizar':'Cadastrar'?></button><?php if($edit): ?><a href="categorias.php" class="btn btn-secondary">Cancelar</a><?php endif; ?></div>
</form>
</div></div>
<div class="card shadow-sm"><div class="card-body p-0"><table class="table mb-0">
<thead><tr><th>Nome</th><th>Descricao</th><th>Acoes</th></tr></thead>
<tbody><?php foreach($lista as $c): ?><tr><td><?=htmlspecialchars($c['nome'])?></td><td><?=htmlspecialchars($c['descricao']??'-')?></td><td class="d-flex gap-1"><a href="categorias.php?editar=<?=$c['id']?>" class="btn btn-sm btn-warning">Editar</a><form method="POST" onsubmit="return confirm('Excluir?')"><input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'])?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn btn-sm btn-danger">Excluir</button></form></td></tr><?php endforeach; ?></tbody>
</table></div></div>
<?php layoutFim(); ?>
