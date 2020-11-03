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
