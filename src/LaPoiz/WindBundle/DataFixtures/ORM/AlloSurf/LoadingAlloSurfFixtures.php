<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM\MeteoConsult;
 

use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadingAlloSurfFixtures implements FixtureInterface
{

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {
        $webSiteAS = new WebSite();
        $webSiteAS->setNom(WebsiteGetData::alloSurfName);
        $webSiteAS->setUrl("http://www.allosurf.net/");
        $webSiteAS->setLogo("AlloSurf.png");
        $manager->persist($webSiteAS);
        $manager->flush();
   }
}