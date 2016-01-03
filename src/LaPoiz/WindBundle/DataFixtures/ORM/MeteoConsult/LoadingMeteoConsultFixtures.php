<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM\MeteoConsult;
 

use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadingMeteoConsultFixtures implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {
        $webSiteMC = new WebSite();
        $webSiteMC->setNom(WebsiteGetData::meteoConsultName);
        $webSiteMC->setUrl("http://marine.meteoconsult.fr");
        $webSiteMC->setLogo("Meteoconsult.jpg");
        $manager->persist($webSiteMC);
        $manager->flush();
   }
}