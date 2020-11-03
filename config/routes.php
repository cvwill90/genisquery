<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Slim\App;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Genis\Action as Genis;

return function (App $app){
    
    $app->post('/export/{export_type}', Genis\DatabaseExportAction::class);
    $app->get('/', function (Request $request, Response $response)
    {
        $response->getBody()->write('hello world');
        return $response;
    });
    
    /*$app->get('/export_internal', function (Request $request, Response $response, array $args)
    {    
        echo 'tets';
        $db = new Database($config['dsn']);
        return $response;
    });*/
};