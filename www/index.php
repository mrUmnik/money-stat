<?php
require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../src/settings.php';
$container = new \Slim\Container($settings);
$app = new \Slim\App($container);
require __DIR__ . '/../src/dependencies.php';
require __DIR__ . '/../src/middleware_web.php';
require __DIR__ . '/../src/routes_web.php';

$app->run();