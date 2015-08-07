<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use LaPoiz\WindBundle\core\graph\TransformeToHighchartsDataTabForJson;
use LaPoiz\WindBundle\core\graph\TransformeToLaPoizGraphDataTabForJson;
use LaPoiz\WindBundle\core\graph\SpotJsonObject;

class FOJsonController extends Controller

{	

	/**
	 * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/json/spot/data/1
     * Used on Highcharts
	*/
	public function getAction($id=null)
	{
		$em = $this->container->get('doctrine.orm.entity_manager');
		if (isset($id) && $id!=-1)
		{
			$spot = $em->find('LaPoizWindBundle:Spot', $id);
			if (!$spot)
			{
				return new JsonResponse(array(
					'success' => false,
					'description' => "No spot find in GetAction"
				));
			}
			// Normal way, we find spot

			$tabJson = TransformeToHighchartsDataTabForJson::createResultJson($spot);

			foreach ($spot->getDataWindPrev() as $dataWindPrev) {
			    // Pour chaque site du spot
                // Récupére toutes les prévisions réalisées la même date que de création de la derniere prévision
                //DELETE this line ?? $name=$dataWindPrev->getWebsite()->getNom();
				$previsionDateList = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->getLastCreated($dataWindPrev);
				$tabJson->addForecast(TransformeToHighchartsDataTabForJson::transformePrevisionDateList($previsionDateList));
			}

            // Récupére les previsions de la Marée prévue à partir d'aujourdhui
            $futureMareeDateList = $this->getDoctrine()->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
            $tabJson->addForecast(TransformeToHighchartsDataTabForJson::transformeMareeDateList($futureMareeDateList,$this->getDoctrine()));

			return new JsonResponse($tabJson);
		} else {
			return new JsonResponse(array(
					'success' => false,
					'description' => "Parameter not found (id of spot)"
				));
		}
	}


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/json/lapoizgraph/spot/1
     * Used on LaPoizGraphBundle
     */
    public function getLaPoizGraphAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return new JsonResponse(array(
                    'success' => false,
                    'description' => "No spot find in GetAction"
                ));
            }
            // Normal way, we find spot
            TransformeToHighchartsDataTabForJson::createResultJson($spot);
            $tabJson=TransformeToLaPoizGraphDataTabForJson::createResultJson($spot);;

            foreach ($spot->getDataWindPrev() as $dataWindPrev) {
                // Pour chaque site du spot
                // Récupére toutes les prévisions réalisées la même date que de création de la derniere prévision
                $forecast=array();
                $forecast["nom"]=$dataWindPrev->getWebsite()->getNom();
                $forecast["date"]=$dataWindPrev->getCreated()->format('d-m-Y');

                $previsionDateList = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->getLastCreated($dataWindPrev);
                $forecast["previsions"]=TransformeToLaPoizGraphDataTabForJson::transformePrevisionDateList($previsionDateList);
                $tabJson["forecast"][]=$forecast;
            }

            // Récupére les previsions de la Marée prévue à partir d'aujourdhui
            //$futureMareeDateList = $this->getDoctrine()->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
            //$tabJson->addForecast(TransformeToLaPoizGraphDataTabForJson::transformeMareeDateList($futureMareeDateList,$this->getDoctrine()));

            return new JsonResponse($tabJson);
        } else {
            return new JsonResponse(array(
                'success' => false,
                'description' => "Parameter not found (id of spot)"
            ));
        }
    }



    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/json/listeWebsite/spot/1
     *
     * Return the website list of the spot
     */

    public function listWebsiteAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return new JsonResponse(array(
                    'success' => false,
                    'description' => "No spot find in GetAction"
                ));
            }
            // Normal way, we find spot

            $tabListResponse = array();

            foreach ($spot->getDataWindPrev() as $dataWindPrev) {
                // Pour chaque site du spot on récupére le nom
                $tabListResponse[] =$dataWindPrev->getWebsite()->getNom();
            }

            /*return new JsonResponse(array(
                'success' => true,
                'data' => json_encode($tabListResponse)
            ));*/

            return new JsonResponse($tabListResponse);

        } else {
            return new JsonResponse(array(
                'success' => false,
                'description' => "Parameter not found (id of spot)"
            ));
        }
    }


}