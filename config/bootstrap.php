<?php
use DI\ContainerBuilder;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// Get container settings
$containerBuilder->addDefinitions(__DIR__ . '/container.php');

// Build container instance
$container = $containerBuilder->build();

// Create the App instance
$app = $container->get(App::class);

// Register routes
(require __DIR__ . '/routes.php')($app);

// Register middleware
(require __DIR__ . '/middleware.php')($app);

return $app;

/*$container['db'] = function ($c) {
    $dsn = $c['settings']['dsn'];
    $parsed_dsn = DsnParser::parse($dsn);
    $host = $parsed_dsn->getHost();
    $user = $parsed_dsn->getUser();
    $pass = ($parsed_dsn->getPassword()=='')? '': ':'. $parsed_dsn->getPassword();
    $db = $parsed_dsn->getParameters();
    $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};*/