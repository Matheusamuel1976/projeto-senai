# LogiControl - Sistema de Gerenciamento Logistico

Sistema web para controle logistico: produtos, categorias, fornecedores, clientes, estoque, movimentacoes, pedidos e entregas. Projeto academico em PHP 8.3 com POO e PDO.

## Descricao

O LogiControl centraliza a operacao logistica em um painel simples: cadastro de produtos com categoria e fornecedor, controle de estoque com entradas/saidas, pedidos vinculados a clientes e entregas com status. Possui autenticacao com niveis admin/comum e dashboard com indicadores.

## Tecnologias

- PHP 8.3+
- MySQL 8
- HTML5 / CSS3 / Bootstrap 5
- JavaScript (minimo)
- PDO + Prepared Statements
- POO
- Composer (autoload PSR-4)

## Funcionalidades

- Autenticacao: login, logout, cadastro, sessoes, `password_hash`/`password_verify`, controle admin/comum
- Dashboard: totais, estoque baixo, pedidos pendentes, entregas em transporte
- Produtos: CRUD com categoria, fornecedor, preco, quantidade, estoque minimo
- Categorias: CRUD
- Fornecedores: CRUD
- Clientes: CRUD
- Estoque: visao de quantidades e alertas
- Movimentacoes: entrada/saida com transacao e validacao de saldo
- Pedidos: criacao com itens, listagem, alteracao de status, exclusao
- Entregas: cadastro por pedido, alteracao de status, exclusao

## Estrutura

```
Config/         - configuracao do banco e autenticacao
Controller/     - UsuarioController
Model/          - Connection, Usuario, Categoria, Fornecedor, Cliente, Produto, Movimentacao, Pedido, Entrega
View/           - telas (dashboard, produtos, categorias, fornecedores, clientes, estoque, movimentacoes, pedidos, entregas)
templates/css/  - estilos
database/       - schema.sql
public/         - (uso via raiz: index.php)
```

## Banco de dados

Importe o arquivo `database/schema.sql` no MySQL:

```bash
mysql -u root -p < database/schema.sql
```

Ou via phpMyAdmin / MySQL Workbench: execute o conteudo do arquivo.

## Instalacao

```bash
composer install
# configure DB em Config/configuration.php ou via env DB_NAME, DB_USER, DB_PASSWORD, DB_HOST, DB_PORT
mysql -u root -p < database/schema.sql
php -S localhost:8000
# abrir http://localhost:8000
```

## Login de demonstracao

| Tipo | E-mail | Senha |
|------|--------|-------|
| Administrador | admin@logistica.com | admin123 |
| Usuario comum | usuario@logistica.com | usuario123 |

## Schema do Banco de Dados

```sql
CREATE DATABASE IF NOT EXISTS logistica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE logistica;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin','comum') NOT NULL DEFAULT 'comum',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cnpj_cpf VARCHAR(20) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf_cnpj VARCHAR(20) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(150) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    categoria_id INT DEFAULT NULL,
    fornecedor_id INT DEFAULT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantidade INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_produto_categoria (categoria_id),
    INDEX idx_produto_fornecedor (fornecedor_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL,
    quantidade INT NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    usuario_id INT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_mov_produto (produto_id),
    INDEX idx_mov_usuario (usuario_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    status ENUM('pendente','em_preparacao','em_transporte','entregue','cancelado') NOT NULL DEFAULT 'pendente',
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observacao VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_pedido_cliente (cliente_id),
    INDEX idx_pedido_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_item_pedido (pedido_id),
    INDEX idx_item_produto (produto_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    data_prevista DATE DEFAULT NULL,
    status ENUM('pendente','em_transporte','entregue','cancelado') NOT NULL DEFAULT 'pendente',
    observacao VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_entrega_pedido (pedido_id),
    INDEX idx_entrega_status (status)
) ENGINE=InnoDB;

INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@logistica.com', '$2y$10$vWP7tWqULwFMF8om4.kK/eUa.IIYXdwU4U7lBEad73KnLYjktXhJy', 'admin'),
('Usuario Comum', 'usuario@logistica.com', '$2y$10$zs9N4JrAqB4kR4rJ5qNcaO.IiotxxSNX7QurSv178d/DOIL.HYSSu', 'comum');

INSERT INTO categorias (nome, descricao) VALUES
('Eletronicos', 'Equipamentos eletronicos e informatica'),
('Alimentos', 'Produtos alimenticios'),
('Vestuario', 'Roupas e acessorios'),
('Limpeza', 'Produtos de limpeza');

INSERT INTO fornecedores (nome, cnpj_cpf, telefone, email, endereco) VALUES
('Distribuidora Alpha', '12.345.678/0001-99', '(11) 99999-0001', 'contato@alpha.com', 'Rua das Industrias, 100 - SP'),
('Fornecedor Beta', '98.765.432/0001-11', '(11) 98888-0002', 'vendas@beta.com', 'Av. Central, 500 - SP');

INSERT INTO clientes (nome, cpf_cnpj, telefone, email, endereco) VALUES
('Joao Silva', '123.456.789-00', '(11) 97777-0001', 'joao@email.com', 'Rua A, 123 - SP'),
('Empresa XYZ', '11.222.333/0001-44', '(11) 96666-0002', 'contato@xyz.com', 'Av. Paulista, 1000 - SP');

INSERT INTO produtos (nome, descricao, categoria_id, fornecedor_id, preco, quantidade, estoque_minimo, status) VALUES
('Notebook Gamer', 'Notebook 16GB RAM', 1, 1, 4500.00, 12, 5, 'ativo'),
('Arroz 5kg', 'Arroz tipo 1', 2, 2, 28.90, 3, 10, 'ativo'),
('Camiseta Polo', 'Camiseta algodao P/M/G', 3, 1, 49.90, 25, 5, 'ativo'),
('Detergente 500ml', 'Detergente neutro', 4, 2, 3.50, 40, 10, 'ativo');

INSERT INTO pedidos (cliente_id, status, total, observacao) VALUES
(1, 'pendente', 4528.90, 'Pedido inicial de demonstracao'),
(2, 'em_transporte', 99.80, 'Entrega em andamento');

INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 4500.00),
(1, 2, 1, 28.90),
(2, 3, 2, 49.90);

INSERT INTO entregas (pedido_id, endereco, data_prevista, status, observacao) VALUES
(1, 'Rua A, 123 - SP', CURDATE(), 'pendente', 'Aguardando separacao'),
(2, 'Av. Paulista, 1000 - SP', DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'em_transporte', 'Saiu para entrega');

INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao, usuario_id) VALUES
(1, 'entrada', 12, 'Estoque inicial', 1),
(2, 'entrada', 20, 'Estoque inicial', 1),
(2, 'saida', 17, 'Venda pedido 1', 1);
```
