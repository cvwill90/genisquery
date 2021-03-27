<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Domain\Animal\Repositories;
use Genis\Domain\Animal\Data\AnimalInformation as AnimalInformation;
use PDO;

/**
 * Description of AnimalReaderRepository
 *
 * @author Christophe
 */
class AnimalReaderRepository {
    private $connection;
    
    public function __construct(PDO $pdo) {
        $this->connection = $pdo;
    }
    
    public function read_animal_information($animal_id) {
        $sql = "SELECT animal.*, ancetre.*, lib_livre, pere.nom_animal as nom_pere, mere.nom_animal as nom_mere, "
                . "pere.no_identification as no_identification_pere, mere.no_identification as no_identification_mere "
                . "FROM animal "
                . "LEFT JOIN ancetre ON ancetre.id_ancetre = animal.id_animal "
                . "LEFT JOIN animal pere ON pere.id_animal=animal.id_pere "
                . "LEFT JOIN animal mere ON mere.id_animal=animal.id_mere "
                . "LEFT JOIN livre_genealogique livre ON livre.id_livre=ancetre.id_livre "
                . "WHERE animal.id_animal=$animal_id";
        $animal_information_result_set = $this->connection->query($sql);
        $animal_information = $animal_information_result_set->fetchObject("Genis\Domain\Animal\Data\AnimalInformation");
        if ($animal_information->type_ancetre == null) {
            $pourcentage_sang_race = $this->calculate_pourcentage_sang_animal([$animal_information->id_pere, $animal_information->id_mere]);
            if ($pourcentage_sang_race["is_available"]) {
                $animal_information->pourcentage_sang_race = $pourcentage_sang_race["value"];
            } else {
                $animal_information->pourcentage_sang_race = null;
            }
        }
        return $animal_information;
    }
    
    public function calculate_pourcentage_sang_animal(array $parent_ids): array {
        $pourcentage_sang_race = [
            "is_available" => 0,
            "value" => null
        ];
        
        $oldest_known_ancestors_list = $this->get_list_of_oldest_known_ancestors($parent_ids);
        
        if (count($oldest_known_ancestors_list) >= 2) {
            $pourcentage = 0;
            foreach ($oldest_known_ancestors_list as $ancestor) {
                $a = $ancestor["ancestor"]["pourcentage_sang_race"];
                $b = pow(2, $ancestor["ascendance_degree"]);
                $pourcentage += $a / $b;
            }
            $pourcentage_sang_race["value"] = round($pourcentage,3);
            $pourcentage_sang_race["is_available"] = 1;
        }
        
        return $pourcentage_sang_race;
    }
    
    public function get_list_of_oldest_known_ancestors(array $parent_ids): array {
        $oldest_known_ancestors = [];
        $ancestors_list = [];
        
        foreach ($parent_ids as $p_id) {
            $ancestors_list[] = [
                "ascendance_degree" => 1,
                "animal_id" => $p_id,
            ];
        }
        
        foreach ($ancestors_list as &$anc) {
            $ancestor_information = $this->get_ancestor_information($anc['animal_id']);
            
            $ancestor_parents_ids = $this->get_ancestor_parents($anc['animal_id']);
            
            if ($ancestor_information->type_ancetre != null) {
                $oldest_known_ancestor_object = [
                    "ascendance_degree" => $anc['ascendance_degree'],
                    "ancestor" => (array) $ancestor_information
                ];
                $oldest_known_ancestors[] = $oldest_known_ancestor_object;
            } elseif ($this->parents_are_unknown($ancestor_parents_ids)) {
                $oldest_known_ancestors = [];
                break;
            } else {
                $ancestor_object = [
                    "ascendance_degree" => 1,
                    "animal_id" => $anc['ascendance_degree']+1,
                ];
                $ancestors_list = array_merge($ancestors_list, $ancestor_object);
            }
        }        
        
        return $oldest_known_ancestors;
    }

    public function get_ancestor_information(int $ancestor_id): object {
        $sql = "SELECT id_animal, type_ancetre, pourcentage_sang_race "
                . "FROM animal "
                . "LEFT JOIN ancetre ON ancetre.id_ancetre = animal.id_animal "
                . "WHERE id_animal=$ancestor_id";
        $result_set = $this->connection->query($sql);
        $ancestor_information = $result_set->fetchObject();
        return $ancestor_information;
    }
    
    public function get_ancestor_parents(int $ancestor_id): array {
        $sql = "SELECT id_pere, id_mere "
                . "FROM animal "
                . "WHERE id_animal=$ancestor_id";
        $result_set = $this->connection->query($sql);
        $ancestor_parents = $result_set->fetchObject();
        $ancestor_parent_ids = [$ancestor_parents->id_pere, $ancestor_parents->id_mere];
        return $ancestor_parent_ids;
    }
        
    public function parents_are_unknown(array $parent_ids): bool {
        if (in_array(1, $parent_ids) || in_array(2, $parent_ids)) {
            return true;
        } else {
            return false;
        }
    }
}
