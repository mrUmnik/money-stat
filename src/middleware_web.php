<?php
if (is_array($settings['settings']['basic_auth'])) { // если есть настройки basic auth
	$app->add(new \Slim\Middleware\HttpBasicAuthentication([
		"users" => $settings['settings']['basic_auth'],
		"secure" => false
	]));
}