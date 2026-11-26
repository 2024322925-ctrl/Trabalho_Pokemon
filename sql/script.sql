create database if not exists jogo_pokemon;


use jogo_pokemon;


create table usuario(
    id_usuario int AUTO_INCREMENT primary key,
    nome varchar(50) not null,
    sobrenome varchar(50) not null,
    email varchar(100) not null unique,
    senha varchar(255) not null,
    perfil varchar(30) not null default 'jogador'
);


create table tipo(
    id_tipo int AUTO_INCREMENT primary key,
    nome varchar(30) not null unique,
    imagem VARCHAR(255) DEFAULT NULL
);


create table pokemon(
    id_pokemon int AUTO_INCREMENT primary key,
    nome varchar(50) not null,
    hp int not null,
    hp_atual int not null,
    velocidade int not null,
    ataque int not null,
    defesa int not null,
    ataque_especial int not null,
    defesa_especial int not null, 
    status_atual varchar(30) default null,
    ativo boolean default true,
    imagem_costas VARCHAR(255) DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL
);


create table item(
    id_item int AUTO_INCREMENT primary key,
    nome varchar(50) not null unique,
    descricao varchar(255),
    imagem_item VARCHAR(500) NULL,
    preco decimal(10,2) not null,
    tipo varchar(30) not null,
    quantidade int default null,
    disponivel boolean default true
);


create table pokemon_tipo(
    id_pokemon int not null,
    id_tipo int not null,


    primary key(id_pokemon,id_tipo),


    foreign key(id_pokemon) references pokemon(id_pokemon),
    foreign key(id_tipo) references tipo(id_tipo)
);


create table time_pokemon(
    id_time int AUTO_INCREMENT primary key,
    id_usuario int not null,
    nome varchar(50) not null,
    ativo boolean default true,


    foreign key(id_usuario) references usuario(id_usuario)
);


create table time_membro(
    id_time int not null,
    id_pokemon int not null,
    posicao int not null,


    primary key(id_time,id_pokemon),


    foreign key(id_time) references time_pokemon(id_time),
    foreign key(id_pokemon) references pokemon(id_pokemon),


    unique(id_time,posicao)
);


create table carteira(
    id_carteira int AUTO_INCREMENT primary key,
    id_usuario int not null unique,
    saldo decimal(10,2) not null default 0,


    foreign key(id_usuario) references usuario(id_usuario)
);


create table inventario(
    id_usuario int not null,
    id_item int not null,
    quantidade int not null default 0,


    primary key(id_usuario,id_item),


    foreign key(id_usuario) references usuario(id_usuario),
	foreign key(id_item) references item(id_item) 
);


create table compra(
    id_compra int AUTO_INCREMENT primary key,
    id_usuario int not null,
    data_compra datetime not null default CURRENT_TIMESTAMP,
    valor_total decimal(10,2) not null,


    foreign key(id_usuario) references usuario(id_usuario)
);


create table compra_item(
    id_compra int not null,
    id_item int not null,
    quantidade int not null,
    preco_unitario decimal(10,2) not null,


    primary key(id_compra,id_item),


    foreign key(id_compra) references compra(id_compra),
    foreign key(id_item) references item(id_item)
);


create table evolucao(
    id_evolucao int AUTO_INCREMENT primary key,
    id_pokemon int not null,
    id_pokemon_evolucao int not null,
    id_item int not null,


    foreign key(id_pokemon) references pokemon(id_pokemon),
    foreign key(id_pokemon_evolucao) references pokemon(id_pokemon),
    foreign key(id_item) references item(id_item)
);

CREATE TABLE pokemon_time_status(
    id_time INT NOT NULL,
    id_pokemon INT NOT NULL,
    hp_atual INT NOT NULL,
    status_atual VARCHAR(30) DEFAULT NULL,
    turnos_status INT NOT NULL DEFAULT 0,

    PRIMARY KEY (id_time, id_pokemon),

    FOREIGN KEY (id_time, id_pokemon)
    REFERENCES time_membro(id_time, id_pokemon)
    ON DELETE CASCADE
);

