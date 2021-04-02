<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Domain\Animal\Services;
use Genis\Domain\Animal\Repositories\AnimalReaderRepository as AnimalReaderRepository;

/**
 * Description of AnimalInformationRetriever
 *
 * @author Christophe
 */
class AnimalInformationRetriever {
    private $repository;
    
    public function __construct($repository) {
        $this->repository = $repository;
    }
    
    public function get_animal_information(int $animal_id, array $optional_query_params) {
        $result = $this->repository->read_animal_information($animal_id);
        $this->handle_query_parameters($optional_query_params);
        
        return json_encode($result);
    }
    
    private function handle_query_parameters($optional_query_params) {
        // Do something with query
        return null;
    }
}
