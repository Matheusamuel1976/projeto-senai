<?php
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php?msg=login');
    exit;
}

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

use Model\Pedido;
use Model\Cliente;
use Model\Produto;

$db = getConexao();
$pedidoModel = new Pedido($db);
$cliModel = new Cliente($db);
$prodModel = new Produto($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $msg = 'Token invalido.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'excluir') {
            $pedidoModel->excluir((int) $_POST['id']);
            $msg = 'Pedido excluido.';
        } elseif ($acao === 'status') {
            $pedidoModel->atualizarStatus((int) $_POST['id'], $_POST['status']);
            $msg = 'Status atualizado.';
        } elseif ($acao === 'criar') {
            $clienteId = (int) ($_POST['cliente_id'] ?? 0);
            $status = $_POST['status'] ?? 'pendente';
            $obs = trim($_POST['observacao'] ?? '');
            $pids = $_POST['produto_id'] ?? [];
            $qtds = $_POST['quantidade'] ?? [];
            $itens = [];

            foreach ($pids as $i => $pid) {
                $pid = (int) $pid;
                $qtd = (int) ($qtds[$i] ?? 0);

                if ($pid && $qtd > 0) {
                    $p = $prodModel->porId($pid);

                    if ($p) {
                        $itens[] = [
                            'produto_id' => $pid,
                            'quantidade' => $qtd,
                            'preco' => (float) $p['preco']
                        ];
                    }
                }
            }

            if (!$clienteId) {
                $msg = 'Selecione o cliente.';
            } elseif (!$itens) {
                $msg = 'Adicione pelo menos um produto.';
            } elseif ($pedidoModel->criar($clienteId, $status, $obs, $itens)) {
                $msg = 'Pedido criado.';
            } else {
                $msg = 'Erro ao criar pedido.';
            }
        }
    }
}

$pedidos = $pedidoModel->listar();
$clientes = $cliModel->listar();
$produtos = $prodModel->listar();

$ver = null;

if (isset($_GET['ver'])) {
    $ver = $pedidoModel->porId((int) $_GET['ver']);
    $verItens = $ver ? $pedidoModel->itens($ver['id']) : [];
}

require_once '_layout.php';
layoutTopo('Pedidos');
?>

<h4 class="fw-bold"><i class="bi bi-receipt"></i> Pedidos</h4>

<?php if ($msg): ?>
<div class="alert alert-info py-2"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($ver): ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h5>Pedido #<?= $ver['id'] ?> - <?= htmlspecialchars($ver['status']) ?></h5>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Preco unit.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($verItens as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['produto_nome']) ?></td>
                    <td><?= $it['quantidade'] ?></td>
                    <td>R$ <?= number_format($it['preco_unitario'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p>Total: <strong>R$ <?= number_format($ver['total'], 2, ',', '.') ?></strong></p>
        <a href="pedidos.php" class="btn btn-secondary btn-sm">Voltar</a>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form method="POST" id="formPedido">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="acao" value="criar">

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Cliente *</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">--</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pendente">pendente</option>
                        <option value="em_preparacao">em_preparacao</option>
                        <option value="em_transporte">em_transporte</option>
                        <option value="entregue">entregue</option>
                        <option value="cancelado">cancelado</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Observacao</label>
                    <input name="observacao" class="form-control">
                </div>
            </div>

            <div id="itens" class="mt-3">
                <div class="row g-2 item-linha mb-2">
                    <div class="col-md-6">
                        <select name="produto_id[]" class="form-select">
                            <option value="">Produto</option>
                            <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['nome']) ?> - R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input
                            type="number"
                            name="quantidade[]"
                            class="form-control"
                            placeholder="Qtd"
                            min="1"
                            value="1"
                        >
                    </div>

                    <div class="col-md-3">
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm"
                            onclick="this.closest('.item-linha').remove()"
                        >
                            Remover
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addItem()">
                + Produto
            </button>

            <button class="btn text-white btn-sm" style="background:#0d3b66">
                Criar pedido
            </button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Acoes</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['cliente_nome']) ?></td>
                    <td>
                        <span class="badge bg-secondary">
                            <?= htmlspecialchars($p['status']) ?>
                        </span>
                    </td>
                    <td>R$ <?= number_format($p['total'], 2, ',', '.') ?></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="pedidos.php?ver=<?= $p['id'] ?>" class="btn btn-sm btn-info">
                                Ver
                            </a>

                            <form method="POST" class="d-flex gap-1">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="acao" value="status">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">

                                <select name="status" class="form-select form-select-sm" style="width:auto">
                                    <option value="pendente">pendente</option>
                                    <option value="em_preparacao">em_preparacao</option>
                                    <option value="em_transporte">em_transporte</option>
                                    <option value="entregue">entregue</option>
                                    <option value="cancelado">cancelado</option>
                                </select>

                                <button class="btn btn-sm btn-warning">OK</button>
                            </form>

                            <form method="POST" onsubmit="return confirm('Excluir?')">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

<script>
function addItem() {
    const c = document.getElementById('itens');
    const d = c.firstElementChild.cloneNode(true);

    d.querySelectorAll('select, input').forEach(e => {
        e.value = e.tagName === 'SELECT' ? '' : 1;
    });

    c.appendChild(d);
}
</script>

<?php layoutFim(); ?>
