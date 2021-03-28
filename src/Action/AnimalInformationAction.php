<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Action;

use Genis\Domain\Animal\Services\AnimalInformationRetriever as AnimalInformationRetriever;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Description of AnimalInformationAction
 *
 * @author Christophe
 */
class AnimalInformationAction {
    private $animal_information_retriever;
    
    public function __construct(AnimalInformationRetriever $animal_information_retriever)
    {
        $this->animal_information_retriever = $animal_information_retriever;
    }
    
    public function __invoke(Request $request, Response $response, array $args) : Response
    {
        $params = $request->getQueryParams();
        $result = $this->animal_information_retriever->get_animal_information($args['animal_id'], $params);
        
        $response->getBody()->write($result);
        
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
    }
}
