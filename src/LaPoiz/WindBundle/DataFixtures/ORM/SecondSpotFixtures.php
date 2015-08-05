<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM;
 
use LaPoiz\WindBundle\Entity\SpotParameter;
//use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Entity\Spot;
//use LaPoiz\WindBundle\Entity\WindCondition;
//use LaPoiz\WindBundle\Entity\MareeCondition;
//use LaPoiz\WindBundle\Entity\Balise;
use LaPoiz\WindBundle\Entity\DataWindPrev;
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

        $spot=new Spot();
        $spot->setNom("Almanarre");
        $spot->setDescription("Spot mythique proche de Hyeres.");
        $spot->setLocalisationDescription('A Hyères suivre Giens (D559), puis prendre Route du sel. Le spot est balisé.');
        $spot->setGpsLong('43.056244');
        $spot->setGpsLat('6.133105');


        $dataWindPrevWG = new DataWindPrev();
        $dataWindPrevWG->setUrl('http://www.windguru.cz/fr/index.php?sc=14&sty=m_spot');
        $dataWindPrevWG->setWebsite($webSiteWG);
        $dataWindPrevWG->setCreated(new \DateTime("now"));

        $dataWindPrevWF = new DataWindPrev();
        $dataWindPrevWF->setUrl('http://fr.windfinder.com/forecast/l_almanarre');
        $dataWindPrevWF->setWebsite($webSiteWF);
        $dataWindPrevWF->setCreated(new \DateTime("now"));


        $dataWindPrevMF = new DataWindPrev();
        $dataWindPrevMF->setUrl('http://www.meteofrance.com/previsions-meteo-france/hyeres/83400');
        $dataWindPrevMF->setWebsite($webSiteMF);
        $dataWindPrevMF->setCreated(new \DateTime("now"));


        $dataWindPrevWG->setSpot($spot);
        $dataWindPrevWF->setSpot($spot);
        $dataWindPrevMF->setSpot($spot);
        $spot->addDataWindPrev($dataWindPrevMF);
        $spot->addDataWindPrev($dataWindPrevWG);
        $spot->addDataWindPrev($dataWindPrevWF);

        $manager->persist($dataWindPrevWG);
        $manager->persist($dataWindPrevWF);
        $manager->persist($dataWindPrevMF);
        $manager->persist($spot);

        $manager->flush();
        */
    }         
}