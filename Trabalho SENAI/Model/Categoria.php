<?php
namespace Model;

use PDO;

class Categoria
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function cadastrar(string $nome, string $descricao): bool
    {
        $stmt = $this->db->prepare("INSERT INTO categorias (nome, descricao) VALUES (:nome, :descricao)");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':descricao', $descricao);
        return $stmt->execute();
    }

    public function atualizar(int $id, string $nome, string $descricao): bool
    {
        $stmt = $this->db->prepare("UPDATE categorias SET nome=:nome, descricao=:descricao WHERE id=:id");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
