<?php

// проверка почты
$app->get('/check_email', \MoneyStat\Controller\CheckEmail::class);