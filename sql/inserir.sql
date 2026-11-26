INSERT INTO item
(id_item, nome, descricao, imagem_item, preco, tipo, quantidade, disponivel)
VALUES

(1, 'Potion',
 'Recupera uma pequena quantidade de HP do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/potion.png',
 200.00, 'hp', NULL, 1),

(2, 'Super Potion',
 'Recupera uma quantidade maior de HP do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/super-potion.png',
 700.00, 'hp', NULL, 1),

(3, 'Hyper Potion',
 'Recupera uma grande quantidade de HP do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/hyper-potion.png',
 1500.00, 'hp', NULL, 1),

(4, 'Max Potion',
 'Recupera todo o HP do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/max-potion.png',
 2500.00, 'hp', NULL, 1),

(5, 'Full Restore',
 'Recupera todo o HP e remove condições negativas.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/full-restore.png',
 3000.00, 'hp', NULL, 1),

(6, 'Antidote',
 'Cura o envenenamento do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/antidote.png',
 200.00, 'status', NULL, 1),

(7, 'Burn Heal',
 'Cura a queimadura do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/burn-heal.png',
 200.00, 'status', NULL, 1),

(8, 'Ice Heal',
 'Cura o congelamento do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/ice-heal.png',
 200.00, 'status', NULL, 1),

(9, 'Awakening',
 'Desperta um Pokémon adormecido.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/awakening.png',
 200.00, 'status', NULL, 1),

(10, 'Paralyze Heal',
 'Cura a paralisia do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/paralyze-heal.png',
 200.00, 'status', NULL, 1),

(11, 'Full Heal',
 'Remove condições negativas do Pokémon.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/full-heal.png',
 400.00, 'status', NULL, 1),

(12, 'Revive',
 'Revive um Pokémon derrotado.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/revive.png',
 2000.00, 'reviver', NULL, 1),

(13, 'Fire Stone',
 'Pedra utilizada para evoluir Pokémon compatíveis com fogo.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/fire-stone.png',
 3000.00, 'evolucao', NULL, 1),

(14, 'Water Stone',
 'Pedra utilizada para evoluir Pokémon compatíveis com água.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/water-stone.png',
 3000.00, 'evolucao', NULL, 1),

(15, 'Thunder Stone',
 'Pedra utilizada para evoluir Pokémon compatíveis com eletricidade.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/thunder-stone.png',
 3000.00, 'evolucao', NULL, 1),

(16, 'Leaf Stone',
 'Pedra utilizada para evoluir Pokémon compatíveis com planta.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/leaf-stone.png',
 3000.00, 'evolucao', NULL, 1),

(17, 'Moon Stone',
 'Pedra utilizada para evoluir Pokémon compatíveis.',
 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/moon-stone.png',
 3000.00, 'evolucao', NULL, 1);

