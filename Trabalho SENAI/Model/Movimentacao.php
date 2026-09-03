<?php
namespace Model;

use PDO;

class Movimentacao
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("
            SELECT m.*, p.nome AS produto_nome, u.nome AS usuario_nome
            FROM movimentacoes m
            JOIN produtos p ON p.id=m.produto_id
            LEFT JOIN usuarios u ON u.id=m.usuario_id
            ORDER BY m.id DESC
        ")->fetchAll();
    }

    public function registrar(int $produtoId, string $tipo, int $qtd, string $obs, ?int $usuarioId): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO movimentacoes (produto_id,tipo,quantidade,observacao,usuario_id) VALUES (:pid,:tipo,:qtd,:obs,:uid)");
            $stmt->bindValue(':pid', $produtoId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo', $tipo);
            $stmt->bindValue(':qtd', $qtd, PDO::PARAM_INT);
            $stmt->bindValue(':obs', $obs);
            $stmt->bindValue(':uid', $usuarioId, $usuarioId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->execute();

            $delta = $tipo === 'entrada' ? $qtd : -$qtd;
            if ($tipo === 'saida') {
                $check = $this->db->prepare("SELECT quantidade FROM produtos WHERE id=:id");
                $check->bindValue(':id', $produtoId, PDO::PARAM_INT);
                $check->execute();
                $atual = (int)($check->fetchColumn());
                if ($atual < $qtd) {
                    $this->db->rollBack();
                    return false;
                }
            }
            $upd = $this->db->prepare("UPDATE produtos SET quantidade = quantidade + :delta WHERE id=:id");
            $upd->bindValue(':delta', $delta, PDO::PARAM_INT);
            $upd->bindValue(':id', $produtoId, PDO::PARAM_INT);
            $upd->execute();

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
}
