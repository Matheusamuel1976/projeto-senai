<?php
namespace Model;

use PDO;

class Produto
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("
            SELECT p.*, c.nome AS categoria_nome, f.nome AS fornecedor_nome
            FROM produtos p
            LEFT JOIN categorias c ON c.id=p.categoria_id
            LEFT JOIN fornecedores f ON f.id=p.fornecedor_id
            ORDER BY p.id DESC
        ")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function estoqueBaixo(): array
    {
        return $this->db->query("SELECT * FROM produtos WHERE quantidade <= estoque_minimo ORDER BY quantidade ASC")->fetchAll();
    }

    public function cadastrar(array $d): bool
    {
        $stmt = $this->db->prepare("INSERT INTO produtos (nome,descricao,categoria_id,fornecedor_id,preco,quantidade,estoque_minimo,status) VALUES (:nome,:desc,:cat,:forn,:preco,:qtd,:min,:status)");
        $stmt->bindValue(':nome', $d['nome']);
        $stmt->bindValue(':desc', $d['descricao']);
        $stmt->bindValue(':cat', $d['categoria_id'] ?: null, $d['categoria_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':forn', $d['fornecedor_id'] ?: null, $d['fornecedor_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':preco', $d['preco']);
        $stmt->bindValue(':qtd', $d['quantidade'], PDO::PARAM_INT);
        $stmt->bindValue(':min', $d['estoque_minimo'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $d['status']);
        return $stmt->execute();
    }

    public function atualizar(int $id, array $d): bool
    {
        $stmt = $this->db->prepare("UPDATE produtos SET nome=:nome,descricao=:desc,categoria_id=:cat,fornecedor_id=:forn,preco=:preco,quantidade=:qtd,estoque_minimo=:min,status=:status WHERE id=:id");
        $stmt->bindValue(':nome', $d['nome']);
        $stmt->bindValue(':desc', $d['descricao']);
        $stmt->bindValue(':cat', $d['categoria_id'] ?: null, $d['categoria_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':forn', $d['fornecedor_id'] ?: null, $d['fornecedor_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':preco', $d['preco']);
        $stmt->bindValue(':qtd', $d['quantidade'], PDO::PARAM_INT);
        $stmt->bindValue(':min', $d['estoque_minimo'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $d['status']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM produtos WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function ajustarEstoque(int $id, int $delta): bool
    {
        $stmt = $this->db->prepare("UPDATE produtos SET quantidade = quantidade + :delta WHERE id=:id");
        $stmt->bindValue(':delta', $delta, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
