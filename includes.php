<?php

require realpath(__DIR__) . '/config.php';

$loader = require realpath(__DIR__) . '/vendor/autoload.php';
$loader->add('', __DIR__ . '/class');

require realpath(__DIR__) . '/vendor/alikm6/php-pomo/l10n.php';

require realpath(__DIR__) . '/functions/functions.php';