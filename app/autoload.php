<?php

spl_autoload_register(static function ($classe) {
    $pastas = [
        "Database" => "../config/database.php",
        "Evolucao" => "models/Evolucao.php",
        "EvolucaoException" => "exceptions/EvolucaoException.php",
        "EvolucaoController" => "controllers/EvolucaoController.php",
        "EvolucaoDAO" => "dao/EvolucaoDAO.php"
    ];

    if (isset($pastas[$classe])) {
        require_once __DIR__ . "/" . $pastas[$classe];
    }
});
