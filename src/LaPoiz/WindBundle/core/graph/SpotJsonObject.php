<?php

namespace LaPoiz\WindBundle\core\graph;

class SpotJsonObject {
	public $spot;
	public $forecast;
	function __construct($spotName) {
		$this->spot=$spotName;
	}

    public function getForecast() {
        return $this->forecast;
    }
    public function setForecast($newForecast) {
        return $this->forecast=$newForecast;
    }
    public function addForecast($newElem) {
        if ($this->forecast==null) {
            $this->forecast=$newElem;
        } else {
            $this->forecast=array_merge($this->forecast, $newElem);
        }
    }
}