
<?php

require_once __DIR__ . "/../dao/TimeMembroDAO.php";

class TimeMembroController {

    private $timeMembroDAO;

    public function __construct() {
        $this->timeMembroDAO = new TimeMembroDAO();
    }

    public function adicionarPokemon(TimeMembro $membro) {
        return $this->timeMembroDAO->adicionar($membro);
    }

    public function listarPokemonsDoTime($id_time) {
        return $this->timeMembroDAO->listarPorTime($id_time);
    }

    public function removerPokemon($id_time, $id_pokemon) {
        return $this->timeMembroDAO->remover($id_time, $id_pokemon);
    }
}

?>
