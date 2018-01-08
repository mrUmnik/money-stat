<?php

// проверка почты
$app->get('/check_email', \MoneyStat\Controller\CheckEmail::class);
$app->get('/process', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
	$filename = $request->getQueryParams()['file'];
	$parser = new \MoneyStat\Parser\Sberbank($this);
	$parser->parse($filename);
});