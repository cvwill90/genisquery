<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Domain\Animal\Data;

/**
 * Description of AnimalInformation
 *
 * @author Christophe
 */
class AnimalInformation {
    public $id_animal;
    public $nom_animal;
    public $sexe;
    public $no_identification;
    public $date_naiss;
    public $reproducteur;
    public $fecondation;
    public $coeff_consang;
    public $conservatoire;
    public $code_race;
    public $id_pere;
    public $id_mere;
    public $nom_pere;
    public $nom_mere;
    public $no_identification_pere;
    public $no_identification_mere;
    public $father_information;
    public $mother_information;

    public function __set($id_animal, $value) {}
}
