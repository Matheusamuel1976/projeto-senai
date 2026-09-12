<?php
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php?msg=login');
    exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

use Model\Movimentacao;
use Model\Produto;

$db = getConexao();
$movModel = new Movimentacao($db);
$prodModel = new Produto($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $msg = 'Token invalido.';
    } else {
        $pid = (int) ($_POST['produto_id'] ?? 0);
        $tipo = $_POST['tipo'] ?? '';
        $qtd = (int) ($_POST['quantidade'] ?? 0);
        $obs = trim($_POST['observacao'] ?? '');

        if (!$pid || !$qtd || !in_array($tipo, ['entrada', 'saida'], true)) {
            $msg = 'Preencha todos os campos.';
        } elseif (!$movModel->registrar($pid, $tipo, $qtd, $obs, (int) $_SESSION['usuario_id'])) {
            $msg = 'Erro: estoque insuficiente ou falha.';
        } else {
            $msg = 'Movimentacao registrada.';
        }
    }
}

$lista = $movModel->listar();
$produtos = $prodModel->listar();

require_once '_layout.php';
layoutTopo('Movimentacoes');
?>

<h4 class="fw-bold"><i class="bi bi-arrow-left-right"></i> Movimentacoes de Estoque</h4>

<?php if ($msg): ?>
<div class="alert alert-info py-2"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="POST" class="row g-2">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <div class="col-md-4">
                <label class="form-label">Produto *</label>
                <select name="produto_id" class="form-select" required>
                    <option value="">--</option>
                    <?php foreach ($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['nome']) ?> (<?= $p['quantidade'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Tipo *</label>
                <select name="tipo" class="form-select" required>
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saida</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Qtd *</label>
                <input type="number" name="quantidade" class="form-control" min="1" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Observacao</label>
                <input name="observacao" class="form-control">
            </div>

            <div class="col-12">
                <button class="btn text-white" style="background:#0d3b66">Registrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Qtd</th>
                    <th>Usuario</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['produto_nome']) ?></td>
                    <td>
                        <span class="badge <?= $m['tipo'] === 'entrada' ? 'bg-success' : 'bg-danger' ?>">
                            <?= htmlspecialchars($m['tipo']) ?>
                        </span>
                    </td>
                    <td><?= $m['quantidade'] ?></td>
                    <td><?= htmlspecialchars($m['usuario_nome'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($m['criado_em']) ?></td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$lista): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-2">Sem movimentacoes.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php layoutFim(); ?>
