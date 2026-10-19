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
    nome varchar(30) not null unique
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
    ativo boolean default true
);


create table item(
    id_item int AUTO_INCREMENT primary key,
    nome varchar(50) not null unique,
    descricao varchar(255),
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

ALTER TABLE pokemon
ADD COLUMN imagem VARCHAR(255) DEFAULT NULL;

ALTER TABLE tipo
ADD COLUMN imagem VARCHAR(255) DEFAULT NULL;

UPDATE tipo
SET imagem = CASE nome
    WHEN 'normal'   THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/1.png'
    WHEN 'fighting' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/2.png'
    WHEN 'flying'   THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/3.png'
    WHEN 'poison'   THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/4.png'
    WHEN 'ground'   THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/5.png'
    WHEN 'rock'     THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/6.png'
    WHEN 'bug'      THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/7.png'
    WHEN 'ghost'    THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/8.png'
    WHEN 'steel'    THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/9.png'
    WHEN 'fire'     THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/10.png'
    WHEN 'water'    THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/11.png'
    WHEN 'grass'    THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/12.png'
    WHEN 'electric' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/13.png'
    WHEN 'psychic'  THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/14.png'
    WHEN 'ice'      THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/15.png'
    WHEN 'dragon'   THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/16.png'
    WHEN 'fairy'    THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/18.png'
    ELSE imagem
END
WHERE nome IN (
    'normal', 'fighting', 'flying', 'poison', 'ground', 'rock',
    'bug', 'ghost', 'steel', 'fire', 'water', 'grass',
    'electric', 'psychic', 'ice', 'dragon', 'fairy'
);