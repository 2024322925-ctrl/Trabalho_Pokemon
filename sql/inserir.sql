USE jogo_pokemon;


INSERT INTO item
(id_item, nome, descricao, imagem_item, preco, tipo, quantidade, disponivel)
VALUES
(1, 'Potion', 'Recupera uma pequena quantidade de HP do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/potion.png', 200.00, 'hp', NULL, 1),
(2, 'Super Potion', 'Recupera uma quantidade maior de HP do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/super-potion.png', 700.00, 'hp', NULL, 1),
(3, 'Hyper Potion', 'Recupera uma grande quantidade de HP do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/hyper-potion.png', 1500.00, 'hp', NULL, 1),
(4, 'Max Potion', 'Recupera todo o HP do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/max-potion.png', 2500.00, 'hp', NULL, 1),
(5, 'Full Restore', 'Recupera todo o HP e remove condições negativas.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/full-restore.png', 3000.00, 'hp', NULL, 1),
(6, 'Antidote', 'Cura o envenenamento do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/antidote.png', 200.00, 'status', NULL, 1),
(7, 'Burn Heal', 'Cura a queimadura do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/burn-heal.png', 200.00, 'status', NULL, 1),
(8, 'Ice Heal', 'Cura o congelamento do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/ice-heal.png', 200.00, 'status', NULL, 1),
(9, 'Awakening', 'Desperta um Pokémon adormecido.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/awakening.png', 200.00, 'status', NULL, 1),
(10, 'Paralyze Heal', 'Cura a paralisia do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/paralyze-heal.png', 200.00, 'status', NULL, 1),
(11, 'Full Heal', 'Remove condições negativas do Pokémon.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/full-heal.png', 400.00, 'status', NULL, 1),
(12, 'Revive', 'Revive um Pokémon derrotado.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/revive.png', 2000.00, 'reviver', NULL, 1),
(13, 'Fire Stone', 'Pedra utilizada para evoluir Pokémon compatíveis com fogo.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/fire-stone.png', 3000.00, 'evolucao', NULL, 1),
(14, 'Water Stone', 'Pedra utilizada para evoluir Pokémon compatíveis com água.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/water-stone.png', 3000.00, 'evolucao', NULL, 1),
(15, 'Thunder Stone', 'Pedra utilizada para evoluir Pokémon compatíveis com eletricidade.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/thunder-stone.png', 3000.00, 'evolucao', NULL, 1),
(16, 'Leaf Stone', 'Pedra utilizada para evoluir Pokémon compatíveis com planta.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/leaf-stone.png', 3000.00, 'evolucao', NULL, 1),
(17, 'Moon Stone', 'Pedra utilizada para evoluir Pokémon compatíveis.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/moon-stone.png', 3000.00, 'evolucao', NULL, 1),
(18, 'King''s Rock', 'Item de evolução do tipo Normal.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/kings-rock.png', 3000.00, 'evolucao', NULL, 1),
(19, 'Ice Stone', 'Item de evolução do tipo Gelo.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/ice-stone.png', 3000.00, 'evolucao', NULL, 1),
(20, 'Punching Glove', 'Item de evolução do tipo Lutador.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/punching-glove.png', 3000.00, 'evolucao', NULL, 1),
(21, 'Poison Barb', 'Item de evolução do tipo Venenoso.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poison-barb.png', 3000.00, 'evolucao', NULL, 1),
(22, 'Protector', 'Item de evolução do tipo Terrestre.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/protector.png', 3000.00, 'evolucao', NULL, 1),
(23, 'Pretty Feather', 'Item de evolução do tipo Voador.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/pretty-wing.png', 3000.00, 'evolucao', NULL, 1),
(24, 'Twisted Spoon', 'Item de evolução do tipo Psíquico.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/twisted-spoon.png', 3000.00, 'evolucao', NULL, 1),
(25, 'Silver Powder', 'Item de evolução do tipo Inseto.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/silver-powder.png', 3000.00, 'evolucao', NULL, 1),
(26, 'Hard Stone', 'Item de evolução do tipo Pedra.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/hard-stone.png', 3000.00, 'evolucao', NULL, 1),
(27, 'Dusk Stone', 'Item de evolução do tipo Fantasma.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/dusk-stone.png', 3000.00, 'evolucao', NULL, 1),
(28, 'Dragon Scale', 'Item de evolução do tipo Dragão.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/dragon-scale.png', 3000.00, 'evolucao', NULL, 1),
(29, 'Metal Coat', 'Item de evolução do tipo Aço.', 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/metal-coat.png', 3000.00, 'evolucao', NULL, 1);


INSERT INTO fase (nome, tipo, recompensa_vitoria, xp_recompensa) VALUES
('Ginásio de Pewter', 'rock', 100, 50),
('Ginásio de Cerulean', 'water', 140, 80),
('Ginásio de Vermilion', 'electric', 196, 120),
('Ginásio de Celadon', 'grass', 274, 180),
('Ginásio de Fuchsia', 'poison', 384, 260),
('Ginásio da Ilha Cinnabar', 'fire', 537, 360),
('Ginásio de Saffron', 'psychic', 751, 500),
('Ginásio de Viridian', 'ground', 1051, 700);


UPDATE tipo
SET imagem = CASE nome
    WHEN 'normal' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/1.png'
    WHEN 'fighting' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/2.png'
    WHEN 'flying' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/3.png'
    WHEN 'poison' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/4.png'
    WHEN 'ground' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/5.png'
    WHEN 'rock' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/6.png'
    WHEN 'bug' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/7.png'
    WHEN 'ghost' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/8.png'
    WHEN 'steel' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/9.png'
    WHEN 'fire' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/10.png'
    WHEN 'water' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/11.png'
    WHEN 'grass' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/12.png'
    WHEN 'electric' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/13.png'
    WHEN 'psychic' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/14.png'
    WHEN 'ice' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/15.png'
    WHEN 'dragon' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/16.png'
    WHEN 'fairy' THEN 'https://raw.githubusercontent.com/PokeAPI/sprites/refs/heads/master/sprites/types/generation-ix/scarlet-violet/18.png'
    ELSE imagem
END
WHERE nome IN (
    'normal', 'fighting', 'flying', 'poison', 'ground',
    'rock', 'bug', 'ghost', 'steel', 'fire', 'water',
    'grass', 'electric', 'psychic', 'ice', 'dragon', 'fairy'
);


INSERT INTO fase_pokemon (id_fase, id_pokemon, nivel, posicao) VALUES
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Pewter'), (SELECT id_pokemon FROM pokemon WHERE nome = 'geodude'), 3, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Pewter'), (SELECT id_pokemon FROM pokemon WHERE nome = 'onix'), 4, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Pewter'), (SELECT id_pokemon FROM pokemon WHERE nome = 'golbat'), 5, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Cerulean'), (SELECT id_pokemon FROM pokemon WHERE nome = 'goldeen'), 7, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Cerulean'), (SELECT id_pokemon FROM pokemon WHERE nome = 'starmie'), 8, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Cerulean'), (SELECT id_pokemon FROM pokemon WHERE nome = 'gyarados'), 9, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Vermilion'), (SELECT id_pokemon FROM pokemon WHERE nome = 'voltorb'), 13, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Vermilion'), (SELECT id_pokemon FROM pokemon WHERE nome = 'electabuzz'), 15, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Vermilion'), (SELECT id_pokemon FROM pokemon WHERE nome = 'raichu'), 17, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Celadon'), (SELECT id_pokemon FROM pokemon WHERE nome = 'weepinbell'), 21, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Celadon'), (SELECT id_pokemon FROM pokemon WHERE nome = 'tangela'), 23, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Celadon'), (SELECT id_pokemon FROM pokemon WHERE nome = 'vileplume'), 25, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Fuchsia'), (SELECT id_pokemon FROM pokemon WHERE nome = 'koffing'), 32, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Fuchsia'), (SELECT id_pokemon FROM pokemon WHERE nome = 'muk'), 34, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Fuchsia'), (SELECT id_pokemon FROM pokemon WHERE nome = 'weezing'), 36, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio da Ilha Cinnabar'), (SELECT id_pokemon FROM pokemon WHERE nome = 'growlithe'), 46, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio da Ilha Cinnabar'), (SELECT id_pokemon FROM pokemon WHERE nome = 'rapidash'), 48, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio da Ilha Cinnabar'), (SELECT id_pokemon FROM pokemon WHERE nome = 'arcanine'), 50, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Saffron'), (SELECT id_pokemon FROM pokemon WHERE nome = 'kadabra'), 63, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Saffron'), (SELECT id_pokemon FROM pokemon WHERE nome = 'mr-mime'), 66, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Saffron'), (SELECT id_pokemon FROM pokemon WHERE nome = 'alakazam'), 69, 3),

