<?php
require __DIR__ . '/vendor/autoload.php';

if (is_array($argv)) {
	// вся эта свистопляска, чтобы из под cli можно было запускать в виде
	// php cli.php commandName <params>
	$argv[1] = '/' . $argv[1];
	$argv[3] = $argv[2];
	$argv[2] = 'GET';
}

$settings = require __DIR__ . '/src/settings.php';
$container = new \Slim\Container($settings);
$app = new \Slim\App($container);
require __DIR__ . '/src/dependencies.php';
require __DIR__ . '/src/middleware_cli.php';
require __DIR__ . '/src/routes_cli.php';

$app->run();