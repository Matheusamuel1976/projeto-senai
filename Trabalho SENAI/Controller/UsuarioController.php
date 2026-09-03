<?php
namespace Controller;

use Model\Usuario;
use PDO;

class UsuarioController
{
    private Usuario $model;

    public function __construct(PDO $db = null)
    {
        $db = $db ?? getConexao();
        $this->model = new Usuario($db);
    }

    public function login(string $email, string $senha): bool
    {
        $usuario = $this->model->porEmail($email);
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return false;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        return true;
    }

    public function cadastrar(string $nome, string $email, string $senha, string $tipo = 'comum'): bool
    {
        if ($this->model->porEmail($email)) return false;
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        return $this->model->cadastrar($nome, $email, $hash, $tipo);
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
    }
}
