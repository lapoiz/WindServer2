<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM;
 
use LaPoiz\WindBundle\Entity\WebSite;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class MeteoFranceFixtures implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {
             
        $webSiteWG = new WebSite();
        $webSiteWG->setNom(WebsiteGetData::meteoFranceName);
        $webSiteWG->setUrl("http://france.meteofrance.com/");
        $webSiteWG->setLogo("logo");
        $manager->persist($webSiteWG);
        $manager->flush();
        
    }         
}