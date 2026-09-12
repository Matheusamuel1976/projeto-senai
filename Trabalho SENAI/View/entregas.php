<?php
require_once '../vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php?msg=login');
    exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

use Model\Entrega;

$db = getConexao();
$m = new Entrega($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $msg = 'Token invalido.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'excluir') {
            $m->excluir((int) $_POST['id']);
            $msg = 'Entrega excluida.';
        } elseif ($acao === 'atualizar') {
            $m->atualizar((int) $_POST['id'], $_POST['status'], trim($_POST['observacao'] ?? ''));
            $msg = 'Entrega atualizada.';
        } elseif ($acao === 'cadastrar') {
            $pid = (int) ($_POST['pedido_id'] ?? 0);
            $end = trim($_POST['endereco'] ?? '');
            $data = $_POST['data_prevista'] ?? null;
            $status = $_POST['status'] ?? 'pendente';
            $obs = trim($_POST['observacao'] ?? '');

            if (!$pid || !$end) {
                $msg = 'Pedido e endereco obrigatorios.';
            } elseif ($m->cadastrar($pid, $end, $data ?: null, $status, $obs)) {
                $msg = 'Entrega cadastrada.';
            } else {
                $msg = 'Erro ao cadastrar.';
            }
        }
    }
}

$lista = $m->listar();

$pedidos = $db->query("
    SELECT p.id, c.nome AS cliente_nome
    FROM pedidos p
    JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.id DESC
")->fetchAll();

require_once '_layout.php';
layoutTopo('Entregas');
?>

<h4 class="fw-bold"><i class="bi bi-geo-alt"></i> Entregas</h4>

<?php if ($msg): ?>
<div class="alert alert-info py-2"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="POST" class="row g-2">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="acao" value="cadastrar">

            <div class="col-md-3">
                <label class="form-label">Pedido *</label>
                <select name="pedido_id" class="form-select" required>
                    <option value="">--</option>
                    <?php foreach ($pedidos as $p): ?>
                    <option value="<?= $p['id'] ?>">#<?= $p['id'] ?> - <?= htmlspecialchars($p['cliente_nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Endereco *</label>
                <input name="endereco" class="form-control" required>
            </div>

            <div class="col-md-2">
                <label class="form-label">Data prevista</label>
                <input type="date" name="data_prevista" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="pendente">pendente</option>
                    <option value="em_transporte">em_transporte</option>
                    <option value="entregue">entregue</option>
                    <option value="cancelado">cancelado</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Observacao</label>
                <input name="observacao" class="form-control">
            </div>

            <div class="col-12">
                <button class="btn text-white" style="background:#0d3b66">Cadastrar entrega</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Endereco</th>
                    <th>Data prev.</th>
                    <th>Status</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $e): ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td>#<?= $e['pedido_id'] ?></td>
                    <td><?= htmlspecialchars($e['cliente_nome']) ?></td>
                    <td><?= htmlspecialchars($e['endereco']) ?></td>
                    <td><?= htmlspecialchars($e['data_prevista'] ?? '-') ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($e['status']) ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="acao" value="atualizar">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width:auto">
                                    <option value="pendente">pendente</option>
                                    <option value="em_transporte">em_transporte</option>
                                    <option value="entregue">entregue</option>
                                    <option value="cancelado">cancelado</option>
                                </select>
                                <input type="hidden" name="observacao" value="<?= htmlspecialchars($e['observacao'] ?? '') ?>">
                                <button class="btn btn-sm btn-warning">OK</button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Excluir?')">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
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
