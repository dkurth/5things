<?php

$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

$config = parse_ini_file('config.ini');
$basePath = rtrim($config['basePath'], '/');

// Routing rules:

switch (strtolower($request_uri[0])) {
    case $basePath . '/':
        require 'views/activity_random.php';
        break;
    case $basePath . '/activity/list':
        require 'views/activity_list.php';
        break;
    case $basePath . '/activity/edit':
        require 'views/activity_edit.php';
        break;
    // Everything else
    default:
        header('HTTP/1.0 404 Not Found');
        require 'views/404.php';
        break;
}