<?php
namespace Model;

use PDO;

class Fornecedor
{
    private PDO $db;
    public function __construct(PDO $db) { $this->db = $db; }

    public function listar(): array
    {
        return $this->db->query("SELECT * FROM fornecedores ORDER BY nome")->fetchAll();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM fornecedores WHERE id=:id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function cadastrar(array $dados): bool
    {
        $stmt = $this->db->prepare("INSERT INTO fornecedores (nome,cnpj_cpf,telefone,email,endereco) VALUES (:nome,:cnpj,:tel,:email,:end)");
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':cnpj', $dados['cnpj_cpf']);
        $stmt->bindValue(':tel', $dados['telefone']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':end', $dados['endereco']);
        return $stmt->execute();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->db->prepare("UPDATE fornecedores SET nome=:nome,cnpj_cpf=:cnpj,telefone=:tel,email=:email,endereco=:end WHERE id=:id");
        $stmt->bindValue(':nome', $dados['nome']);
        $stmt->bindValue(':cnpj', $dados['cnpj_cpf']);
        $stmt->bindValue(':tel', $dados['telefone']);
        $stmt->bindValue(':email', $dados['email']);
        $stmt->bindValue(':end', $dados['endereco']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM fornecedores WHERE id=:id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
