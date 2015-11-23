<?php

namespace LaPoiz\WindBundle\core\nbHoure;

// SI MODIFIE, LE FAIRE EGALEMENT pour LaPoiz\GraphBundle\Resources\public\js\meteoFranceIcon.js
class MeteoFranceIcon
{
    private static $_instance = null;
    private $_tabMeteo = null;

    private function __construct() {
        $this->_tabMeteo = array();
        $value=0;

        $this->_tabMeteo["en"]=$value++;  // Ensoleillé - OK
        $this->_tabMeteo[""]=$value++;    // Ensoleillé nuit - OK
        $this->_tabMeteo["c-v"]=$value++; // Ciel voilé - OK
        $this->_tabMeteo[""]=$value++;    // Ciel voilé - OK
        $this->_tabMeteo["ec"]=$value++;  // Eclaircie - OK
        $this->_tabMeteo[""]=$value++;    // Eclaircie de nuit - OK

        $this->_tabMeteo["tn"]=$value;    // tres nuageux - OK
        $this->_tabMeteo["t-n"]=$value++; // tres nuageux - OK

        $this->_tabMeteo["b"]=$value++;   // brume - OK
        $this->_tabMeteo[""]=$value++;    // brume de nuit - OK
        $this->_tabMeteo[""]=$value++;    // bancs de brouillard
        $this->_tabMeteo[""]=$value++;    // Brouillard
        $this->_tabMeteo[""]=$value++;    // Brouillard Givrant

        $this->_tabMeteo[""]=$value++;   // Bruine / Pluie faible

        $this->_tabMeteo[""]=$value++;   // Pluie verglaçante
        $this->_tabMeteo[""]=$value++;   // Pluie verglaçante de nuit
        $this->_tabMeteo[""]=$value++;   // Pluie verglaçante

        $this->_tabMeteo["p-e"]=$value++; // Pluies éparses - OK
        $this->_tabMeteo[""]=$value++;    // Pluies éparses de nuit - OK
        $this->_tabMeteo["r-a"]=$value++; // Rares averses - OK

        $this->_tabMeteo["a"]=$value++;   // Averses
        $this->_tabMeteo[""]=$value++;    // Averse de nuit
        $this->_tabMeteo["p"]=$value++;   // Pluie - OK

        $this->_tabMeteo[""]=$value++;    // Pluie forte
        $this->_tabMeteo["p-o"]=$value++; // Pluie orageuses ?
        $this->_tabMeteo[""]=$value++;    // Pluie orageuses de nuit - OK
        $this->_tabMeteo["a-o"]=$value++; // Averses orageuses

        $this->_tabMeteo[""]=$value++;    //  Quelques flocons
        $this->_tabMeteo[""]=$value++;    //  Quelques flocons de nuit
        $this->_tabMeteo[""]=$value++;    //  Quelques flocons

        $this->_tabMeteo[""]=$value++;    //  neige
        $this->_tabMeteo[""]=$value++;    //  neige de nuit
        $this->_tabMeteo[""]=$value++;    //  Averse de neige

        $this->_tabMeteo[""]=$value++;    //  Neige forte

        $this->_tabMeteo["r-g"]=$value++; //  Risque de grele
        $this->_tabMeteo[""]=$value++;    //  Risque de grele de nuit
        $this->_tabMeteo[""]=$value++;    //  Risque de grele

        $this->_tabMeteo["r-o"]=$value++; // Risque d'orage
        $this->_tabMeteo[""]=$value++;    // Risque d'orage de nuit
        $this->_tabMeteo[""]=$value++;    // Risque d'orage

        $this->_tabMeteo["o"]=$value++;   // Orages
        $this->_tabMeteo[""]=$value++;    // Orage la nuit
        $this->_tabMeteo[""]=$value++;    // Orages


        $this->_tabMeteo["?"]=$value++;  // Inconnu
        $this->_tabMeteo["i"]=$value++;  // Iceberck -> inventé
    }
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new MeteoFranceIcon();
        }

        return self::$_instance;
    }

    public function getTabMeteo() {
        return $this->_tabMeteo;
    }
}