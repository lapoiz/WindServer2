<?php

namespace LaPoiz\WindBundle\Twig;


use LaPoiz\WindBundle\core\nbHoure\MeteoFranceIcon;

class LaPoizExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('meteoFrance_logo', array($this, 'meteoFranceLogoFilter')),
            new \Twig_SimpleFilter('meteoFrance_value', array($this, 'meteoFranceValueFilter')),
        );
    }

    public function meteoFranceLogoFilter($meteoFranceSigle)
    {
        // images de 35 px -> 1260 px pour 43 icons -> 29.3px / icon
        $value=MeteoFranceIcon::getInstance()->getTabMeteo()[$meteoFranceSigle];

        return $value*100/44;
    }

    public function meteoFranceValueFilter($meteoFranceSigle)
    {
        // images de 35 px -> 1260 px pour 43 icons -> 29.3px / icon
        $value=MeteoFranceIcon::getInstance()->getTabMeteo()[$meteoFranceSigle];

        return $value;
    }
    public function getName()
    {
        return 'lapoiz_extension';
    }
}