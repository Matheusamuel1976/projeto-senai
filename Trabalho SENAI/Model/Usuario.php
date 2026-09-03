<?php
namespace Model;

use PDO;
use PDOException;

class Usuario
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function cadastrar(string $nome, string $email, string $senhaHash, string $tipo = 'comum'): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha', $senhaHash);
            $stmt->bindValue(':tipo', $tipo);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function porEmail(string $email): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function porId(int $id): array|bool
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function listar(): array
    {
        return $this->db->query("SELECT id,nome,email,tipo,criado_em FROM usuarios ORDER BY id DESC")->fetchAll();
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
