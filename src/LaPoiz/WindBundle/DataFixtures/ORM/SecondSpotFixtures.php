<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM;
 
use LaPoiz\WindBundle\Entity\SpotParameter;
//use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Entity\Spot;
//use LaPoiz\WindBundle\Entity\WindCondition;
//use LaPoiz\WindBundle\Entity\MareeCondition;
//use LaPoiz\WindBundle\Entity\Balise;
//use LaPoiz\WindBundle\Entity\DataWindPrev;
//use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SecondSpotFixtures implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {

        /*
        $balise=new Balise();
        $balise->setNom('Balise FFVL de St Aubin');
        $balise->setUrl('http://balisemeteo.com/balise.php?idBalise=56');
        $balise->setDescription('Balise de la Federation Francais de Vol Libre');
        */
        /*
        $mareeCondition = new MareeCondition();
        $mareeCondition->setQuality('Ideal');
        $mareeCondition->setMaree('basse');
        */  
        $spot=new Spot();
        $spot->setNom("Almanarre");
        $spot->setDescription("Spot mythique proche de Hyeres.");
        $spot->setIsKitePractice(true);
        $spot->setIsWindsurfPractice(true);
        $spot->setGoogleMapURL('http://maps.google.fr/maps?ll=43.056244,6.133105&spn=0.019989,0.049567&z=15&msa=0&msid=205227836204694166614.0004a72c27fbff9038758');
        $spot->setLocalisationDescription('A Hyères suivre Giens (D559), puis prendre Route du sel. Le spot est balisé.');
        $spot->setGpsLong('43.056244');
        $spot->setGpsLat('6.133105');

        $manager->persist($spot);
        //$manager->persist($balise);
        
        
        $manager->flush();
        
    }         
}