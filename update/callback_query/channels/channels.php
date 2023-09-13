<?php
/** @var array $callback_data */

if ($callback_data['action'] == 'channels') {
    require realpath(__DIR__) . '/add.php';
    require realpath(__DIR__) . '/delete.php';
}