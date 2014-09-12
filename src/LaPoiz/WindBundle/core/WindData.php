<?php
namespace LaPoiz\WindBundle\core;

use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WindguruGetData;
use LaPoiz\WindBundle\core\websiteDataManage\MeteoFranceGetData;

abstract class WindData {
	
	static function parseAndSaveDataWindPrev($dataWindPrev) {
		switch ($dataWindPrev->getWebsite()->getNom()) {
			case "MeteoFrance":
				
				break;
			case "Windguru":
				WindguruGetData->parseURL($dataWindPrev->getUrl());
				break;			
		}
	}
	
	static function getTableauDataHTML($tableauData) {
		$str= '<table  BORDER="1">';
		foreach( $tableauData as $tag => $line )
		{
			$str.='<tr><td><strong>'.$tag.'</strong></td>';
			
			foreach( $line as $elem ) 
			{
				$str.='<td>'.$elem.'</td>';
			}
			$str.='</tr>';	
		}
		$str .= '</table>';
		return $str;
	}

	static function getTableauDataHTMLWithTitle($tableauData) {
		$str= '<table  BORDER="1">';
		if (sizeof($tableauData)>0) {
			$str.='<tr><th>num Line</th>';
			foreach($tableauData[0] as $title => $line) 
			{
				$str.='<th>'.$title.'</th>';
			}
			$str.='<tr>';
		}
		foreach( $tableauData as $tag => $line )
		{
			$str.='<tr><td><strong>'.$tag.'</strong></td>';
			if (sizeof($line)>0) {
				foreach( $line as $elem ) 
				{
					$str.='<td>'.$elem.'</td>';
				}
			}
			$str.='</tr>';	
		}
		$str .= '</table>';
		return $str;
	}
	
	
	static function getSmalleTableauDataHTML($tableauData) {
		$str= '<table  BORDER="1">';
		foreach( $tableauData as $tag => $elem )
		{
			$str.='<tr><td><strong>'.$tag.'</strong></td>';			
			$str.='<td>'.$elem.'</td>';			
			$str.='</tr>';	
		}
		$str .= '</table>';
		return $str;
	}
	
	static function getWindNoeud($windKmH) {
		return $windKmH*0.54;
	}
}