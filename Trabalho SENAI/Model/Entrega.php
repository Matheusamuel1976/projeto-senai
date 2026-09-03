<?php
namespace Model;

use PDO;

class Entrega
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("
            SELECT e.*, p.status AS pedido_status, c.nome AS cliente_nome
            FROM entregas e
            JOIN pedidos p ON p.id=e.pedido_id
            JOIN clientes c ON c.id=p.cliente_id
            ORDER BY e.id DESC
        ")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM entregas WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function cadastrar(int $pedidoId, string $endereco, ?string $dataPrevista, string $status, string $obs): bool
    {
        $stmt = $this->db->prepare("INSERT INTO entregas (pedido_id,endereco,data_prevista,status,observacao) VALUES (:pid,:end,:data,:status,:obs)");
        $stmt->bindValue(':pid', $pedidoId, PDO::PARAM_INT);
        $stmt->bindValue(':end', $endereco);
        $stmt->bindValue(':data', $dataPrevista ?: null, $dataPrevista ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':obs', $obs);
        return $stmt->execute();
    }

    public function atualizar(int $id, string $status, string $obs): bool
    {
        $stmt = $this->db->prepare("UPDATE entregas SET status=:status, observacao=:obs WHERE id=:id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':obs', $obs);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM entregas WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
