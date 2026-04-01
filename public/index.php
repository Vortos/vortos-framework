<?php

$config = require __DIR__ . '/../bootstrap/app.php';

use Vortos\Foundation\Runner;

$runner = new Runner(...$config, context: 'http');

$response = $runner->run();
$response->send();

$runner->cleanUp();