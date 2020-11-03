<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnimalReader
 *
 * @author Christophe
 */
namespace Genis\Domain\Animal\Repositories;
use Genis\Domain\Animal\Data\AnimalInternalExport;
use PDO;

class AnimalReaderRepository {
    private $connection;
    
    public function __construct(PDO $pdo) {
        $this->connection = $pdo;
    }
    
    public function read_all_animals_for_export() : array {
        $sql_export = "
            SELECT * 
            FROM (
                SELECT nom as nom_contact_cheptel_actuel, prenom as prenom_contact_cheptel_actuel, 
                    e.nom_elevage as cheptel_actuel, a.id_animal, a.nom_animal as nom, 
                    a.sexe, a.no_identification, a.date_naiss as date_naissance, 
                    lg.lib_livre as livre_genealogique, 
                    IF(tp.lib_type='sejour', 'vivant', tp.lib_type) as etat, 
                    a.id_mere as famille, mere.nom_animal AS nom_mere, 
                    mere.no_identification AS no_identification_mere, a.id_pere as lignee, 
                    pere.nom_animal AS nom_pere, 
                    pere.no_identification AS no_identification_pere,
                    r.lib_race AS nom_race
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
                ORDER BY esp.lib_espece, nom_race, a.nom_animal
            ) AS q1
            LEFT JOIN (
                SELECT p.id_animal as id_animal_2, e.nom_elevage as nom_elevage_naisseur, 
                    e.no_elevage as no_elevage_naisseur 
                FROM periode p
                LEFT JOIN elevage e ON e.id_elevage=p.id_elevage
                WHERE p.id_type=3
            ) AS q2 ON q1.id_animal=q2.id_animal_2";
        
        $animals_list_result_set = $this->connection->query($sql_export);
        
        $animals_list_array = $this->getExportArray($animals_list_result_set);
        
        return $animals_list_array;
    }
    
    private function getExportArray(\PDOStatement $result_set): array
    {
        $result_set->setFetchMode(PDO::FETCH_CLASS, "Genis\Domain\Animal\Data\AnimalInternalExport");
        $animal_objects_array = $result_set->fetchAll();
        
        $animal_export = new AnimalInternalExport();
        $animal_export_cunstructor_args = array_keys(get_object_vars($animal_export));
        $animals_export_array = [];
        $animals_export_array[] = $animal_export_cunstructor_args;
        foreach ($animal_objects_array as &$animal_object_value) {
            $animal_object_value->famille = $this->getFamille($animal_object_value->famille);
            $animal_object_value->lignee = $this->getLignee($animal_object_value->lignee);
            
            $animal_array = [];
            foreach ($animal_export_cunstructor_args as $property) {
                $animal_array[] = $animal_object_value->$property;
            }
            $animals_export_array[] = $animal_array;
        }
        return $animals_export_array;
    }
    
    private function getLignee($id_animal): string 
    {
        $sql = 'CALL getLignee(:id_animal, @MaleAncestorName)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id_animal', $id_animal, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        $result = $this->connection->query("SELECT @MaleAncestorName AS lignee")->fetch(PDO::FETCH_ASSOC);
        return $result['lignee'];
    }
    
    private function getFamille($id_animal): string 
    {
        $sql = 'CALL getFamille(:id_animal, @FemaleAncestorName)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id_animal', $id_animal, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        $result = $this->connection->query("SELECT @FemaleAncestorName AS famille")->fetch(PDO::FETCH_ASSOC);
        return $result['famille'];
    }
}
