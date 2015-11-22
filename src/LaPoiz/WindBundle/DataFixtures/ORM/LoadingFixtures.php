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
        $webSiteWG->setLogo("Windguru.png");
        $manager->persist($webSiteWG);
        $manager->flush();


        $webSiteWGPro = new WebSite();
        $webSiteWGPro->setNom(WebsiteGetData::windguruProName);
        $webSiteWGPro->setUrl("www.windguru.cz");
        $webSiteWGPro->setLogo("WindguruPro.png");
        $manager->persist($webSiteWGPro);
        $manager->flush();
        
        $webSiteWF = new WebSite();
        $webSiteWF->setNom(WebsiteGetData::windFinderName);
        $webSiteWF->setUrl("www.windfinder.com");
        $webSiteWF->setLogo("Windfinder.png");
        $manager->persist($webSiteWF);
        $manager->flush();

        $webSiteMF = new WebSite();
        $webSiteMF->setNom(WebsiteGetData::meteoFranceName);
        $webSiteMF->setUrl("http://france.meteofrance.com/");
        $webSiteMF->setLogo("MeteoFrance.png");
        $manager->persist($webSiteMF);
        $manager->flush();
        
        /*$balise=new Balise();
        $balise->setNom('Balise FFVL de St Aubin');
        $balise->setUrl('http://balisemeteo.com/balise.php?idBalise=56');
        $balise->setDescription('Balise de la Federation Francais de Vol Libre');
        */

        $dataWindPrevWG = new DataWindPrev();
        $dataWindPrevWG->setUrl('http://www.windguru.cz/fr/index.php?sc=3627');
        $dataWindPrevWG->setWebsite($webSiteWG);
        $dataWindPrevWG->setCreated(new \DateTime("now"));
        
        $dataWindPrevWF = new DataWindPrev();
        $dataWindPrevWF->setUrl('http://www.windfinder.com/forecast/saint_aubin_sur_mer');
		$dataWindPrevWF->setWebsite($webSiteWF);
        $dataWindPrevWF->setCreated(new \DateTime("now"));


        $dataWindPrevMF = new DataWindPrev();
        $dataWindPrevMF->setUrl('http://www.meteofrance.com/previsions-meteo-france/saint-aubin-sur-mer/76740');
        $dataWindPrevMF->setWebsite($webSiteMF);
        $dataWindPrevMF->setCreated(new \DateTime("now"));


        $spot=new Spot();
        $spot->setNom("Saint Aubin");
        $spot->setDescription("Spot avec grande plage, sans risque.");
        $spot->setLocalisationDescription('Proche de Saint-Aubin-sur-mer prendre a gauche dans le début du village: route de Saussemare');
        $spot->setGpsLong('0.862329');
        $spot->setGpsLat('49.892945');
        //$spot->setBalise($balise);
        //$balise->setSpot($spot);
        
        $dataWindPrevWG->setSpot($spot);
        $dataWindPrevWF->setSpot($spot);
        $dataWindPrevMF->setSpot($spot);
        $spot->addDataWindPrev($dataWindPrevMF);
        $spot->addDataWindPrev($dataWindPrevWG);        
        $spot->addDataWindPrev($dataWindPrevWF);

        $spot->setMareeURL("http://maree.info/16");

        $manager->persist($dataWindPrevWG);
        $manager->persist($dataWindPrevWF);
        $manager->persist($dataWindPrevMF);
        $manager->persist($spot);
        //$manager->persist($balise);



        // ************************
        // **** Alamanarre  *******
        // ************************

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
        
    }         
}