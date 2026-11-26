CREATE DATABASE IF NOT EXISTS jogo_pokemon
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE jogo_pokemon;


CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    sobrenome VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(30) NOT NULL DEFAULT 'jogador'
) ENGINE=InnoDB;


CREATE TABLE tipo (
    id_tipo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(30) NOT NULL UNIQUE,
    imagem VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;


CREATE TABLE pokemon (
    id_pokemon INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    hp INT NOT NULL,
    hp_atual INT NOT NULL,
    velocidade INT NOT NULL,
    ataque INT NOT NULL,
    defesa INT NOT NULL,
    ataque_especial INT NOT NULL,
    defesa_especial INT NOT NULL,
    status_atual VARCHAR(30) DEFAULT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    imagem_costas VARCHAR(255) DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;


CREATE TABLE item (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    imagem_item VARCHAR(500) DEFAULT NULL,
    preco DECIMAL(10,2) NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    quantidade INT DEFAULT NULL,
    disponivel BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB;


CREATE TABLE pokemon_tipo (
    id_pokemon INT NOT NULL,
    id_tipo INT NOT NULL,

    PRIMARY KEY (id_pokemon, id_tipo),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon),

    FOREIGN KEY (id_tipo)
        REFERENCES tipo(id_tipo)
) ENGINE=InnoDB;


CREATE TABLE time_pokemon (
    id_time INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;


CREATE TABLE time_membro (
    id_time INT NOT NULL,
    id_pokemon INT NOT NULL,
    posicao INT NOT NULL,

    PRIMARY KEY (id_time, id_pokemon),
    UNIQUE (id_time, posicao),

    FOREIGN KEY (id_time)
        REFERENCES time_pokemon(id_time),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon)
) ENGINE=InnoDB;


CREATE TABLE carteira (
    id_carteira INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    saldo DECIMAL(10,2) NOT NULL DEFAULT 0,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;


CREATE TABLE inventario (
    id_usuario INT NOT NULL,
    id_item INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,

    PRIMARY KEY (id_usuario, id_item),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_item)
        REFERENCES item(id_item)
) ENGINE=InnoDB;


CREATE TABLE compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    data_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;


CREATE TABLE compra_item (
    id_compra INT NOT NULL,
    id_item INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (id_compra, id_item),

    FOREIGN KEY (id_compra)
        REFERENCES compra(id_compra),

    FOREIGN KEY (id_item)
        REFERENCES item(id_item)
) ENGINE=InnoDB;


CREATE TABLE evolucao (
    id_evolucao INT AUTO_INCREMENT PRIMARY KEY,
    id_pokemon INT NOT NULL,
    id_pokemon_evolucao INT NOT NULL,
    id_item INT NOT NULL,

    UNIQUE (id_pokemon, id_item),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon),

    FOREIGN KEY (id_pokemon_evolucao)
        REFERENCES pokemon(id_pokemon),

    FOREIGN KEY (id_item)
        REFERENCES item(id_item)
) ENGINE=InnoDB;


CREATE TABLE pokemon_time_status (
    id_time INT NOT NULL,
    id_pokemon INT NOT NULL,
    hp_atual INT NOT NULL,
    status_atual VARCHAR(30) DEFAULT NULL,
    turnos_status INT NOT NULL DEFAULT 0,
    nivel INT NOT NULL DEFAULT 1,
    xp INT NOT NULL DEFAULT 0,

    PRIMARY KEY (id_time, id_pokemon),

    FOREIGN KEY (id_time, id_pokemon)
        REFERENCES time_membro(id_time, id_pokemon)
        ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE fase (
    id_fase INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    tipo VARCHAR(30) NOT NULL,
    recompensa_vitoria INT NOT NULL DEFAULT 0,
    xp_recompensa INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;


CREATE TABLE fase_pokemon (
    id_fase INT NOT NULL,
    id_pokemon INT NOT NULL,
    nivel INT NOT NULL,
    posicao INT NOT NULL,

    PRIMARY KEY (id_fase, posicao),
    UNIQUE (id_fase, id_pokemon),

    FOREIGN KEY (id_fase)
        REFERENCES fase(id_fase),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon)
) ENGINE=InnoDB;


CREATE TABLE log_batalha (
    id_batalha INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_fase INT NOT NULL,
    resultado VARCHAR(20) NOT NULL,
    recompensa DECIMAL(10,2) DEFAULT 0,
    xp_recebido INT NOT NULL DEFAULT 0,
    chave_batalha CHAR(36) DEFAULT NULL,
    data_batalha DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_chave_batalha (chave_batalha),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_fase)
        REFERENCES fase(id_fase)
) ENGINE=InnoDB;


CREATE TABLE log_batalha_pokemon (
    id_batalha INT NOT NULL,
    id_pokemon INT NOT NULL,
    ordem INT NOT NULL,
    xp_recebido INT NOT NULL DEFAULT 0,

    PRIMARY KEY (id_batalha, ordem),
    UNIQUE (id_batalha, id_pokemon),

    FOREIGN KEY (id_batalha)
        REFERENCES log_batalha(id_batalha),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon)
) ENGINE=InnoDB;


