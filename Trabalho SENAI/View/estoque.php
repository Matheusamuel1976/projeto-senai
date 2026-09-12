<?php
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: ../index.php?msg=login');
    exit;
}

use Model\Produto;

$db = getConexao();
$m = new Produto($db);
$lista = $m->listar();

require_once '_layout.php';
layoutTopo('Estoque');
?>

<h4 class="fw-bold"><i class="bi bi-stack"></i> Estoque</h4>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Minimo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $p): ?>
                    <?php $baixo = $p['quantidade'] <= $p['estoque_minimo']; ?>
                    <tr class="<?= $baixo ? 'table-danger' : '' ?>">
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td>
                            <span class="badge <?= $baixo ? 'bg-danger' : 'bg-success' ?>">
                                <?= $p['quantidade'] ?>
                            </span>
                        </td>
                        <td><?= $p['estoque_minimo'] ?></td>
                        <td>
                            <?= $baixo
                                ? '<span class="badge bg-danger">Estoque baixo</span>'
                                : '<span class="badge bg-success">OK</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<p class="mt-3">
    <a href="movimentacoes.php" class="btn text-white" style="background:#0d3b66">
        Registrar movimentacao
    </a>
</p>

<?php layoutFim(); ?>
