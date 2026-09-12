<?php
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php?msg=login');
    exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

use Model\Fornecedor;

$db = getConexao();
$m = new Fornecedor($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $msg = 'Token invalido.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'excluir') {
            $m->excluir((int) $_POST['id']);
            $msg = 'Fornecedor excluido.';
        } else {
            $dados = [
                'nome' => trim($_POST['nome'] ?? ''),
                'cnpj_cpf' => trim($_POST['cnpj_cpf'] ?? ''),
                'telefone' => trim($_POST['telefone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'endereco' => trim($_POST['endereco'] ?? '')
            ];

            if (!$dados['nome']) {
                $msg = 'Nome obrigatorio.';
            } elseif ($acao === 'editar') {
                $m->atualizar((int) $_POST['id'], $dados);
                $msg = 'Fornecedor atualizado.';
            } else {
                $m->cadastrar($dados);
                $msg = 'Fornecedor cadastrado.';
            }
        }
    }
}

$edit = null;

if (isset($_GET['editar'])) {
    $edit = $m->porId((int) $_GET['editar']);
}

$lista = $m->listar();

require_once '_layout.php';
layoutTopo('Fornecedores');
?>

<h4 class="fw-bold"><i class="bi bi-building"></i> Fornecedores</h4>

<?php if ($msg): ?>
<div class="alert alert-info py-2"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="POST" class="row g-2">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="acao" value="<?= $edit ? 'editar' : 'cadastrar' ?>">

            <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= $edit['id'] ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label class="form-label">Nome *</label>
                <input name="nome" class="form-control" required value="<?= htmlspecialchars($edit['nome'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">CNPJ/CPF</label>
                <input name="cnpj_cpf" class="form-control" value="<?= htmlspecialchars($edit['cnpj_cpf'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Telefone</label>
                <input name="telefone" class="form-control" value="<?= htmlspecialchars($edit['telefone'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">E-mail</label>
                <input name="email" class="form-control" value="<?= htmlspecialchars($edit['email'] ?? '') ?>">
            </div>

            <div class="col-12">
                <label class="form-label">Endereco</label>
                <input name="endereco" class="form-control" value="<?= htmlspecialchars($edit['endereco'] ?? '') ?>">
            </div>

            <div class="col-12">
                <button class="btn text-white" style="background:#0d3b66">
                    <?= $edit ? 'Atualizar' : 'Cadastrar' ?>
                </button>

                <?php if ($edit): ?>
                <a href="fornecedores.php" class="btn btn-secondary">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CNPJ/CPF</th>
                    <th>Telefone</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['nome']) ?></td>
                    <td><?= htmlspecialchars($f['cnpj_cpf'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($f['telefone'] ?? '-') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="fornecedores.php?editar=<?= $f['id'] ?>" class="btn btn-sm btn-warning">
                                Editar
                            </a>

                            <form method="POST" onsubmit="return confirm('Excluir?')">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                <button class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php layoutFim(); ?>
