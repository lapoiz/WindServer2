<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\PrevisionDateRepository")
 * @ORM\Table(name="windServer_PrevisionDate")
 */
class PrevisionDate
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */    
    private $datePrev;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */    
    private $created;
    
    /**
     * @ORM\ManyToOne(targetEntity="DataWindPrev", inversedBy="listPrevisionDate")
     * @Assert\NotBlank()
     */    
    private $dataWindPrev;
    
    /**
     * @ORM\OneToMany(targetEntity="Prevision", mappedBy="previsionDate", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listPrevision;
    


    public function __construct()
    {
        $this->listPrevision = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set dataWindPrev
     *
     * @param LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     */
    public function setDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev = $dataWindPrev;
    }

    /**
     * Get dataWindPrev
     *
     * @return LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     */
    public function getDataWindPrev()
    {
        return $this->dataWindPrev;
    }

    /**
     * Add listPrevision
     *
     * @param LaPoiz\WindBundle\Entity\Prevision $listPrevision
     */
    public function addListPrevision(\LaPoiz\WindBundle\Entity\Prevision $listPrevision)
    {
        $this->listPrevision[] = $listPrevision;
    }

    /**
     * Get listPrevision
     *
     * @return Doctrine\Common\Collections\Collection $listPrevision
     */
    public function getListPrevision()
    {
        return $this->listPrevision;
    }

    /**
     * Add listPrevision
     *
     * @param LaPoiz\WindBundle\Entity\Prevision $listPrevision
     */
    public function addPrevision(\LaPoiz\WindBundle\Entity\Prevision $listPrevision)
    {
        $this->listPrevision[] = $listPrevision;
    }

    /**
     * Remove listPrevision
     *
     * @param \LaPoiz\WindBundle\Entity\Prevision $listPrevision
     */
    public function removeListPrevision(\LaPoiz\WindBundle\Entity\Prevision $listPrevision)
    {
        $this->listPrevision->removeElement($listPrevision);
    }
}
