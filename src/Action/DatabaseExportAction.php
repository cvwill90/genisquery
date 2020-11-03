<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Action;

use Genis\Domain\Animal\Services\DatabaseExporter as DatabaseExporter;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


/**
 * Description of DatabaseExports
 *
 * @author Christophe
 */
final class DatabaseExportAction {
    private $db_exporter;
    
    public function __construct(DatabaseExporter $db_exporter) {
        $this->db_exporter = $db_exporter;
    }
    
    public function __invoke(Request $request, Response $response, array $array) : Response
    {
        switch ($array['export_type'])
        {
            case 'export_internal':
                $result = $this->db_exporter->export_database_internal();
                $response->getBody()->write($result);
                break;
            case 'export_external':
                $result = $this->db_exporter->export_database_external();
                $response->getBody()->write($result);
                break;
            default:
                $response->getBody()->write('Method not allowed');
        }
        
        return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);;
    }
}
