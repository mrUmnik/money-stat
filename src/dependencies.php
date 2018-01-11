<?php

$container = $app->getContainer();

$container['renderer'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};
$container['db'] = function ($container) {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($container['settings']['db']);

	$capsule->setAsGlobal();
	$capsule->bootEloquent();
	//$capsule->getConnection()->enableQueryLog();

	return $capsule;
};
$app->getContainer()->get('db'); // чтобв инициализировать базу