<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


// A EFFACER ?

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\DataSpotRepository")
 * @ORM\Table(name="windServer_DataSpot")
 */
class DataSpot 
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */    
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="Balise")
     * @ORM\JoinColumn(name="balise_id", referencedColumnName="id")
     */
    private $balise;
    
    /**
     * @ORM\OneToMany(targetEntity="DataWindSpot", mappedBy="dataSpot", cascade={"remove"})
     */
    private $listDataWindSpot;


    
    public function __construct()
    {
        $this->listDataWindSpot = new \Doctrine\Common\Collections\ArrayCollection();
//        $this->listMaree = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \Datetime();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datePrev
     *
     * @param date $datePrev
     */
    public function setDatePrev($datePrev)
    {
        $this->datePrev = $datePrev;
    }

    /**
     * Get datePrev
     *
     * @return date $datePrev
     */
    public function getDatePrev()
    {
        return $this->datePrev;
    }

    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set windAverage
     *
     * @param float $windAverage
     */
    public function setWindAverage($windAverage)
    {
        $this->windAverage = $windAverage;
    }

    /**
     * Get windAverage
     *
     * @return float $windAverage
     */
    public function getWindAverage()
    {
        return $this->windAverage;
    }

    /**
     * Set windGauss
     *
     * @param float $windGauss
     */
    public function setWindGauss($windGauss)
    {
        $this->windGauss = $windGauss;
    }

    /**
     * Get windGauss
     *
     * @return float $windGauss
     */
    public function getWindGauss()
    {
        return $this->windGauss;
    }

    /**
     * Set windMax
     *
     * @param float $windMax
     */
    public function setWindMax($windMax)
    {
        $this->windMax = $windMax;
    }

    /**
     * Get windMax
     *
     * @return float $windMax
     */
    public function getWindMax()
    {
        return $this->windMax;
    }

    /**
     * Set windMin
     *
     * @param float $windMin
     */
    public function setWindMin($windMin)
    {
        $this->windMin = $windMin;
    }

    /**
     * Get windMin
     *
     * @return float $windMin
     */
    public function getWindMin()
    {
        return $this->windMin;
    }

    /**
     * Set windMiddle
     *
     * @param float $windMiddle
     */
    public function setWindMiddle($windMiddle)
    {
        $this->windMiddle = $windMiddle;
    }

    /**
     * Get windMiddle
     *
     * @return float $windMiddle
     */
    public function getWindMiddle()
    {
        return $this->windMiddle;
    }

    /**
     * Add listDataWindSpot
     *
     * @param LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot
     */
    public function addListDataWindSpot(\LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot)
    {
        $this->listDataWindSpot[] = $listDataWindSpot;
    }

    /**
     * Get listDataWindSpot
     *
     * @return Doctrine\Common\Collections\Collection $listDataWindSpot
     */
    public function getListDataWindSpot()
    {
        return $this->listDataWindSpot;
    }

    /**
     * Set spot
     *
     * @param LaPoiz\WindBundle\Entity\Spot $spot
     */
    public function setSpot(\LaPoiz\WindBundle\Entity\Spot $spot)
    {
        $this->spot = $spot;
    }

    /**
     * Get spot
     *
     * @return LaPoiz\WindBundle\Entity\Spot $spot
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * Set balise
     *
     * @param LaPoiz\WindBundle\Entity\Balise $balise
     */
    public function setBalise(\LaPoiz\WindBundle\Entity\Balise $balise)
    {
        $this->balise = $balise;
    }

    /**
     * Get balise
     *
     * @return LaPoiz\WindBundle\Entity\Balise $balise
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Add listDataWindSpot
     *
     * @param LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot
     */
    public function addDataWindSpot(\LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot)
    {
        $this->listDataWindSpot[] = $listDataWindSpot;
    }

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Remove listDataWindSpot
     *
     * @param \LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot
     */
    public function removeListDataWindSpot(\LaPoiz\WindBundle\Entity\DataWindSpot $listDataWindSpot)
    {
        $this->listDataWindSpot->removeElement($listDataWindSpot);
    }

}
