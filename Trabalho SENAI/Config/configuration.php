<?php
define("DB_NAME", getenv("DB_NAME") ?: "logistica");
define("DB_USER", getenv("DB_USER") ?: "root");
define("DB_PASSWORD", getenv("DB_PASSWORD") ?: "");
define("DB_HOST", getenv("DB_HOST") ?: "127.0.0.1");
define("DB_PORT", getenv("DB_PORT") ?: "3306");

function getConexao(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
