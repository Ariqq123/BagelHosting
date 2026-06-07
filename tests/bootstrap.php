<?php

$testsEnabled = getenv('PTERODACTYL_ENABLE_TESTS');

if ($testsEnabled !== 'true') {
    fwrite(STDERR, "Tests are disabled for this live panel checkout. Set PTERODACTYL_ENABLE_TESTS=true only when using a dedicated test database.\n");
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
