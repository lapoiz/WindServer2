<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use LaPoiz\WindBundle\core\graph\TransformeToHighchartsDataTabForJson;
use LaPoiz\WindBundle\core\graph\SpotJsonObject;

class FOJsonController extends Controller

{	

	/**
	 * @Template()
	*/
    // http://localhost/WindServer/web/app_dev.php/fo/json/spot/data/1
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
}