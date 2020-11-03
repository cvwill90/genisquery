<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Database
 *
 * @author Christophe
 */
namespace Genis\Domain\Animal\Services;
use Genis\Domain\Animal\Repositories\AnimalReaderRepository as AnimalReaderRepository;

class DatabaseExporter {
    private $export_paths;
    private $repository;
    const EXPORT_TYPE_INTERNAL = 'export_interne';
    const EXPORT_TYPE_INTRANET = 'export_intratnet';

    public function __construct($export_paths, AnimalReaderRepository $repository) {
        $this->export_paths = $export_paths;
        $this->repository = $repository;
    }
    
    public function export_database_internal(): string
    {        
        // Get animals from repository
        $result = $this->repository->read_all_animals_for_export();
        
        // Define file name
        $date_time = new \DateTime();
        $filename = self::EXPORT_TYPE_INTERNAL . $date_time->format('Ymd_His') . ".csv";
        
        // Open file for writing
        $data_export_path = $this->export_paths['data_export_path'];
        $this->ensure_directory_existence($data_export_path);
        $fp = fopen($data_export_path ."/". $filename, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($result as $line) {
            fputcsv($fp, $line, ';', '"');
        }
        fclose($fp);
        return json_encode(['success' => true]);
    }
    
    public function export_database_external() : string
    {
        return 'Hello, World 1!';
    }
    
    public function ensure_directory_existence($dir_path) {
        if (!is_dir($dir_path)) mkdir($dir_path, 0777, true);
    }
}