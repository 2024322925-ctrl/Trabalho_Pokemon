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

INSERT INTO fase (nome, tipo) VALUES
('Ginásio de Pewter', 'rock'),
('Ginásio de Cerulean', 'water'),
('Ginásio de Vermilion', 'electric'),
('Ginásio de Celadon', 'grass'),
('Ginásio de Fuchsia', 'poison'),
('Ginásio da Ilha Cinnabar', 'fire'),
('Ginásio de Saffron', 'psychic'),
('Ginásio de Viridian', 'ground');

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