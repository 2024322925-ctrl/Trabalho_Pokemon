<?php

if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require_once __DIR__ . "/app/autoload.php";

try {
    $novas = (new EvolucaoController())->importarRegras();
    echo "$novas regras novas cadastradas." . PHP_EOL;
} catch (Throwable $erro) {
    fwrite(STDERR, $erro->getMessage() . PHP_EOL);
    exit(1);
}
