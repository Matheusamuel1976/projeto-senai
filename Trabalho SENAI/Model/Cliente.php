<?php
namespace Model;

use PDO;

class Cliente
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("SELECT * FROM clientes ORDER BY nome")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function cadastrar(array $dados): bool
    {
        $stmt = $this->db->prepare("INSERT INTO clientes (nome,cpf_cnpj,telefone,email,endereco) VALUES (:nome,:cpf,:tel,:email,:end)");
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':cpf', $dados['cpf_cnpj']);
        $stmt->bindValue(':tel', $dados['telefone']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':end', $dados['endereco']);
        return $stmt->execute();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare("UPDATE clientes SET nome=:nome,cpf_cnpj=:cpf,telefone=:tel,email=:email,endereco=:end WHERE id=:id");
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':cpf', $dados['cpf_cnpj']);
        $stmt->bindValue(':tel', $dados['telefone']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':end', $dados['endereco']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
