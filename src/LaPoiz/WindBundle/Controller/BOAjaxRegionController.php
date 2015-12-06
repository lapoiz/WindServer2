<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Region;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxRegionController extends Controller

{

    /**
     * @Template()
     * Ajoute à la region (id) le spot (idSpot)
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/region/add/spot/1/1
     */
    public function addSpotAction($id=null, $idSpot=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1 && isset($idSpot) && $idSpot!=-1  )
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $idSpot);
            $region = $em->find('LaPoizWindBundle:Region', $id);

            if (!$spot || !$region) {
                return new JsonResponse(array('message' => "L'id de region ou du spot n'ont pas étés trouvés dans la BD"), 400);
            }

            try {
                $previousRegion=$spot->getRegion();
                if (isset($previousRegion)) {
                    $previousRegion->removeSpot($spot);
                    $em->persist($previousRegion);
                }
                $region->addSpot($spot);
                $spot->setRegion($region);

                $em->persist($region);
                $em->persist($spot);
                $em->flush();

                return new JsonResponse(array('message' => 'Success!'), 200);
            } catch (\Exception $e) {
                return new JsonResponse(array('message' => "Exception"+$e->getMessage()), 400);
            }

        } else {
            return new JsonResponse(array('message' => "L'id de region ou du spot n'ont pas étés trouvés dans l'URL"), 400);
        }
    }

    /**
     * @Template()
     * Ajoute à la region (id) le spot (idSpot)
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/region/add/spot/1/1
     */
    public function removeSpotAction($id=null, $idSpot=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1 && isset($idSpot) && $idSpot!=-1  )
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $idSpot);
            $region = $em->find('LaPoizWindBundle:Region', $id);

            if (!$spot || !$region) {
                return new JsonResponse(array('message' => "L'id de region ou du spot n'ont pas étés trouvés dans la BD"), 400);
            }

            try {
                $region->removeSpot($spot);
                $spot->setRegion(null);

                $em->persist($region);
                $em->persist($spot);

                $em->flush();

                return new JsonResponse(array('message' => 'Success!'), 200);
            } catch (\Exception $e) {
                return new JsonResponse(array('message' => "Exception"+$e->getMessage()), 400);
            }

        } else {
            return new JsonResponse(array('message' => "L'id de region ou du spot n'ont pas étés trouvés dans l'URL"), 400);
        }
    }
}