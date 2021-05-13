<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Genis\Domain\Animal\Data;

/**
 * Description of AnimalInternalExport
 *
 * @author Christophe
 */
class AnimalInternalExport {
    
    /**
     *IMPORTANT: DO NOT CHANGE PROPERTY NAMES!!!
     * If names need to be changed, make sure to adjust the read_all_animals_for_export()
     * method in the Genis\Domain\Animal\Repositories\AnimalsExportReaderRepository class!!
     */
    public $nom;
    public $no_identification;
    public $sexe;
    public $date_naissance;
    public $livre_genealogique;
    public $etat;
    public $nom_mere;
    public $no_identification_mere;
    public $famille;
    public $nom_pere;
    public $no_identification_pere;
    public $lignee;
    public $nom_race;
    public $cheptel_actuel;
    public $nom_contact_cheptel_actuel;
    public $prenom_contact_cheptel_actuel;
    public $nom_elevage_naisseur;
    public $no_elevage_naisseur;
}
