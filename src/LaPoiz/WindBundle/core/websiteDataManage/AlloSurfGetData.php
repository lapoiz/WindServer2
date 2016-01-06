<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class AlloSurfGetData extends WebsiteGetData
{

	const nbJoursPrev=3;

	const dataTabClass='meteosurf-table';
	const hourLineSelector='tbody > tr';
	const elemLineSelector='td';


	/**
	 * @param $url
	 * @return Crawler
	 */
	function getDataURL($url)	{
		$client = new Client();
		$jsonURL=AlloSurfGetData::getJsonUrl($url);
		$crawler = $client->request('GET', $jsonURL);
		return $crawler;
	}


	/**
	 * @param $pageHTML: crawler de Goutte
	 * @param $url: URL de la page (ex:http://www.allosurf.net/meteo/surf/st-aubin-meteo-wam-1-wrf-5-96-h-278.html)
	 * @return array|null
	 */
	function analyseData($jsonCrawler,$url)
	{
		$previsionTab = array();
		// Transforme JSON en array
		// http://www.allosurf.net/meteo/spot/data.php?spoId=278&meteo=wam_1_wrf_5_96_h&start=1&end=4
		$data=json_decode($jsonCrawler->text(),true);

		foreach ($data['jour'] as $day) {
			$dayTab=array();

			foreach ($day['heures'] as $heure) {
				$heureTab=array();
				$heureTab['date']=$day['date'];
				$heureTab['heure']=AlloSurfGetData::getHoureClean($heure['hour']);
				$heureTab['wind']=$heure['wind_10m_kts'];
				$heureTab['orientation']=AlloSurfGetData::getOrientationClean($heure['gly_ori_wsp']);
				$heureTab['temp']=$heure['temp'];

				$dayTab[]=$heureTab;
			}

			$previsionTab[$day['date']]=$dayTab;
		}
		return $previsionTab;
	}

	function transformData($tableauData) {
		return $tableauData;
	}

	/**
	 * @param $url du type: http://www.allosurf.net/meteo/surf/st-aubin-meteo-wam-1-wrf-5-96-h-278.html
	 * @return l'url JSOn du type: http://www.allosurf.net/meteo/spot/data.php?spoId=278&meteo=wam_1_wrf_5_96_h&start=1&end=4
	 */
	private function getJsonUrl($url) {

		// Transforme $elem=st-aubin-meteo-wam-1-wrf-5-96-h-278.html => spoId=278&meteo=wam_1_wrf_5_96_h&start=1&end=4
		// l-almanarre-meteo-wam-5-wrf-5-96-h-755.html => spoId=755&meteo=wam_5_wrf_5_96_h&start=1&end=4
		if (preg_match('#-(\d+).html#',$url,$value)>0) {
			$spotId=$value[1];
			return 'http://www.allosurf.net/meteo/spot/data.php?spoId='.$spotId.'&meteo=wam_1_wrf_5_96_h&start=1&end=4';
		} else {
			return null;
		}
	}

	// input: wind_sse
	// return: sse
	// attention wind_oso -> wsw
	// attention wind_sss -> s
	static private function getOrientationClean($htmlValue) {
		$htmlValue=str_replace('o','w',$htmlValue);
		if (preg_match('#wind_([snwe]+)#',$htmlValue,$value)>0) {
			$result=$value[1];
			switch ($result) {
				case 'sss':
					$result='s';
					break;
				case 'nnn':
					$result='n';
					break;
				case 'www':
					$result='w';
					break;
				case 'eee':
					$result='e';
					break;
			}
			return $result;
		} else {
			return null;
		}
	}

	// input: 05
	// return: 5
	static private function getHoureClean($htmlValue) {
		if (preg_match('#0?(\d+)#',$htmlValue,$value)>0) {
			 return $value[1];
		} else {
			return null;
		}
	}


	static function typeDisplay($step) {
		$result="text";
		switch ($step) {
			case 10: $result="text";break; // display error texte
			case 0: $result="text";break;
			case 1: $result="code";break;
			case 2: $result="arrayOfArrayOfArray";break;
			case 3: $result="arrayOfArrayOfArray";break;
			case 4: $result="prevDate";break;
		}
		return $result;
	}

}