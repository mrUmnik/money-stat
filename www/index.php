<?php
include dirname(__DIR__) . '/src/Bootstrap.php';

$app = \MoneyStat\Application::getInstance();
$app->run(new \MoneyStat\Tasks\ProcessHttpRequest());