CREATE TABLE ataque (
    id_ataque INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    id_tipo INT NOT NULL,
    poder INT NOT NULL DEFAULT 0,
    precisao INT,
    categoria VARCHAR(20) NOT NULL,
    efeito_status VARCHAR(30),
    chance_efeito DECIMAL(5,2) DEFAULT 0,
    dados_efeito JSON DEFAULT NULL,

    FOREIGN KEY (id_tipo)
        REFERENCES tipo(id_tipo)
) ENGINE=InnoDB;


CREATE TABLE pokemon_ataque (
    id_pokemon INT NOT NULL,
    id_ataque INT NOT NULL,
    posicao INT NOT NULL,

    PRIMARY KEY (id_pokemon, posicao),
    UNIQUE (id_pokemon, id_ataque),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon)
        ON DELETE CASCADE,

    FOREIGN KEY (id_ataque)
        REFERENCES ataque(id_ataque)
) ENGINE=InnoDB;


CREATE TABLE progresso_usuario (
    id_usuario INT PRIMARY KEY,
    maior_fase_desbloqueada INT NOT NULL DEFAULT 1,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;


CREATE TABLE usuario_fase_concluida (
    id_usuario INT NOT NULL,
    id_fase INT NOT NULL,
    data_conclusao DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_usuario, id_fase),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_fase)
        REFERENCES fase(id_fase)
) ENGINE=InnoDB;


CREATE TABLE pokemon_desbloqueado (
    id_usuario INT NOT NULL,
    id_pokemon INT NOT NULL,

    PRIMARY KEY (id_usuario, id_pokemon),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_pokemon)
        REFERENCES pokemon(id_pokemon)
) ENGINE=InnoDB;


CREATE TABLE escolha_inicial_pokemon (
    id_usuario INT PRIMARY KEY,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;


DROP TRIGGER IF EXISTS criar_carteira_usuario;
DROP TRIGGER IF EXISTS atualizar_inventario_compra;


DELIMITER //

CREATE TRIGGER criar_carteira_usuario
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO carteira (id_usuario, saldo)
    VALUES (NEW.id_usuario, 0);
END //


CREATE TRIGGER atualizar_inventario_compra
AFTER INSERT ON compra_item
FOR EACH ROW
BEGIN

    INSERT INTO inventario (
        id_usuario,
        id_item,
        quantidade
    )

    SELECT
        c.id_usuario,
        NEW.id_item,
        NEW.quantidade

    FROM compra c
    WHERE c.id_compra = NEW.id_compra

    ON DUPLICATE KEY UPDATE
        quantidade = quantidade + NEW.quantidade;

END //

DELIMITER ;


CREATE OR REPLACE VIEW vw_usuario_carteira AS
SELECT
    u.id_usuario,
    u.nome,
    u.sobrenome,
    u.email,
    u.perfil,
    c.saldo
FROM usuario u
LEFT JOIN carteira c
    ON u.id_usuario = c.id_usuario;


CREATE OR REPLACE VIEW vw_pokemon_tipos AS
SELECT
    p.id_pokemon,
    p.nome,
    p.hp,
    p.velocidade,
    p.ataque,
    p.defesa,
    p.ataque_especial,
    p.defesa_especial,
    p.imagem,
    p.imagem_costas,
    t.nome AS tipo
FROM pokemon p
INNER JOIN pokemon_tipo pt
    ON p.id_pokemon = pt.id_pokemon
INNER JOIN tipo t
    ON pt.id_tipo = t.id_tipo;


CREATE OR REPLACE VIEW vw_time_pokemon AS
SELECT
    u.id_usuario,
    u.nome AS nome_usuario,
    tp.id_time,
    tp.nome AS nome_time,
    tm.posicao,
    p.id_pokemon,
    p.nome AS nome_pokemon,
    p.hp,
    p.velocidade,
    p.ataque,
    p.defesa,
    p.ataque_especial,
    p.defesa_especial,
    COALESCE(pts.hp_atual, p.hp_atual) AS hp_atual,
    COALESCE(pts.status_atual, p.status_atual) AS status_atual
FROM time_pokemon tp
INNER JOIN usuario u
    ON tp.id_usuario = u.id_usuario
INNER JOIN time_membro tm
    ON tp.id_time = tm.id_time
INNER JOIN pokemon p
    ON tm.id_pokemon = p.id_pokemon
LEFT JOIN pokemon_time_status pts
    ON tm.id_time = pts.id_time
    AND tm.id_pokemon = pts.id_pokemon
WHERE tp.ativo = 1;