INSERT INTO fase (nome, tipo, recompensa_vitoria, xp_recompensa) VALUES
('Ginásio de Pewter', 'rock', 100, 50),
('Ginásio de Cerulean', 'water', 140, 80),
('Ginásio de Vermilion', 'electric', 196, 120),
('Ginásio de Celadon', 'grass', 274, 180),
('Ginásio de Fuchsia', 'poison', 384, 260),
('Ginásio da Ilha Cinnabar', 'fire', 537, 360),
('Ginásio de Saffron', 'psychic', 751, 500),
('Ginásio de Viridian', 'ground', 1051, 700);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(1, 74, 3, 1),
(1, 95, 4, 2),
(1, 42, 5, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(2, 118, 7, 1),
(2, 121, 8, 2),
(2, 130, 9, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(3, 100, 13, 1),
(3, 125, 15, 2),
(3, 26, 17, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(4, 70, 21, 1),
(4, 114, 23, 2),
(4, 45, 25, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(5, 109, 32, 1),
(5, 89, 34, 2),
(5, 110, 36, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(6, 58, 46, 1),
(6, 78, 48, 2),
(6, 59, 50, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(7, 64, 63, 1),
(7, 122, 66, 2),
(7, 65, 69, 3);

INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
(8, 51, 80, 1),
(8, 31, 85, 2),
(8, 34, 90, 3);

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
INSERT INTO item
    (nome, descricao, imagem_item, preco, tipo, quantidade, disponivel)
SELECT
    novos.nome,
    CONCAT('Item de evolução do tipo ', novos.tipo_pokemon, '.'),
    CONCAT(
        'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/',
        novos.sprite,
        '.png'
    ),
    3000.00,
    'evolucao',
    NULL,
    1
FROM (
    SELECT 'King''s Rock' AS nome, 'Normal' AS tipo_pokemon, 'kings-rock' AS sprite
    UNION ALL SELECT 'Ice Stone', 'Gelo', 'ice-stone'
    UNION ALL SELECT 'Punching Glove', 'Lutador', 'punching-glove'
    UNION ALL SELECT 'Poison Barb', 'Venenoso', 'poison-barb'
    UNION ALL SELECT 'Protector', 'Terrestre', 'protector'
    UNION ALL SELECT 'Pretty Feather', 'Voador', 'pretty-wing'
    UNION ALL SELECT 'Twisted Spoon', 'Psíquico', 'twisted-spoon'
    UNION ALL SELECT 'Silver Powder', 'Inseto', 'silver-powder'
    UNION ALL SELECT 'Hard Stone', 'Pedra', 'hard-stone'
    UNION ALL SELECT 'Dusk Stone', 'Fantasma', 'dusk-stone'
    UNION ALL SELECT 'Dragon Scale', 'Dragão', 'dragon-scale'
    UNION ALL SELECT 'Metal Coat', 'Aço', 'metal-coat'
) AS novos
ON DUPLICATE KEY UPDATE
    descricao = VALUES(descricao),
    imagem_item = VALUES(imagem_item),
    preco = VALUES(preco),
    tipo = VALUES(tipo),
    disponivel = VALUES(disponivel);
-- Execute depois de importar os Pokémon e cadastrar os itens da loja.
INSERT INTO evolucao (id_pokemon, id_pokemon_evolucao, id_item)
SELECT origem.id_pokemon, destino.id_pokemon, i.id_item
FROM (
    SELECT 'bulbasaur' AS origem, 'ivysaur' AS destino, 'Leaf Stone' AS item
    UNION ALL SELECT 'ivysaur', 'venusaur', 'Leaf Stone'
    UNION ALL SELECT 'oddish', 'gloom', 'Leaf Stone'
    UNION ALL SELECT 'gloom', 'vileplume', 'Leaf Stone'
    UNION ALL SELECT 'bellsprout', 'weepinbell', 'Leaf Stone'
    UNION ALL SELECT 'weepinbell', 'victreebel', 'Leaf Stone'
    UNION ALL SELECT 'exeggcute', 'exeggutor', 'Leaf Stone'
    UNION ALL SELECT 'charmander', 'charmeleon', 'Fire Stone'
    UNION ALL SELECT 'charmeleon', 'charizard', 'Fire Stone'
    UNION ALL SELECT 'vulpix', 'ninetales', 'Fire Stone'
    UNION ALL SELECT 'growlithe', 'arcanine', 'Fire Stone'
    UNION ALL SELECT 'ponyta', 'rapidash', 'Fire Stone'
    UNION ALL SELECT 'eevee', 'flareon', 'Fire Stone'
    UNION ALL SELECT 'squirtle', 'wartortle', 'Water Stone'
    UNION ALL SELECT 'wartortle', 'blastoise', 'Water Stone'
    UNION ALL SELECT 'psyduck', 'golduck', 'Water Stone'
    UNION ALL SELECT 'poliwag', 'poliwhirl', 'Water Stone'
    UNION ALL SELECT 'poliwhirl', 'poliwrath', 'Water Stone'
    UNION ALL SELECT 'tentacool', 'tentacruel', 'Water Stone'
    UNION ALL SELECT 'slowpoke', 'slowbro', 'Water Stone'
    UNION ALL SELECT 'seel', 'dewgong', 'Water Stone'
    UNION ALL SELECT 'shellder', 'cloyster', 'Water Stone'
    UNION ALL SELECT 'krabby', 'kingler', 'Water Stone'
    UNION ALL SELECT 'horsea', 'seadra', 'Water Stone'
    UNION ALL SELECT 'goldeen', 'seaking', 'Water Stone'
    UNION ALL SELECT 'staryu', 'starmie', 'Water Stone'
    UNION ALL SELECT 'magikarp', 'gyarados', 'Water Stone'
    UNION ALL SELECT 'omanyte', 'omastar', 'Water Stone'
    UNION ALL SELECT 'kabuto', 'kabutops', 'Water Stone'
    UNION ALL SELECT 'eevee', 'vaporeon', 'Water Stone'
    UNION ALL SELECT 'pikachu', 'raichu', 'Thunder Stone'
    UNION ALL SELECT 'voltorb', 'electrode', 'Thunder Stone'
    UNION ALL SELECT 'eevee', 'jolteon', 'Thunder Stone'
    UNION ALL SELECT 'nidorino', 'nidoking', 'Moon Stone'
    UNION ALL SELECT 'nidorina', 'nidoqueen', 'Moon Stone'
    UNION ALL SELECT 'clefairy', 'clefable', 'Moon Stone'
    UNION ALL SELECT 'jigglypuff', 'wigglytuff', 'Moon Stone'
    UNION ALL SELECT 'caterpie', 'metapod', 'Silver Powder'
    UNION ALL SELECT 'metapod', 'butterfree', 'Silver Powder'
    UNION ALL SELECT 'weedle', 'kakuna', 'Silver Powder'
    UNION ALL SELECT 'kakuna', 'beedrill', 'Silver Powder'
    UNION ALL SELECT 'paras', 'parasect', 'Silver Powder'
    UNION ALL SELECT 'venonat', 'venomoth', 'Silver Powder'
    UNION ALL SELECT 'pidgey', 'pidgeotto', 'Pretty Feather'
    UNION ALL SELECT 'pidgeotto', 'pidgeot', 'Pretty Feather'
    UNION ALL SELECT 'spearow', 'fearow', 'Pretty Feather'
    UNION ALL SELECT 'doduo', 'dodrio', 'Pretty Feather'
    UNION ALL SELECT 'ekans', 'arbok', 'Poison Barb'
    UNION ALL SELECT 'nidoran-f', 'nidorina', 'Poison Barb'
    UNION ALL SELECT 'nidoran-m', 'nidorino', 'Poison Barb'
    UNION ALL SELECT 'zubat', 'golbat', 'Poison Barb'
    UNION ALL SELECT 'grimer', 'muk', 'Poison Barb'
    UNION ALL SELECT 'koffing', 'weezing', 'Poison Barb'
    UNION ALL SELECT 'sandshrew', 'sandslash', 'Protector'
    UNION ALL SELECT 'diglett', 'dugtrio', 'Protector'
    UNION ALL SELECT 'cubone', 'marowak', 'Protector'
    UNION ALL SELECT 'rhyhorn', 'rhydon', 'Protector'
    UNION ALL SELECT 'mankey', 'primeape', 'Punching Glove'
    UNION ALL SELECT 'machop', 'machoke', 'Punching Glove'
    UNION ALL SELECT 'machoke', 'machamp', 'Punching Glove'
    UNION ALL SELECT 'abra', 'kadabra', 'Twisted Spoon'
    UNION ALL SELECT 'kadabra', 'alakazam', 'Twisted Spoon'
    UNION ALL SELECT 'drowzee', 'hypno', 'Twisted Spoon'
    UNION ALL SELECT 'geodude', 'graveler', 'Hard Stone'
    UNION ALL SELECT 'graveler', 'golem', 'Hard Stone'
    UNION ALL SELECT 'gastly', 'haunter', 'Dusk Stone'
    UNION ALL SELECT 'haunter', 'gengar', 'Dusk Stone'
    UNION ALL SELECT 'dratini', 'dragonair', 'Dragon Scale'
    UNION ALL SELECT 'dragonair', 'dragonite', 'Dragon Scale'
    UNION ALL SELECT 'magnemite', 'magneton', 'Metal Coat'
    UNION ALL SELECT 'seel', 'dewgong', 'Ice Stone'
    UNION ALL SELECT 'shellder', 'cloyster', 'Ice Stone'
    UNION ALL SELECT 'rattata', 'raticate', 'King''s Rock'
    UNION ALL SELECT 'meowth', 'persian', 'King''s Rock'
) AS regra
JOIN pokemon origem ON origem.nome = regra.origem
JOIN pokemon destino ON destino.nome = regra.destino
JOIN item i ON i.nome = regra.item AND i.tipo = 'evolucao'
WHERE NOT EXISTS (
    SELECT 1 FROM evolucao e
    WHERE e.id_pokemon = origem.id_pokemon AND e.id_item = i.id_item
);

SELECT ROW_COUNT() AS novas_regras;
CREATE TABLE IF NOT EXISTS pokemon_desbloqueado (
    id_usuario INT NOT NULL,
    id_pokemon INT NOT NULL,
    PRIMARY KEY (id_usuario, id_pokemon),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_pokemon) REFERENCES pokemon(id_pokemon)
);

CREATE TABLE IF NOT EXISTS escolha_inicial_pokemon (
    id_usuario INT PRIMARY KEY,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- Preserva a equipe ativa de quem ja jogava.
INSERT IGNORE INTO pokemon_desbloqueado (id_usuario, id_pokemon)
SELECT t.id_usuario, m.id_pokemon
FROM time_pokemon t JOIN time_membro m ON m.id_time = t.id_time
WHERE t.ativo = 1;

INSERT IGNORE INTO escolha_inicial_pokemon (id_usuario)
SELECT t.id_usuario
FROM time_pokemon t JOIN time_membro m ON m.id_time = t.id_time
WHERE t.ativo = 1
GROUP BY t.id_usuario, t.id_time
HAVING COUNT(*) >= 3;
UPDATE fase SET recompensa_vitoria = 100, xp_recompensa = 50 WHERE id_fase = 1;
UPDATE fase SET recompensa_vitoria = 140, xp_recompensa = 80 WHERE id_fase = 2;
UPDATE fase SET recompensa_vitoria = 196, xp_recompensa = 120 WHERE id_fase = 3;
UPDATE fase SET recompensa_vitoria = 274, xp_recompensa = 180 WHERE id_fase = 4;
UPDATE fase SET recompensa_vitoria = 384, xp_recompensa = 260 WHERE id_fase = 5;
UPDATE fase SET recompensa_vitoria = 537, xp_recompensa = 360 WHERE id_fase = 6;
UPDATE fase SET recompensa_vitoria = 751, xp_recompensa = 500 WHERE id_fase = 7;
UPDATE fase SET recompensa_vitoria = 1051, xp_recompensa = 700 WHERE id_fase = 8;