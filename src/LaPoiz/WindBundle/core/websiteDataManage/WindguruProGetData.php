<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class WindguruProGetData extends WebsiteGetData
{
    Const windguruProURLLogin = 'https://www.windguru.cz/fr/prihlaseni.php';
    Const div_WERF9_id = 'div_wgfcst3';
    Const javascriptVar = 'wg_fcst_tab_data_3';
    Const exprWindPart = 'WINDSPD';
    Const exprOrientationPart = "WINDDIR";
	Const exprUpdateTime= 'update_last';
	
	function getDataURL($url) 
	{
        $client = new Client();
        // Problème avec : cURL error 60: SSL certificate problem: self signed certificate in certificate chain
        $guzzle = $client->getClient(); //You'll want to pull the Guzzle client out of Goutte to inherit its defaults
        $guzzle->setDefaultOption('verify', false); //Set the certificate at @mtdowling recommends
        $client->setClient($guzzle); //Tell Goutte to use your modified Guzzle client

        // On se connecte à WindGuruPro
        $crawler = $client->request('GET', WindguruProGetData::windguruProURLLogin);
        // sélectionne le formulaire et le remplit avec quelques valeurs
        $form = $crawler->selectButton('Entrer')->form();
        $form['userp'] = 'lapoiz';
        $form['psswd'] = 'liberte';
        $crawler = $client->submit($form);

        // On récupére les données en temps que personne connecté
        $crawler = $client->request('GET', $url);

		return $crawler->text();
	}
		
	function analyseData($tabData,$url) {
		// Don't use $tabData with Goutte

        //$client = new Client();
        //$crawler = $client->request('GET', $url);

		//$crawler = new Crawler($tabData);
        /*$crawler = $crawler->filter('#'.WindguruProGetData::div_WERF9_id);

		if ($crawler->text() == null) {
			// Ce spot est une spot personnel. Il n'est pas officiel, il a été créé par un utilisateur
			// Autre présentation

		}
*/
        // récupére la valeur de wg_fcst_tab_data_3
        //$data = WindguruProGetData::getJavascriptData($crawler->text());
		$data = WindguruProGetData::getJavascriptData($tabData);
        // {"id_spot":3627,"id_user":169,"nickname":"windguru","spot":"France - Saint-Aubin-sur-Mer","lat":49.8768,"lon":0.8025,"alt":49,"id_model":"21","model":"wrfeuh","model_alt":40,"levels":1,"sst":12,"sunrise":"06:03","sunset":"21:44","tz":"CEST","tzutc":"(UTC+2)","utc_offset":2,"tzid":"Europe\/Paris","tides":0,"md5chk":"a0b87b79cd807adc10791db0cbf6cb92","fcst":{"21":{"initstamp":1432512000,"TMP":[12.2,12.1,12,11.9,11.9,11.8,11.5,11.3,11.2,11.3,11.7,12,12.1,12.3,12.5,12.6,12.6,12.4,12,11.1,10.6,10.9,11.4,9.4,9.5,9.7,9.6,9.7,9.6,9.6,9.9,10.4,11,11.6,12.2,12.7,12.9,12.8,12.8,12.8,12.9,13,12.7,11.7,10.9,10.9,11.8,10.2,9.2,10.4,10,10,10.1,10,10,10.2,10.7,12,13,13.5,13.8,14.1,14.2,14.2,14.2,14,13.5,12.3,11.3,11,10.9,10.9,11.5,10.7,10.5,10.3,10.5,10.8,10.8],"RH":[99,100,100,100,100,98,93,89,86,83,80,76,72,69,68,69,72,75,81,89,93,96,96,98,100,100,100,100,100,100,100,99,97,93,90,85,83,83,82,81,80,80,81,86,91,93,92,96,98,99,100,100,99,99,99,98,94,86,76,72,70,70,71,72,74,77,82,87,90,89,89,89,83,82,83,87,89,87,86],"GUST":[13.4,9.6,6.9,5.1,6.7,10.6,10,9.2,7.8,7.6,8.5,8.7,9,9,9.2,10.2,10.9,11.6,12,13.4,12.2,10.4,8.4,3.8,0.5,1.5,2.6,4.5,5.6,6.5,6.7,5.8,5.1,5.1,5.4,6.6,7.3,8,8.2,8.7,8.8,8.8,10,12.6,12.6,12.2,9.9,7.1,3.8,1.8,2.5,3.5,4,3.9,4.1,3.9,4.2,6.7,8.5,11,12.2,12.8,12.5,12.1,12,12.7,10.7,14.7,14.4,15.9,16.1,16.4,18.3,20.3,20.7,20.5,21.2,21.1,21.5],"SLP":[1019,1018,1018,1018,1019,1019,1019,1019,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1021,1021,1021,1021,1021,1021,1021,1022,1023,1022,1023,1024,1024,1024,1024,1025,1025,1025,1025,1025,1025,1025,1025,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1025,1025,1024,1024,1023,1023,1023,1022,1022,1021,1020,1020,1019,1019,1018,1018,1018],"FLHGT":[2765,2791,2785,2754,2659,2656,2626,2589,2575,2543,2501,2453,2387,2324,2267,2228,2226,2236,2237,2295,2364,2412,2599,2775,3001,3191,3240,3195,3143,3127,3103,3027,2936,2882,2812,2708,2614,2558,2555,2591,2672,2759,2810,2825,2841,2856,2859,2864,2859,2839,2806,2788,2786,2796,2821,2839,2838,2839,2835,2818,2831,2842,2795,2774,2762,2739,2752,2742,2707,2688,2667,2631,2562,2564,2577,2570,2522,2470,2440],"APCP1":[null,0.2,0.4,0.4,0.3,0.3,0.1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0.1,0.1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"TCDC":[null,100,100,100,100,100,100,100,100,100,99,42,54,53,64,73,78,86,90,91,87,86,80,78,87,93,100,100,97,89,74,80,84,85,89,90,88,73,58,43,25,25,51,86,94,99,100,94,89,83,76,81,87,80,64,65,80,95,94,91,85,89,95,93,89,97,98,96,98,94,94,100,100,100,99,96,100,100,100],"HCDC":[50,80,92,92,94,71,71,84,71,48,37,36,40,49,55,66,78,76,61,48,42,38,34,26,18,0,0,0,0,4,26,63,79,60,26,18,0,0,0,0,0,0,0,0,0,8,12,15,17,33,46,63,83,78,64,65,78,77,71,74,84,89,95,93,89,97,98,96,98,91,93,93,96,96,95,96,97,97,97],"MCDC":[100,82,59,17,0,0,0,0,5,0,21,18,54,52,64,72,48,17,0,0,0,0,0,10,14,0,0,0,0,0,6,6,15,30,47,65,70,64,33,1,0,0,0,0,0,0,0,0,0,0,0,0,0,4,39,53,80,95,94,91,83,66,36,26,13,30,49,56,98,94,93,100,100,100,99,90,98,100,79],"LCDC":[100,100,100,100,100,100,100,100,100,100,99,39,2,0,0,25,58,86,90,91,87,86,80,78,87,93,100,100,97,89,74,80,84,85,89,90,88,70,58,43,25,25,51,86,94,99,100,94,89,83,76,81,86,76,56,51,22,44,22,1,1,6,16,36,39,46,56,44,22,0,0,0,0,7,73,90,84,100,100],"WINDSPD":[9.2,6.5,5.6,4.5,5.2,6.9,7.8,7.4,6.5,6.6,7.1,8,8,7.6,7.8,8.6,8.9,9,9,8.7,8.1,6.5,5.3,2.6,0.4,1.4,2.7,4.2,4.6,5.1,5.2,4.6,4.4,4.8,5.1,6.1,7.4,8.2,8.5,8.5,8,7.4,7.8,8.2,8.7,8.6,6.5,6.3,3.5,2.1,2.3,3,3.2,3,3.4,3.4,3.6,4.5,6.8,8.8,10,10.6,10.3,9.7,9.2,8.4,7,8.4,8.6,9,9.7,10.2,10.6,12,12.4,12.6,12.7,13.2,13],"WINDDIR":[274,296,303,338,10,16,19,8,2,352,349,348,347,341,328,317,309,303,294,286,284,303,328,341,322,207,209,216,230,247,268,293,327,343,342,330,324,320,315,309,303,294,282,270,254,260,275,277,279,281,258,262,264,258,266,271,276,281,290,291,287,284,284,282,279,277,269,265,259,248,250,249,246,245,243,245,248,252,248],"SMERN":["12","13","13","15","0","1","1","0","0","0","0","15","15","15","15","14","14","13","13","13","13","13","15","15","14","9","9","10","10","11","12","13","15","15","15","15","14","14","14","14","13","13","13","12","11","12","12","12","12","12","11","12","12","11","12","12","12","12","13","13","13","13","13","13","12","12","12","12","12","11","11","11","11","11","11","11","11","11","11"],"TMPE":[12.2,12.1,12,11.9,11.9,11.8,11.5,11.3,11.2,11.3,11.7,12,12.1,12.3,12.5,12.6,12.6,12.4,12,11.1,10.6,10.9,11.4,9.4,9.5,9.7,9.6,9.7,9.6,9.6,9.9,10.4,11,11.6,12.2,12.7,12.9,12.8,12.8,12.8,12.9,13,12.7,11.7,10.9,10.9,11.8,10.2,9.2,10.4,10,10,10.1,10,10,10.2,10.7,12,13,13.5,13.8,14.1,14.2,14.2,14.2,14,13.5,12.3,11.3,11,10.9,10.9,11.5,10.7,10.5,10.3,10.5,10.8,10.8],"PCPT":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"hr_weekday":[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4],"hr_h":["02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08"],"hr_d":["25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","28","28","28","28","28","28","28","28","28"],"hours":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78],"initdate":"2015-05-25 00:00:00","init_d":"25.05.2015","init_dm":"25.05.","init_h":"00","initstr":"2015052500","model_name":"WRF 9 km","id_model":"21","update_last":"Mon, 25 May 2015 08:10:45 +0000","update_next":"Mon, 25 May 2015 13:40:00 +0000","img_param":{"WINDSPD":"windspd","MWINDSPD":"windspd","SMER":"windspd","SMERN":"windspd","TMP":"tmp","TMPE":"tmp","APCP1":"tcdc_apcp1","APCP1s":"tcdc_apcp1","CDC":"tcdc","TCDC":"tcdc","SLP":"tcdc_apcp1"}}}}

        $tableauData = array();
		if ($data !=null) {
			// Identique à WindGuru (pas Pro)
			$windPart = WindguruProGetData::getPart(WindguruProGetData::exprWindPart, $data); // get wind part in line
			$tableauData['wind'] = WindguruProGetData::getElemeInPart($windPart);// transforme to tab

			// Identique à WindGuru (pas Pro)
			$hourePart = WindguruProGetData::getHourePart($data);
			$tableauData['heure'] = WindguruProGetData::getElemeInPart($hourePart);// transforme to tab

			// Identique à WindGuru (pas Pro)
			$datePart = WindguruProGetData::getDatePart($data);
			$tableauData['date'] = WindguruProGetData::getElemeInPart($datePart);// transforme to tab

			$orientationPart = WindguruProGetData::getPart(WindguruProGetData::exprOrientationPart, $data); // get wind part in line
			$tableauData['orientation'] = WindguruProGetData::getElemeInPart($orientationPart);// transforme to tab

			$updateTime = WindguruProGetData::getSpecialPart(WindguruProGetData::exprUpdateTime, $data);
			$tableauData['update'] = array($updateTime);
		}

		return $tableauData;
	}

    /**
     * @param $javascriptText: le javascript du HTML de Windguru:
     *<script language="JavaScript" type="text/javascript">
    //<![CDATA[
    var wg_fcst_tab_data_3 = {"id_spot":3627,"id_user":169,"nickname":"windguru","spot":"France - Saint-Aubin-sur-Mer","lat":49.8768,"lon":0.8025,"alt":49,"id_model":"21","model":"wrfeuh","model_alt":40,"levels":1,"sst":12,"sunrise":"06:03","sunset":"21:44","tz":"CEST","tzutc":"(UTC+2)","utc_offset":2,"tzid":"Europe\/Paris","tides":0,"md5chk":"a0b87b79cd807adc10791db0cbf6cb92","fcst":{"21":{"initstamp":1432512000,"TMP":[12.2,12.1,12,11.9,11.9,11.8,11.5,11.3,11.2,11.3,11.7,12,12.1,12.3,12.5,12.6,12.6,12.4,12,11.1,10.6,10.9,11.4,9.4,9.5,9.7,9.6,9.7,9.6,9.6,9.9,10.4,11,11.6,12.2,12.7,12.9,12.8,12.8,12.8,12.9,13,12.7,11.7,10.9,10.9,11.8,10.2,9.2,10.4,10,10,10.1,10,10,10.2,10.7,12,13,13.5,13.8,14.1,14.2,14.2,14.2,14,13.5,12.3,11.3,11,10.9,10.9,11.5,10.7,10.5,10.3,10.5,10.8,10.8],"RH":[99,100,100,100,100,98,93,89,86,83,80,76,72,69,68,69,72,75,81,89,93,96,96,98,100,100,100,100,100,100,100,99,97,93,90,85,83,83,82,81,80,80,81,86,91,93,92,96,98,99,100,100,99,99,99,98,94,86,76,72,70,70,71,72,74,77,82,87,90,89,89,89,83,82,83,87,89,87,86],"GUST":[13.4,9.6,6.9,5.1,6.7,10.6,10,9.2,7.8,7.6,8.5,8.7,9,9,9.2,10.2,10.9,11.6,12,13.4,12.2,10.4,8.4,3.8,0.5,1.5,2.6,4.5,5.6,6.5,6.7,5.8,5.1,5.1,5.4,6.6,7.3,8,8.2,8.7,8.8,8.8,10,12.6,12.6,12.2,9.9,7.1,3.8,1.8,2.5,3.5,4,3.9,4.1,3.9,4.2,6.7,8.5,11,12.2,12.8,12.5,12.1,12,12.7,10.7,14.7,14.4,15.9,16.1,16.4,18.3,20.3,20.7,20.5,21.2,21.1,21.5],"SLP":[1019,1018,1018,1018,1019,1019,1019,1019,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1020,1021,1021,1021,1021,1021,1021,1021,1022,1023,1022,1023,1024,1024,1024,1024,1025,1025,1025,1025,1025,1025,1025,1025,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1026,1025,1025,1024,1024,1023,1023,1023,1022,1022,1021,1020,1020,1019,1019,1018,1018,1018],"FLHGT":[2765,2791,2785,2754,2659,2656,2626,2589,2575,2543,2501,2453,2387,2324,2267,2228,2226,2236,2237,2295,2364,2412,2599,2775,3001,3191,3240,3195,3143,3127,3103,3027,2936,2882,2812,2708,2614,2558,2555,2591,2672,2759,2810,2825,2841,2856,2859,2864,2859,2839,2806,2788,2786,2796,2821,2839,2838,2839,2835,2818,2831,2842,2795,2774,2762,2739,2752,2742,2707,2688,2667,2631,2562,2564,2577,2570,2522,2470,2440],"APCP1":[null,0.2,0.4,0.4,0.3,0.3,0.1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0.1,0.1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"TCDC":[null,100,100,100,100,100,100,100,100,100,99,42,54,53,64,73,78,86,90,91,87,86,80,78,87,93,100,100,97,89,74,80,84,85,89,90,88,73,58,43,25,25,51,86,94,99,100,94,89,83,76,81,87,80,64,65,80,95,94,91,85,89,95,93,89,97,98,96,98,94,94,100,100,100,99,96,100,100,100],"HCDC":[50,80,92,92,94,71,71,84,71,48,37,36,40,49,55,66,78,76,61,48,42,38,34,26,18,0,0,0,0,4,26,63,79,60,26,18,0,0,0,0,0,0,0,0,0,8,12,15,17,33,46,63,83,78,64,65,78,77,71,74,84,89,95,93,89,97,98,96,98,91,93,93,96,96,95,96,97,97,97],"MCDC":[100,82,59,17,0,0,0,0,5,0,21,18,54,52,64,72,48,17,0,0,0,0,0,10,14,0,0,0,0,0,6,6,15,30,47,65,70,64,33,1,0,0,0,0,0,0,0,0,0,0,0,0,0,4,39,53,80,95,94,91,83,66,36,26,13,30,49,56,98,94,93,100,100,100,99,90,98,100,79],"LCDC":[100,100,100,100,100,100,100,100,100,100,99,39,2,0,0,25,58,86,90,91,87,86,80,78,87,93,100,100,97,89,74,80,84,85,89,90,88,70,58,43,25,25,51,86,94,99,100,94,89,83,76,81,86,76,56,51,22,44,22,1,1,6,16,36,39,46,56,44,22,0,0,0,0,7,73,90,84,100,100],"WINDSPD":[9.2,6.5,5.6,4.5,5.2,6.9,7.8,7.4,6.5,6.6,7.1,8,8,7.6,7.8,8.6,8.9,9,9,8.7,8.1,6.5,5.3,2.6,0.4,1.4,2.7,4.2,4.6,5.1,5.2,4.6,4.4,4.8,5.1,6.1,7.4,8.2,8.5,8.5,8,7.4,7.8,8.2,8.7,8.6,6.5,6.3,3.5,2.1,2.3,3,3.2,3,3.4,3.4,3.6,4.5,6.8,8.8,10,10.6,10.3,9.7,9.2,8.4,7,8.4,8.6,9,9.7,10.2,10.6,12,12.4,12.6,12.7,13.2,13],"WINDDIR":[274,296,303,338,10,16,19,8,2,352,349,348,347,341,328,317,309,303,294,286,284,303,328,341,322,207,209,216,230,247,268,293,327,343,342,330,324,320,315,309,303,294,282,270,254,260,275,277,279,281,258,262,264,258,266,271,276,281,290,291,287,284,284,282,279,277,269,265,259,248,250,249,246,245,243,245,248,252,248],"SMERN":["12","13","13","15","0","1","1","0","0","0","0","15","15","15","15","14","14","13","13","13","13","13","15","15","14","9","9","10","10","11","12","13","15","15","15","15","14","14","14","14","13","13","13","12","11","12","12","12","12","12","11","12","12","11","12","12","12","12","13","13","13","13","13","13","12","12","12","12","12","11","11","11","11","11","11","11","11","11","11"],"TMPE":[12.2,12.1,12,11.9,11.9,11.8,11.5,11.3,11.2,11.3,11.7,12,12.1,12.3,12.5,12.6,12.6,12.4,12,11.1,10.6,10.9,11.4,9.4,9.5,9.7,9.6,9.7,9.6,9.6,9.9,10.4,11,11.6,12.2,12.7,12.9,12.8,12.8,12.8,12.9,13,12.7,11.7,10.9,10.9,11.8,10.2,9.2,10.4,10,10,10.1,10,10,10.2,10.7,12,13,13.5,13.8,14.1,14.2,14.2,14.2,14,13.5,12.3,11.3,11,10.9,10.9,11.5,10.7,10.5,10.3,10.5,10.8,10.8],"PCPT":[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"hr_weekday":[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,4,4,4,4,4,4,4,4,4],"hr_h":["02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","00","01","02","03","04","05","06","07","08"],"hr_d":["25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","25","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","26","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","27","28","28","28","28","28","28","28","28","28"],"hours":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78],"initdate":"2015-05-25 00:00:00","init_d":"25.05.2015","init_dm":"25.05.","init_h":"00","initstr":"2015052500","model_name":"WRF 9 km","id_model":"21","update_last":"Mon, 25 May 2015 08:10:45 +0000","update_next":"Mon, 25 May 2015 13:40:00 +0000","img_param":{"WINDSPD":"windspd","MWINDSPD":"windspd","SMER":"windspd","SMERN":"windspd","TMP":"tmp","TMPE":"tmp","APCP1":"tcdc_apcp1","APCP1s":"tcdc_apcp1","CDC":"tcdc","TCDC":"tcdc","SLP":"tcdc_apcp1"}}}};
    var wgopts_3 = {"id_user":966827,"wj":"knots","tj":"c","waj":"m","odh":4,"doh":22,"wrap":40,"fhours":180,"limit1":11,"limit2":15,"limit3":19,"tlimit":10,"vt":1,"params":["WINDSPD","GUST","SMER","TMPE","CDC","APCP1s","RATING"],"first_row_mwinfo":true,"path_lng":"\/fr\/"};
    wgopts_3.lang = WgLang;
    WgFcst.showForecast(wg_fcst_tab_data_3,wgopts_3);
    //]]>
    </script>
     *
     * Return: uniquement la partie interressante transformable en JSON
     */
    function getJavascriptData($javascriptText) {
        $patternPart = '#'.WindguruProGetData::javascriptVar.'\s=\s(.*);var(.*)#';
        //$patternPart = '#wg_fcst_tab_data_3\s=\s(.*);var(.*)#';

        $javascriptText=str_replace(Chr(13), "", $javascriptText);
        $javascriptText=str_replace(Chr(10), "", $javascriptText);

        preg_match_all($patternPart,$javascriptText,$parts);
        $nb=count($parts[0]);
		if ($nb>0) {
			return $parts[1][0];
		} else {
			return null;
		}
    }

	function transformData($tableauData) {
		// $tableauData
		// wind  -> 17.5 | 12 | 10 | 14.5 | 15
		// orientation  -> 198 | 172 | 170 | 180 | 188
		// heure -> 13   | 19 | 22 | 01   | 04
		// date  -> 04   | 04 | 04 | 05   | 05
		
		$tableauWindData = array();
		$currentDate = '';
		$firstElem=true;
		$currenteLine=array();
		//$indexCol=0;
		
		foreach ($tableauData['date'] as $key=>$date) {
			if ($currentDate!=$date) {
				if ($firstElem) {
					$firstElem=false;
				} else {
					$tableauWindData[WindguruProGetData::getCompleteDate($currentDate)]=$currenteLine;
				}
				$currenteLine=array();
			}
			$dataPrev=array();
			$dataPrev["wind"]=$tableauData['wind'][$key];
			$dataPrev["heure"]=$tableauData['heure'][$key];
			if (isset($tableauData['orientation'][$key])) {
				$dataPrev["orientation"] = WebsiteGetData::transformeOrientationDegToNom($tableauData['orientation'][$key]);
			} else {
				$dataPrev["orientation"] = '?';
			}
			$currenteLine[$tableauData['heure'][$key]]=$dataPrev;
			$currentDate=$date;
			//$indexCol++;
		}
		$tableauWindData[WindguruProGetData::getCompleteDate($currentDate)]=$currenteLine;
		$tableauWindData['update']=array(array(WindguruProGetData::transformeUpdate($tableauData['update'][0])));

		return $tableauWindData;
	}
		

	static private function getElemeInPart($windPart) 
	{	 		
		return preg_split('/,/',$windPart);
	}
	
    static private function getPart($expres,$line) 
	{ 
      //$patternPart = '#\"WINDSPD\":\[([\d\.,\"]*)#';
      // la valeur peut parfoit être égale à "null"
      // $patternPart = '#\"'.$expres.'\":\[([\d\.,\"]*)#'; -> fonctionne hors "null"
      $patternPart = '#\"'.$expres.'\":\[([\d|null\.,\"]*)#';
	  preg_match_all($patternPart,$line,$parts);
		if (count($parts[1])>0) {
			return $parts[1][0];
		} else {
			return null;
		}
	}

	/**
	 * Recupere élément seul et non un tableau de données, ex: ,"update_last":"Sun, 13 Dec 2015 17:21:16 +0000",
	 */
	static private function getSpecialPart($expres,$line) {
		// \"update_last\":\"([\w,\s:+]+)\"
		$patternPart = '#\"'.$expres.'\":\"([\w,\s:+]+)\"#';
		preg_match_all($patternPart,$line,$parts);
		if (count($parts[1])>0) {
			return $parts[1][0];
		} else {
			return null;
		}

	}
	//need special with hr_d can't be send in param of a function...
    static private function getDatePart($line) 
	{
      $patternPart = '#\"hr\_d\":\[([\d\.,\"]*)#';
      //$patternPart = '#"hr_d":\[([0-9\.,"]+)\]#';
	  preg_match_all($patternPart,$line,$parts);
	  $result= preg_replace('#\"#','',$parts[1][0]);
      return $result;
	}

	// need special with hr_h can't be send in param of a function...
    static private function getHourePart($line) 
	{
      $patternPart = '#\"hr\_h\":\[([\d\.,\"]*)#';
	  preg_match_all($patternPart,$line,$parts);
	  $result= preg_replace('#\"#','',$parts[1][0]);
      return $result;
	}

	/**
	 * transforme date like '15' in saved date : '05/12/2011'
	 * @param string $date
	 */
	static private function getCompleteDate($date) {
		$today= new \DateTime("now");
		if ($today->format('d') > $date) {
			//next month
            $today->modify( '-2 day' );// if we do that late, and prevision of yesterday still here...
            if ($today->format('d') > $date) {
			    $today->modify( '+1 month' );
            }
		} elseif ($today->format('d')==1 && $date > 27) {
            // $today < $date et $today = 01/mm/aaaa et $date > 27 -> date = jour du mois précédent
            $today->modify( '-1 month' );
        }
		$result=$today->format('Y-m-').$date;
		return $result;
	}
	
	/**
	 * $windCalculate -> max | min | cumul | nbPrev
	 */
	static function calculateWind($windCalculate, $prevision) {
		$wind=$prevision->getWind();
		$windCalculate["max"]=($wind>$windCalculate["max"]?$wind:$windCalculate["max"]);
		$windCalculate["min"]=($wind<$windCalculate["min"]?$wind:$windCalculate["min"]);
		$windCalculate["cumul"]+=$wind;
		$windCalculate["nbPrev"]++;
	}
	
	/*
	 * 3627|3|1281931200|169|'France - Saint-Aubin-sur-Mer'|'16.08. 2010 06'|1|0|2|23|46|49.8768|0.8025
	 */
	private function getDateFromHTML($htmlLine) {
		preg_match('#([0-9]{2}).([0-9]{2}).\s([0-9]{4})\s#',$htmlLine[5],$data);
		return $data[3].'-'.$data[2].'-'.$data[1];
	}

	/*
	 * $htmlUpdate: "Sun, 13 Dec 2015 17:21:16 +0000"
	 * return : 2015-12-13 18:21:16
	 */
	private function transformeUpdate($htmlUpdate) {
		//preg_match('#(\w+), (\d+) (\w+) (\d+) (\d+):(\d+):(\d+)#/i',$htmlUpdate,$data);
		//preg_match('#(\w|,|\s|\d|:)+#',$htmlUpdate,$data);
		// on a séparé chque groupe d'element, il faut juste transformer le mois
		$date=strtotime($htmlUpdate);
		$result=date('Y-m-d H:i:s',$date);
		return $result;
	}
	
}