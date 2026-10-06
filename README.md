# Trabalho_Pokemon
MONSTROBOLSO: atividade avaliativa do 3º tri de Programação Orientada a Objetos I, Linguagens para a Web II e Banco de Dados II

O objetivo do trabalho é criar um site, em PHP (orientado a objetos) e banco de dados, em que se possa jogar uma versão personalizada de Pokémon (ou outro jogo com mesma mecânica).

Este trabalho vai valer 6,5 da nota do trimestre de vocês. 
Último commit é 22/11 (domingo). 
Commit a mais no GitHub zera o trabalho. 
Bancas de correção nos dias 23/11 [DS3T] (13h10 às 16h50) e 27/11 [DS3M] (8h às 11h40). Cada professor vai avaliar e dar a nota para sua respectiva disciplina.

Pode ser feito individualmente, em duplas ou grupos de até 3 pessoas (máximo).

Perfil à la Arnaldo (Bigolin me disse para colocar assim e que vocês iam entender).

Telas exigidas:
tela de montagem de time (Team Builder);
tela de login;
tela de batalha: 
todas as batalhas precisam estar armazenadas numa tabela de log;
tela da lojinha (Poké Mart).

Variáveis mínimas para implementação do Pokémon: 10. Tendo, obrigatoriamente, as seguintes variáveis: nome, HP (vida) e velocidade

Ganhar batalhas geram dinheiro (pokédollar) para o banco, que pode ser usado na lojinha. Nela, é possível comprar os seguintes itens:

Observação: todos os itens abaixo são o mínimo exigido para a avaliação.

Restauradores de Vida (HP): 
Potion: Recupera 20 HP; 
Super Potion: Recupera 50 HP; 
Hyper Potion: Recupera 200 HP;
Max Potion: Recupera 100% do HP;
Full Restore: Recupera 100% do HP + cura todas as condições de status.

Cura de Condição:
Antidote (envenenamento);
Burn Heal (queimadura);
Ice Heal (congelamento);
Awakening (sono);
Paralyze Heal (paralisia);
Full Heal (cura todos os status).
Reviver: Revive (revive um Pokémon desmaiado com metade da vida).

A evolução de Pokémon se dará por compra de item na loja (não é necessário implementar as variações de evolução do Pokémon original).

Possibilidades de ponto extra (limitado a 1 opção):

Centro Pokémon para recuperar a vida após a batalha;
Capricho na interface gráfica.

Pokémon precisa “herdar” (trait) os tipos (Água, Terra etc)

Requisitos técnicos:

Consumo de APIs;
Requisições assíncronas;
Uso do PDO;
MVC;
Transporte de dados via json;
Autoload nas classes;
Mínimo de 3 exceções, 1 delas customizada;
Orientação a Objetos em Javascript e PHP;
Sobrecargas de métodos;
Composição, Agregação, Associação;
Controle de Usuário (troca de perfis);
Encapsulamento;
Polimorfismo;
Projeto estar no GitHub;
4 agrupamentos do SQL (DDL, DML, DTL e DQL). Não é obrigatório usar DROP e TRUNCATE;
Criar tabelas de logs de batalhas, perfis ou usuários e loja;
Obrigatório usar Triggers e pesquisar como funciona.
Usar, pelo menos, três Views.


Para quem gosta de Pokémon: é permitido implementar variações de regras da Smogon (formatos), desde que atenda aos nossos requisitos.

