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


    /*private function create_database_connection($dsn) : object
    {
        //Tentative de connexion
        try
        {
            $parsed_dsn = DsnParser::parse($config);
            $host = $parsed_dsn->getHost();
            $user = $parsed_dsn->getUser();
            $pass = ($parsed_dsn->getPassword()=='')? '': ':'. $parsed_dsn->getPassword();
            $db = $parsed_dsn->getParameters();
            $con = new PDO('mysql:host='. $host .';dbname='. $db .';charset=utf8', $user, $pw);
            $con -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        // Si erreur: attraper l'erreur et afficher le message d'erreur
        catch (Exception $e)
        {
            die('Erreur : ' . $e->getMessage());
        }
        return $con;
    }
    
    private function export_database_internal() : string
    {
        $table_headers = "id_contact;nom;prenom;cheptel;id_animal;nom_animal;sexe;no_identification;date_naiss;livre_gene;famille;lignee;etat;id_mere;nom_mere;identif_mere;id_pere;nom_pere;identif_pere;code_race;nom_race";
            $table_headers_array = explode(';', $table_headers);
            $filename =  EXPORT_TYPES['intern'] . ".csv";

            $sql_export = "SELECT id_contact, nom, prenom, e.nom_elevage as cheptel, a.id_animal, a.nom_animal, a.sexe, a.no_identification, a.date_naiss, lg.lib_livre as livre_gene, IF(tp.lib_type='sejour', 'vivant', tp.lib_type) as etat, a.id_mere, mere.nom_animal AS nom_mere, mere.no_identification AS identif_mere, a.id_pere, pere.nom_animal AS nom_pere, pere.no_identification AS identif_pere, a.code_race, r.lib_race AS nom_race
                    FROM animal a 
                    INNER JOIN animal mere ON a.id_mere=mere.id_animal 
                    INNER JOIN animal pere ON a.id_pere=pere.id_animal 
                    INNER JOIN race r ON a.code_race=r.code_race 
                    INNER JOIN espece esp ON esp.id_espece=r.id_espece 
                    INNER JOIN periode p ON p.id_animal=a.id_animal 
                    LEFT JOIN elevage e ON p.id_elevage=e.id_elevage 
                    LEFT JOIN contact c ON c.id_elevage=e.id_elevage 
                    LEFT JOIN type_periode tp ON tp.id_type=p.id_type 
                    LEFT JOIN livre_genealogique lg ON lg.id_livre=a.id_livre 
                    WHERE (p.id_type=2 AND p.date_sortie IS NULL) OR p.id_type=1 
                    ORDER BY esp.lib_espece, nom_race, a.nom_animal";
    }*/
}