((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Viridian'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dugtrio'), 80, 1),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Viridian'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidoqueen'), 85, 2),
((SELECT id_fase FROM fase WHERE nome = 'Ginásio de Viridian'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidoking'), 90, 3);


INSERT INTO evolucao (id_pokemon, id_pokemon_evolucao, id_item) VALUES
((SELECT id_pokemon FROM pokemon WHERE nome = 'bulbasaur'), (SELECT id_pokemon FROM pokemon WHERE nome = 'ivysaur'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'ivysaur'), (SELECT id_pokemon FROM pokemon WHERE nome = 'venusaur'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'oddish'), (SELECT id_pokemon FROM pokemon WHERE nome = 'gloom'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'gloom'), (SELECT id_pokemon FROM pokemon WHERE nome = 'vileplume'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'bellsprout'), (SELECT id_pokemon FROM pokemon WHERE nome = 'weepinbell'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'weepinbell'), (SELECT id_pokemon FROM pokemon WHERE nome = 'victreebel'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'exeggcute'), (SELECT id_pokemon FROM pokemon WHERE nome = 'exeggutor'), (SELECT id_item FROM item WHERE nome = 'Leaf Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'charmander'), (SELECT id_pokemon FROM pokemon WHERE nome = 'charmeleon'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'charmeleon'), (SELECT id_pokemon FROM pokemon WHERE nome = 'charizard'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'vulpix'), (SELECT id_pokemon FROM pokemon WHERE nome = 'ninetales'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'growlithe'), (SELECT id_pokemon FROM pokemon WHERE nome = 'arcanine'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'ponyta'), (SELECT id_pokemon FROM pokemon WHERE nome = 'rapidash'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'eevee'), (SELECT id_pokemon FROM pokemon WHERE nome = 'flareon'), (SELECT id_item FROM item WHERE nome = 'Fire Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'squirtle'), (SELECT id_pokemon FROM pokemon WHERE nome = 'wartortle'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'wartortle'), (SELECT id_pokemon FROM pokemon WHERE nome = 'blastoise'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'psyduck'), (SELECT id_pokemon FROM pokemon WHERE nome = 'golduck'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'poliwag'), (SELECT id_pokemon FROM pokemon WHERE nome = 'poliwhirl'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'poliwhirl'), (SELECT id_pokemon FROM pokemon WHERE nome = 'poliwrath'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'tentacool'), (SELECT id_pokemon FROM pokemon WHERE nome = 'tentacruel'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'slowpoke'), (SELECT id_pokemon FROM pokemon WHERE nome = 'slowbro'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'seel'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dewgong'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'shellder'), (SELECT id_pokemon FROM pokemon WHERE nome = 'cloyster'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'krabby'), (SELECT id_pokemon FROM pokemon WHERE nome = 'kingler'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'horsea'), (SELECT id_pokemon FROM pokemon WHERE nome = 'seadra'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'goldeen'), (SELECT id_pokemon FROM pokemon WHERE nome = 'seaking'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'staryu'), (SELECT id_pokemon FROM pokemon WHERE nome = 'starmie'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'magikarp'), (SELECT id_pokemon FROM pokemon WHERE nome = 'gyarados'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'omanyte'), (SELECT id_pokemon FROM pokemon WHERE nome = 'omastar'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'kabuto'), (SELECT id_pokemon FROM pokemon WHERE nome = 'kabutops'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'eevee'), (SELECT id_pokemon FROM pokemon WHERE nome = 'vaporeon'), (SELECT id_item FROM item WHERE nome = 'Water Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'pikachu'), (SELECT id_pokemon FROM pokemon WHERE nome = 'raichu'), (SELECT id_item FROM item WHERE nome = 'Thunder Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'voltorb'), (SELECT id_pokemon FROM pokemon WHERE nome = 'electrode'), (SELECT id_item FROM item WHERE nome = 'Thunder Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'eevee'), (SELECT id_pokemon FROM pokemon WHERE nome = 'jolteon'), (SELECT id_item FROM item WHERE nome = 'Thunder Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'nidorino'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidoking'), (SELECT id_item FROM item WHERE nome = 'Moon Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'nidorina'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidoqueen'), (SELECT id_item FROM item WHERE nome = 'Moon Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'clefairy'), (SELECT id_pokemon FROM pokemon WHERE nome = 'clefable'), (SELECT id_item FROM item WHERE nome = 'Moon Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'jigglypuff'), (SELECT id_pokemon FROM pokemon WHERE nome = 'wigglytuff'), (SELECT id_item FROM item WHERE nome = 'Moon Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'caterpie'), (SELECT id_pokemon FROM pokemon WHERE nome = 'metapod'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'metapod'), (SELECT id_pokemon FROM pokemon WHERE nome = 'butterfree'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'weedle'), (SELECT id_pokemon FROM pokemon WHERE nome = 'kakuna'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'kakuna'), (SELECT id_pokemon FROM pokemon WHERE nome = 'beedrill'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'paras'), (SELECT id_pokemon FROM pokemon WHERE nome = 'parasect'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'venonat'), (SELECT id_pokemon FROM pokemon WHERE nome = 'venomoth'), (SELECT id_item FROM item WHERE nome = 'Silver Powder')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'pidgey'), (SELECT id_pokemon FROM pokemon WHERE nome = 'pidgeotto'), (SELECT id_item FROM item WHERE nome = 'Pretty Feather')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'pidgeotto'), (SELECT id_pokemon FROM pokemon WHERE nome = 'pidgeot'), (SELECT id_item FROM item WHERE nome = 'Pretty Feather')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'spearow'), (SELECT id_pokemon FROM pokemon WHERE nome = 'fearow'), (SELECT id_item FROM item WHERE nome = 'Pretty Feather')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'doduo'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dodrio'), (SELECT id_item FROM item WHERE nome = 'Pretty Feather')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'ekans'), (SELECT id_pokemon FROM pokemon WHERE nome = 'arbok'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'nidoran-f'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidorina'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'nidoran-m'), (SELECT id_pokemon FROM pokemon WHERE nome = 'nidorino'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'zubat'), (SELECT id_pokemon FROM pokemon WHERE nome = 'golbat'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'grimer'), (SELECT id_pokemon FROM pokemon WHERE nome = 'muk'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'koffing'), (SELECT id_pokemon FROM pokemon WHERE nome = 'weezing'), (SELECT id_item FROM item WHERE nome = 'Poison Barb')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'sandshrew'), (SELECT id_pokemon FROM pokemon WHERE nome = 'sandslash'), (SELECT id_item FROM item WHERE nome = 'Protector')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'diglett'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dugtrio'), (SELECT id_item FROM item WHERE nome = 'Protector')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'cubone'), (SELECT id_pokemon FROM pokemon WHERE nome = 'marowak'), (SELECT id_item FROM item WHERE nome = 'Protector')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'rhyhorn'), (SELECT id_pokemon FROM pokemon WHERE nome = 'rhydon'), (SELECT id_item FROM item WHERE nome = 'Protector')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'mankey'), (SELECT id_pokemon FROM pokemon WHERE nome = 'primeape'), (SELECT id_item FROM item WHERE nome = 'Punching Glove')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'machop'), (SELECT id_pokemon FROM pokemon WHERE nome = 'machoke'), (SELECT id_item FROM item WHERE nome = 'Punching Glove')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'machoke'), (SELECT id_pokemon FROM pokemon WHERE nome = 'machamp'), (SELECT id_item FROM item WHERE nome = 'Punching Glove')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'abra'), (SELECT id_pokemon FROM pokemon WHERE nome = 'kadabra'), (SELECT id_item FROM item WHERE nome = 'Twisted Spoon')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'kadabra'), (SELECT id_pokemon FROM pokemon WHERE nome = 'alakazam'), (SELECT id_item FROM item WHERE nome = 'Twisted Spoon')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'drowzee'), (SELECT id_pokemon FROM pokemon WHERE nome = 'hypno'), (SELECT id_item FROM item WHERE nome = 'Twisted Spoon')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'geodude'), (SELECT id_pokemon FROM pokemon WHERE nome = 'graveler'), (SELECT id_item FROM item WHERE nome = 'Hard Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'graveler'), (SELECT id_pokemon FROM pokemon WHERE nome = 'golem'), (SELECT id_item FROM item WHERE nome = 'Hard Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'gastly'), (SELECT id_pokemon FROM pokemon WHERE nome = 'haunter'), (SELECT id_item FROM item WHERE nome = 'Dusk Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'haunter'), (SELECT id_pokemon FROM pokemon WHERE nome = 'gengar'), (SELECT id_item FROM item WHERE nome = 'Dusk Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'dratini'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dragonair'), (SELECT id_item FROM item WHERE nome = 'Dragon Scale')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'dragonair'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dragonite'), (SELECT id_item FROM item WHERE nome = 'Dragon Scale')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'magnemite'), (SELECT id_pokemon FROM pokemon WHERE nome = 'magneton'), (SELECT id_item FROM item WHERE nome = 'Metal Coat')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'seel'), (SELECT id_pokemon FROM pokemon WHERE nome = 'dewgong'), (SELECT id_item FROM item WHERE nome = 'Ice Stone')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'shellder'), (SELECT id_pokemon FROM pokemon WHERE nome = 'cloyster'), (SELECT id_item FROM item WHERE nome = 'Ice Stone')),

((SELECT id_pokemon FROM pokemon WHERE nome = 'rattata'), (SELECT id_pokemon FROM pokemon WHERE nome = 'raticate'), (SELECT id_item FROM item WHERE nome = 'King''s Rock')),
((SELECT id_pokemon FROM pokemon WHERE nome = 'meowth'), (SELECT id_pokemon FROM pokemon WHERE nome = 'persian'), (SELECT id_item FROM item WHERE nome = 'King''s Rock'));


SELECT COUNT(*) AS total_itens FROM item;
SELECT COUNT(*) AS total_fases FROM fase;
SELECT COUNT(*) AS total_pokemon_fases FROM fase_pokemon;
SELECT COUNT(*) AS total_regras_evolucao FROM evolucao;