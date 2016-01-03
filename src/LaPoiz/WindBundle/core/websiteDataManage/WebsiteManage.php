<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;


use Doctrine\ORM\EntityManager;

abstract class WebsiteManage {

    /**
     * @param $urlWebsite: url d'un site de prevision
     * @return l'objet Website correspondant
     *
     * url avec ou sans www http https ...
     *
     */
	public static function getWebSiteEntityFromURL($urlWebsite, EntityManager $em) {
        // On enleve de l'url :www http https
        $arrayURL=parse_url($urlWebsite);
        if (count($arrayURL)>1) { // pas uniquement path
            try {
                $nameHost = explode('.', $arrayURL['host']);
                if (isset($nameHost) && count($nameHost) > 0) {
                    $nameUrlWebSite = ($nameHost[0] === 'www' ? $nameHost[1] : $nameHost[0]);
                    $nameWebSite = null;
                    switch ($nameUrlWebSite) {
                        case 'meteofrance':
                            $nameWebSite = WebsiteGetData::meteoFranceName;
                            break;
                        case 'windfinder':
                            $nameWebSite = WebsiteGetData::windFinderName;
                            break;
                        case 'windguru':
                            $nameWebSite = WebsiteGetData::windguruName;
                            break;
                        case 'meteoconsult':
                            $nameWebSite = WebsiteGetData::meteoConsultName;
                            break;
                        case 'marine':
                            $nameWebSite = WebsiteGetData::meteoConsultName;
                            break;
                    }
                    if ($nameWebSite != null) {
                        return $em->getRepository('LaPoizWindBundle:WebSite')->findWithName($nameWebSite);
                    }
                }
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
	}
}