<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM;
 
use LaPoiz\WindBundle\Entity\SpotParameter;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Entity\Spot;
//use LaPoiz\WindBundle\Entity\WindCondition;
//use LaPoiz\WindBundle\Entity\MareeCondition;
use LaPoiz\WindBundle\Entity\Balise;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadingFixtures implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {
             
        $webSiteWG = new WebSite();
        //$webSiteWG->setNom("Windguru");
        $webSiteWG->setNom(WebsiteGetData::windguruName);
        $webSiteWG->setUrl("www.windguru.cz");
        $webSiteWG->setLogo("logo");
        $manager->persist($webSiteWG);
        $manager->flush();
        
        
        $webSiteWF = new WebSite();
        $webSiteWF->setNom(WebsiteGetData::windFinderName);
        $webSiteWF->setUrl("www.windfinder.com");
        $webSiteWF->setLogo("logo");
        $manager->persist($webSiteWF);
        $manager->flush();

        $webSiteMF = new WebSite();
        $webSiteMF->setNom(WebsiteGetData::meteoFranceName);
        $webSiteMF->setUrl("http://france.meteofrance.com/");
        $webSiteMF->setLogo("logo");
        $manager->persist($webSiteMF);
        $manager->flush();
        
        $balise=new Balise();
        $balise->setNom('Balise FFVL de St Aubin');
        $balise->setUrl('http://balisemeteo.com/balise.php?idBalise=56');
        $balise->setDescription('Balise de la Federation Francais de Vol Libre');

        $dataWindPrevWG = new DataWindPrev();
        $dataWindPrevWG->setUrl('http://www.windguru.cz/fr/index.php?sc=3627');
        $dataWindPrevWG->setWebsite($webSiteWG);
        $dataWindPrevWG->setSlotTime(7);
        $dataWindPrevWG->setCreated(new \DateTime("now"));
        
        $dataWindPrevWF = new DataWindPrev();
        $dataWindPrevWF->setUrl('http://www.windfinder.com/forecast/saint_aubin_sur_mer');
		$dataWindPrevWF->setWebsite($webSiteWF);
        $dataWindPrevWF->setSlotTime(8);
        $dataWindPrevWF->setCreated(new \DateTime("now"));


        $dataWindPrevMF = new DataWindPrev();
        $dataWindPrevMF->setUrl('http://www.meteofrance.com/previsions-meteo-france/saint-aubin-sur-mer/76740');
        $dataWindPrevMF->setWebsite($webSiteMF);
        $dataWindPrevMF->setSlotTime(8);
        $dataWindPrevMF->setCreated(new \DateTime("now"));


        $spot=new Spot();
        $spot->setNom("Saint Aubin");
        $spot->setDescription("Spot avec grande plage, sans risque.");
        $spot->setIsKitePractice(true);
        $spot->setIsWindsurfPractice(true);
        $spot->setGoogleMapURL('http://maps.google.fr/maps?ll=49.893141,0.869551&spn=0.019989,0.049567&z=15&msa=0&msid=205227836204694166614.0004a72c27fbff9038758');
        $spot->setLocalisationDescription('Proche de Saint-Aubin-sur-mer prendre a gauche dans le début du village: route de Saussemare');
        $spot->setGpsLong('49.893141');
        $spot->setGpsLat('0.869551');
        $spot->setBalise($balise);
        $balise->setSpot($spot);
        
        $dataWindPrevWG->setSpot($spot);
        $dataWindPrevWF->setSpot($spot);
        $dataWindPrevMF->setSpot($spot);
        $spot->addDataWindPrev($dataWindPrevMF);
        $spot->addDataWindPrev($dataWindPrevWG);        
        $spot->addDataWindPrev($dataWindPrevWF);

        $spotParameter = new SpotParameter();
        $spotParameter->setMareeURL("http://maree.info/16");
        $spotParameter->setSpot($spot);
        $spot->setParameter($spotParameter);


        $manager->persist($dataWindPrevWG);
        $manager->persist($dataWindPrevWF);
        $manager->persist($dataWindPrevMF);
        $manager->persist($spotParameter);
        $manager->persist($spot);
        $manager->persist($balise);
        
        
        $manager->flush();
        
    }         
}