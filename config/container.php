<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Selective\BasePath\BasePathMiddleware;
use Psr\Container\ContainerInterface as ContainerInterface;
use Slim\App as App;
use Slim\Factory\AppFactory as AppFactory;
use Slim\Middleware\ErrorMiddleware as ErrorMiddleware;
use Nyholm\Dsn\DsnParser;
use Genis\Domain\Animal\Repositories\AnimalReaderRepository as AnimalReaderRepository;
use Genis\Domain\Animal\Services\DatabaseExporter as DatabaseExporter;

return [
    'settings' => function() {
        return require __DIR__ . '/settings.php';
    },
            
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();        
    },
            
    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];
        
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details']
        );
    },
            
    BasePathMiddleware::class => function (ContainerInterface $container) {
        return new BasePathMiddleware($container->get(App::class));
    },
    
    PDO::class => function (ContainerInterface $container) {
        $dsn = $container->get('settings')['dsn'];
        $parsed_dsn = DsnParser::parse($dsn);
        return new PDO('mysql:host='. $parsed_dsn->getHost() .';dbname='. substr($parsed_dsn->getPath(),1) .';charset=utf8', $parsed_dsn->getUser(), $parsed_dsn->getPassword());
    },
            
    DatabaseExporter::class => function (ContainerInterface $container) {
        $export_paths = [];
        $export_paths['data_export_path'] = $container->get('settings')['data_export_path'];
        $export_paths['db_dump_path'] = $container->get('settings')['db_dump_path'];
        $export_paths['pedig_results_path'] = $container->get('settings')['pedig_results_path'];
        
        $pdo = $container->get(PDO::class);
        $repository = new AnimalReaderRepository($pdo);
        return new DatabaseExporter($export_paths, $repository);
    }
];

