<?php

spl_autoload_register(static function (string $classe): void {
    if ($classe === "Database") {
        require_once __DIR__ . "/../config/database.php";
        return;
    }

    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $classe)) {
        return;
    }

    $pastas = ["models", "controllers", "dao", "services"];

    foreach ($pastas as $pasta) {
        $arquivo = __DIR__ . "/" . $pasta . "/" . $classe . ".php";
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }

    if (strncmp($classe, "Tipo", 4) === 0) {
        require_once __DIR__ . "/models/TipoPokemon.php";
    }
});