CREATE TABLE fase (
    id_fase INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(30) NOT NULL
);

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
);

CREATE TABLE log_batalha (
    id_batalha INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_fase INT NOT NULL,
    resultado VARCHAR(20) NOT NULL,
    recompensa DECIMAL(10,2) DEFAULT 0,
    data_batalha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_fase)
        REFERENCES fase(id_fase)
);

CREATE TABLE ataque (
    id_ataque INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    id_tipo INT NOT NULL,
    poder INT NOT NULL DEFAULT 0,
    precisao INT,
    categoria VARCHAR(20) NOT NULL,
    efeito_status VARCHAR(30),
    chance_efeito DECIMAL(5,2) DEFAULT 0,
    dados_efeito JSON NULL,

    FOREIGN KEY (id_tipo) REFERENCES tipo(id_tipo)
);

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
);

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
);

CREATE TABLE progresso_usuario (
    id_usuario INT PRIMARY KEY,
    maior_fase_desbloqueada INT NOT NULL DEFAULT 1,

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario)
);

CREATE TABLE usuario_fase_concluida (
    id_usuario INT NOT NULL,
    id_fase INT NOT NULL,
    data_conclusao DATETIME DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_usuario, id_fase),

    FOREIGN KEY (id_usuario)
        REFERENCES usuario(id_usuario),

    FOREIGN KEY (id_fase)
        REFERENCES fase(id_fase)
);

DELIMITER //

CREATE TRIGGER criar_carteira_usuario
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    INSERT INTO carteira (id_usuario, saldo)
    VALUES (NEW.id_usuario, 0);
END //

DELIMITER ;

CREATE VIEW vw_usuario_carteira AS
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

CREATE VIEW vw_pokemon_tipos AS
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

CREATE VIEW vw_time_pokemon AS
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



DROP TRIGGER IF EXISTS atualizar_inventario_compra;

CREATE TRIGGER atualizar_inventario_compra
AFTER INSERT ON compra_item
FOR EACH ROW

INSERT INTO inventario (id_usuario, id_item, quantidade)
SELECT
    c.id_usuario,
    NEW.id_item,
    NEW.quantidade
FROM compra c
WHERE c.id_compra = NEW.id_compra

ON DUPLICATE KEY UPDATE
    quantidade = quantidade + NEW.quantidade;

ALTER TABLE pokemon_time_status
ADD COLUMN nivel INT NOT NULL DEFAULT 1,
ADD COLUMN xp INT NOT NULL DEFAULT 0;

ALTER TABLE log_batalha
ADD COLUMN xp_recebido INT NOT NULL DEFAULT 0;

ALTER TABLE pokemon_time_status
DROP FOREIGN KEY pokemon_time_status_ibfk_1;


ALTER TABLE fase
ADD COLUMN recompensa_vitoria INT NOT NULL DEFAULT 0,
ADD COLUMN xp_recompensa INT NOT NULL DEFAULT 0;

UPDATE fase
SET recompensa_vitoria = 100, xp_recompensa = 50
WHERE id_fase = 1;

UPDATE fase
SET recompensa_vitoria = 140, xp_recompensa = 80
WHERE id_fase = 2;

UPDATE fase
SET recompensa_vitoria = 196, xp_recompensa = 120
WHERE id_fase = 3;

UPDATE fase
SET recompensa_vitoria = 274, xp_recompensa = 180
WHERE id_fase = 4;

UPDATE fase
SET recompensa_vitoria = 384, xp_recompensa = 260
WHERE id_fase = 5;

UPDATE fase
SET recompensa_vitoria = 537, xp_recompensa = 360
WHERE id_fase = 6;

UPDATE fase
SET recompensa_vitoria = 751, xp_recompensa = 500
WHERE id_fase = 7;

UPDATE fase
SET recompensa_vitoria = 1051, xp_recompensa = 700
WHERE id_fase = 8;


ALTER TABLE log_batalha
ADD COLUMN chave_batalha CHAR(36) NULL,
ADD UNIQUE KEY uq_chave_batalha (chave_batalha);