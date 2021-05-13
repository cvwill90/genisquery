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
        $result = $this->repository->read_animal_information($animal_id, $optional_query_params["include_genetic_information"], $optional_query_params["include_parents_information"]);
        
        return json_encode($result);
    }
}
