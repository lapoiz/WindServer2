<?php
/**
 * Created by PhpStorm.
 * User: dapoi
 * Date: 09/08/14
 * Time: 01:08
 */

namespace LaPoiz\WindBundle\core\graph\spotJsonObject;


class WindOrientation {
    public $deg;
    public $nom;


    function __construct($name) {
        $this->nom=$name;
        $this->setDeg($name);
    }

    private function setDeg($name) {
        $result = 0;
        switch ($name) {
            case 'n' :
                $result = 0;
                break;
            case 'nne' :
                $result = 22.5;
                break;
            case 'ne' :
                $result = 45;
                break;
            case 'ene' :
                $result = 67.5;
                break;
            case 'e' :
                $result = 90;
                break;
            case 'ese' :
                $result = 112.5;
                break;
            case 'se' :
                $result = 135;
                break;
            case 'sse' :
                $result = 157.5;
                break;
            case 's' :
                $result = 180;
                break;
            case 'ssw' :
                $result = 202.5;
                break;
            case 'sw' :
                $result = 225;
                break;
            case 'wsw' :
                $result = 247.5;
                break;
            case 'w' :
                $result = 270;
                break;
            case 'wnw' :
                $result = 292.5;
                break;
            case 'nw' :
                $result = 315;
                break;
            case 'nnw' :
                $result = 337.5;
                break;
        }
        $this->deg=$result;
    }
} 