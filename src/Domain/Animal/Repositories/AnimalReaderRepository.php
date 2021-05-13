<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Domain\Animal\Repositories;
use Genis\Domain\Animal\Data\AnimalInformation as AnimalInformation;
use Genis\Domain\Animal\Data\AnimalExtendedInformation as AnimalExtendedInformation;
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
    
    public function read_animal_information($animal_id, $include_genetic_information, $include_parents_information): object {
        $sql = "SELECT animal.*, pere.nom_animal as nom_pere, mere.nom_animal as nom_mere, "
                . "pere.no_identification as no_identification_pere, mere.no_identification as no_identification_mere "
                . "FROM animal "
                . "LEFT JOIN ancetre ON ancetre.id_ancetre = animal.id_animal "
                . "LEFT JOIN animal pere ON pere.id_animal=animal.id_pere "
                . "LEFT JOIN animal mere ON mere.id_animal=animal.id_mere "
                . "LEFT JOIN livre_genealogique livre ON livre.id_livre=ancetre.id_livre "
                . "WHERE animal.id_animal=$animal_id";
        $animal_information_result_set = $this->connection->query($sql);
        
        if ($include_genetic_information) {
            $animal_information = $animal_information_result_set->fetchObject("Genis\Domain\Animal\Data\AnimalExtendedInformation");
            $animal_information = $this->extend_with_genetic_information($animal_information);
        } else {
            $animal_information = $animal_information_result_set->fetchObject("Genis\Domain\Animal\Data\AnimalInformation");
        }
        
        if ($include_parents_information) {
            $animal_information = $this->extend_with_parents_information($animal_information);
        }
        
        return $animal_information;
    }
    
    private function extend_with_genetic_information(AnimalExtendedInformation $animal_without_genetic_information): object {
        $animal_information_extended_with_genetic_information = $animal_without_genetic_information;
        
        $sql = "SELECT ancetre.*, lib_livre "
                . "FROM ancetre "
                . "LEFT JOIN livre_genealogique livre ON livre.id_livre=ancetre.id_livre "
                . "WHERE ancetre.id_ancetre=$animal_information_extended_with_genetic_information->id_animal";
        $animal_genetic_information_result_set = $this->connection->query($sql);
        if ($animal_genetic_information_result_set->rowCount() > 0) {
            // If animal is an ancestor
            // then extend immediately the animal information object with genetic information
            $animal_genetic_information_result_set->setFetchMode(PDO::FETCH_INTO, $animal_information_extended_with_genetic_information);
            $animal_genetic_information_result_set->fetch();
        } else {
            // If animal is not an ancestor
            // Then calculate his genetic information (blood percentage) on the fly
            $pourcentage_sang_race = $this->calculate_pourcentage_sang_animal(
                    [
                        intval($animal_information_extended_with_genetic_information->id_pere),
                        intval($animal_information_extended_with_genetic_information->id_mere)
                    ]
            );

            if ($pourcentage_sang_race["is_available"]) {
                $animal_information_extended_with_genetic_information->pourcentage_sang_race = $pourcentage_sang_race["value"];
            } else {
                $animal_information_extended_with_genetic_information->pourcentage_sang_race = null;
            }
        }

        // Get the animal's lignee
        $animal_information_extended_with_genetic_information->lignee = $this->getLignee(intval($animal_information_extended_with_genetic_information->id_pere));
        
        // Get the animal's famille
        $animal_information_extended_with_genetic_information->famille = $this->getFamille(intval($animal_information_extended_with_genetic_information->id_mere));
        
        return $animal_information_extended_with_genetic_information;
    }


    private function calculate_pourcentage_sang_animal(array $parent_ids): array {
        $pourcentage_sang_race = [
            "is_available" => 0,
            "value" => null
        ];
        
        if (in_array(1, $parent_ids) || in_array(2, $parent_ids)) {
            return $pourcentage_sang_race;
        } else {
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
    }
    
    /**
     * This function establishes the list of all ancestors of an animal by looking at the ascendance of this animal's parents
     * This function may: 
     * * Either a full list of oldest known ancestors from which the blood percentage of an animal can be calculated
     * * Or an empty list, in case one oldest ancestor is unknown
     * @param array $parent_ids
     * @return array
     */
    private function get_list_of_oldest_known_ancestors(array $parent_ids): array {
        $oldest_known_ancestors = [];
        $ancestors_list_to_be_scanned = new \ArrayIterator();
        
        foreach ($parent_ids as $p_id) {
            $ancestors_list_to_be_scanned[] = [
                "ascendance_degree" => 1,
                "animal_id" => $p_id
                /*"animal_id" => $p_id,
                "already_scanned" => false*/
            ];
        }
        
        // Start scanning all animals
        //$ancestors_list_to_be_scanned_copy = &$ancestors_list_to_be_scanned;
        foreach ($ancestors_list_to_be_scanned as $anc) {
            $ancestor_information = $this->get_ancestor_information($anc['animal_id']);
            
            $ancestor_parents_ids = $this->get_ancestor_parents($anc['animal_id']);
            
            if ($ancestor_information->type_ancetre != null) {
                // If currently scanned animal is an ancestor
                // then this animal is added to the oldest_known_ancestors_list
                $oldest_known_ancestors[] = [
                    "ascendance_degree" => $anc['ascendance_degree'],
                    "ancestor" => (array) $ancestor_information
                ];
            } elseif ($this->parents_are_unknown($ancestor_parents_ids)) {
                // If currently scanned animal is not an ancestor AND has at least one unknown parent
                // then this means that there is at least one unknown ancestor in the oldest_known_ancestors_list
                // --> As a consequence, the blood percentage cannot be calculated and an empty oldest_known_ancestors_list is returned
                $oldest_known_ancestors = [];
                break;
            } else {
                // If the currently scanned animal is not an ancestor AND has 2 known parents
                // then this animal's 2 parents are added to the list of animals to be scanned with 1 ascendance degree higher the the currently scanned animal 
                
                $ancestors_list_to_be_scanned[] = [
                        "ascendance_degree" => $anc['ascendance_degree']+1,
                        "animal_id" => $ancestor_parents_ids[0]
                    ];
                $ancestors_list_to_be_scanned[] = [
                        "ascendance_degree" => $anc['ascendance_degree']+1,
                        "animal_id" => $ancestor_parents_ids[1]
                    ];
            }
        }        
        
        return $oldest_known_ancestors;
    }

    private function get_ancestor_information(int $ancestor_id): object {
        $sql = "SELECT id_animal, type_ancetre, pourcentage_sang_race "
                . "FROM animal "
                . "LEFT JOIN ancetre ON ancetre.id_ancetre = animal.id_animal "
                . "WHERE id_animal=$ancestor_id";
        $result_set = $this->connection->query($sql);
        $ancestor_information = $result_set->fetchObject();
        return $ancestor_information;
    }
    
    private function get_ancestor_parents(int $ancestor_id): array {
        $sql = "SELECT id_pere, id_mere "
                . "FROM animal "
                . "WHERE id_animal=$ancestor_id";
        $result_set = $this->connection->query($sql);
        $test = $result_set->rowCount();
        $ancestor_parents = $result_set->fetchObject();
        $ancestor_parent_ids = [$ancestor_parents->id_pere, $ancestor_parents->id_mere];
        return $ancestor_parent_ids;
    }
        
    private function parents_are_unknown(array $parent_ids): bool {
        if (in_array(1, $parent_ids) || in_array(2, $parent_ids)) {
            return true;
        } else {
            return false;
        }
    }
    
    private function getLignee(int $id_animal): string {
        $sql = 'CALL getLignee(:id_animal, @MaleAncestorName)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id_animal', $id_animal, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        $result = $this->connection->query("SELECT @MaleAncestorName AS lignee")->fetch(PDO::FETCH_ASSOC);
        return $result['lignee'];
    }
    
    private function getFamille(int $id_animal): string {
        $sql = 'CALL getFamille(:id_animal, @FemaleAncestorName)';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id_animal', $id_animal, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        $result = $this->connection->query("SELECT @FemaleAncestorName AS famille")->fetch(PDO::FETCH_ASSOC);
        return $result['famille'];
    }
    
    private function extend_with_parents_information($animal_information) {
        if ($animal_information->id_pere != 1) {
            $father_information = $this->read_animal_information($animal_information->id_pere, 1, 0);
            $animal_information->father_information = $father_information;
        }
        
        if ($animal_information->id_mere != 2) {
            $mother_information = $this->read_animal_information($animal_information->id_mere, 1, 0);
            $animal_information->mother_information = $mother_information;
        }
        
        return $animal_information;
    }
}
