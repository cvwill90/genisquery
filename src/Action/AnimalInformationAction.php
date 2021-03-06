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
    private const AUTHORIZED_PARAMETERS_FILTERS = [
        "include_genetic_information" => "verify_boolean_parameter",
        "XDEBUG_SESSION_START" => "verify_param_xdebug_session_start",
        "include_parents_information" => "verify_boolean_parameter"
    ];
    private $filtered_query_parameters = [
        "include_genetic_information" => false,
        "include_parents_information" => false
    ];
    
    public function __construct(AnimalInformationRetriever $animal_information_retriever)
    {
        $this->animal_information_retriever = $animal_information_retriever;
    }
    
    public function __invoke(Request $request, Response $response, array $args) : Response
    {
        $params = $request->getQueryParams();
        
        $query_params_verification_result = $this->verify_query_params_validity($params);
        
        if ($query_params_verification_result["params_valid"]) {
            $result = $this->animal_information_retriever->get_animal_information($args['animal_id'], $this->filtered_query_parameters);
            
            $response->getBody()->write($result);
        
            return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201);
        } else {
            $response->getBody()->write($query_params_verification_result["message"]);
        
            return $response
                    ->withHeader('Content-Type', 'text/plain')
                    ->withStatus(405);
        }
    }
    
    private function verify_query_params_validity(array $query_params): array {
        $params_verification_result = [
            "params_valid" => true,
            "message" => "Query parameters are valid"
        ];
        
        foreach ($query_params as $param_key => $param_val) {
            $verification_result = $this->verify_param_validity($param_key, $param_val);
            if (!$verification_result["is_valid"]) {
                $params_verification_result["params_valid"] = false;
                $params_verification_result["message"] = $verification_result["message"];
                break;
            }
        }
        return $params_verification_result;
    }
    
    private function verify_param_validity(string $param_key, string $param_value): array {
        $verification_result = [
            "is_valid" => true,
            "message" => ""
        ];
        
        if (array_key_exists($param_key, self::AUTHORIZED_PARAMETERS_FILTERS)){
            $param_verification_result = $this->{ self::AUTHORIZED_PARAMETERS_FILTERS[$param_key]}($param_key, $param_value);
            if ($param_verification_result == false) {
                $verification_result["is_valid"] = false;
                $verification_result["message"] = "Given value '$param_value' for parameter '$param_key' is not accepted.";
            }
        } else {
            $verification_result["is_valid"] = false;
            $verification_result["message"] = "Given parameter '$param_key' is not allowed.";
        }
        
        return $verification_result;
    }
    
    private function verify_boolean_parameter(string $param_key, string $param_value): bool {
        $result = filter_var($param_value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_bool($result)) {
            $this->filtered_query_parameters[$param_key] = $result;
            return true;
        } else {
            return false;
        }
    }
    
    private function verify_param_xdebug_session_start(string $param_key, string $param_value): bool {
        return ($param_value == "netbeans-xdebug") ? true : false;
    }
}
