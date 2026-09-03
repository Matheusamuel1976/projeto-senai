<?php
namespace Model;

use PDO;

class Pedido
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("
            SELECT p.*, c.nome AS cliente_nome
            FROM pedidos p JOIN clientes c ON c.id=p.cliente_id
            ORDER BY p.id DESC
        ")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM pedidos WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function itens(int $pedidoId): array
    {
        $stmt = $this->db->prepare("SELECT pi.*, pr.nome AS produto_nome FROM pedido_itens pi JOIN produtos pr ON pr.id=pi.produto_id WHERE pi.pedido_id=:id");
        $stmt->bindValue(':id', $pedidoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function criar(int $clienteId, string $status, string $obs, array $itens): bool
    {
        $this->db->beginTransaction();
        try {
            $total = 0;
            foreach ($itens as $it) { $total += $it['quantidade'] * $it['preco']; }
            $stmt = $this->db->prepare("INSERT INTO pedidos (cliente_id,status,total,observacao) VALUES (:cid,:status,:total,:obs)");
            $stmt->bindValue(':cid', $clienteId, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':total', $total);
            $stmt->bindValue(':obs', $obs);
            $stmt->execute();
            $pedidoId = (int)$this->db->lastInsertId();
            foreach ($itens as $it) {
                $s = $this->db->prepare("INSERT INTO pedido_itens (pedido_id,produto_id,quantidade,preco_unitario) VALUES (:pid,:prod,:qtd,:preco)");
                $s->bindValue(':pid', $pedidoId, PDO::PARAM_INT);
                $s->bindValue(':prod', $it['produto_id'], PDO::PARAM_INT);
                $s->bindValue(':qtd', $it['quantidade'], PDO::PARAM_INT);
                $s->bindValue(':preco', $it['preco']);
                $s->execute();
            }
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }

    public function atualizarStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE pedidos SET status=:status WHERE id=:id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM pedidos WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function contarPorStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM pedidos WHERE status=:s");
        $stmt->bindValue(':s', $status);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
