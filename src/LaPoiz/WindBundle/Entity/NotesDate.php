<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\NotesDateRepository")
 * @ORM\Table(name="windServer_NotesDate")
 */
class NotesDate
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
     * @ORM\OneToMany(targetEntity="NbHoureNav", mappedBy="notesDate", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $nbHoureNav;


    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $nbHoureNavCalc;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $tempMax;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $tempMin;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $tempWater;

    /**
     * @ORM\Column(type="string",length=10, nullable=true)
     */
    private $meteoBest;

    /**
     * @ORM\Column(type="string",length=10, nullable=true)
     */
    private $meteoWorst;

    /**
     * @ORM\ManyToOne(targetEntity="Spot", inversedBy="notesDate", cascade={"persist"})
     * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
     */
    private $spot;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nbHoureNav = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datePrev
     *
     * @param \DateTime $datePrev
     *
     * @return NotesDate
     */
    public function setDatePrev($datePrev)
    {
        $this->datePrev = $datePrev;

        return $this;
    }

    /**
     * Get datePrev
     *
     * @return \DateTime
     */
    public function getDatePrev()
    {
        return $this->datePrev;
    }

    /**
     * Set noteTemp
     *
     * @param string $noteTemp
     *
     * @return NotesDate
     */
    public function setNoteTemp($noteTemp)
    {
        $this->noteTemp = $noteTemp;

        return $this;
    }

    /**
     * Get noteTemp
     *
     * @return string
     */
    public function getNoteTemp()
    {
        return $this->noteTemp;
    }

    /**
     * Add nbHoureNav
     *
     * @param \LaPoiz\WindBundle\Entity\NbHoureNav $nbHoureNav
     *
     * @return NotesDate
     */
    public function addNbHoureNav(\LaPoiz\WindBundle\Entity\NbHoureNav $nbHoureNav)
    {
        $this->nbHoureNav[] = $nbHoureNav;

        return $this;
    }

    /**
     * Remove nbHoureNav
     *
     * @param \LaPoiz\WindBundle\Entity\NbHoureNav $nbHoureNav
     */
    public function removeNbHoureNav(\LaPoiz\WindBundle\Entity\NbHoureNav $nbHoureNav)
    {
        $this->nbHoureNav->removeElement($nbHoureNav);
    }

    /**
     * Get nbHoureNav
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNbHoureNav()
    {
        return $this->nbHoureNav;
    }

    /**
     * Set spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     *
     * @return NotesDate
     */
    public function setSpot(\LaPoiz\WindBundle\Entity\Spot $spot = null)
    {
        $this->spot = $spot;

        return $this;
    }

    /**
     * Get spot
     *
     * @return \LaPoiz\WindBundle\Entity\Spot
     */
    public function getSpot()
    {
        return $this->spot;
    }

    /**
     * Set tempMax
     *
     * @param string $tempMax
     *
     * @return NotesDate
     */
    public function setTempMax($tempMax)
    {
        $this->tempMax = $tempMax;

        return $this;
    }

    /**
     * Get tempMax
     *
     * @return string
     */
    public function getTempMax()
    {
        return $this->tempMax;
    }

    /**
     * Set tempMin
     *
     * @param string $tempMin
     *
     * @return NotesDate
     */
    public function setTempMin($tempMin)
    {
        $this->tempMin = $tempMin;

        return $this;
    }

    /**
     * Get tempMin
     *
     * @return string
     */
    public function getTempMin()
    {
        return $this->tempMin;
    }

    /**
     * Set meteoBest
     *
     * @param string $meteoBest
     *
     * @return NotesDate
     */
    public function setMeteoBest($meteoBest)
    {
        $this->meteoBest = $meteoBest;

        return $this;
    }

    /**
     * Get meteoBest
     *
     * @return string
     */
    public function getMeteoBest()
    {
        return $this->meteoBest;
    }

    /**
     * Set meteoWorst
     *
     * @param string $meteoWorst
     *
     * @return NotesDate
     */
    public function setMeteoWorst($meteoWorst)
    {
        $this->meteoWorst = $meteoWorst;

        return $this;
    }

    /**
     * Get meteoWorst
     *
     * @return string
     */
    public function getMeteoWorst()
    {
        return $this->meteoWorst;
    }

    /**
     * Set tempWater
     *
     * @param string $tempWater
     *
     * @return NotesDate
     */
    public function setTempWater($tempWater)
    {
        $this->tempWater = $tempWater;

        return $this;
    }

    /**
     * Get tempWater
     *
     * @return string
     */
    public function getTempWater()
    {
        return $this->tempWater;
    }


    /**
     * Set nbHoureNavCalc
     *
     * @param string $nbHoureNavCalc
     *
     * @return NotesDate
     */
    public function setNbHoureNavCalc($nbHoureNavCalc)
    {
        $this->nbHoureNavCalc = $nbHoureNavCalc;

        return $this;
    }

    /**
     * Get nbHoureNavCalc
     *
     * @return string
     */
    public function getNbHoureNavCalc()
    {
        return $this->nbHoureNavCalc;
    }
}
