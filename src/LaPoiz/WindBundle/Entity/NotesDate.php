<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
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
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteWind;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $wind;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteOrientationWind;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timeOrientationOK;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteTemp;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $temperature;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteWaterTemp;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $waterTemperature;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteMaree;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timeMareeOK;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $notePrecipitation;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timePrecipitation;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $noteMeteo;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $timeMeteo;

    /**
     * @ORM\ManyToOne(targetEntity="Spot", inversedBy="mareeRestriction", cascade={"persist"})
     * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
     */
    private $spot;   


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
     * Set noteWind
     *
     * @param string $noteWind
     * @return NotesDate
     */
    public function setNoteWind($noteWind)
    {
        $this->noteWind = $noteWind;

        return $this;
    }

    /**
     * Get noteWind
     *
     * @return string 
     */
    public function getNoteWind()
    {
        return $this->noteWind;
    }

    /**
     * Set wind
     *
     * @param string $wind
     * @return NotesDate
     */
    public function setWind($wind)
    {
        $this->wind = $wind;

        return $this;
    }

    /**
     * Get wind
     *
     * @return string 
     */
    public function getWind()
    {
        return $this->wind;
    }

    /**
     * Set noteOrientationWind
     *
     * @param string $noteOrientationWind
     * @return NotesDate
     */
    public function setNoteOrientationWind($noteOrientationWind)
    {
        $this->noteOrientationWind = $noteOrientationWind;

        return $this;
    }

    /**
     * Get noteOrientationWind
     *
     * @return string 
     */
    public function getNoteOrientationWind()
    {
        return $this->noteOrientationWind;
    }

    /**
     * Set timeOrientationOK
     *
     * @param \DateTime $timeOrientationOK
     * @return NotesDate
     */
    public function setTimeOrientationOK($timeOrientationOK)
    {
        $this->timeOrientationOK = $timeOrientationOK;

        return $this;
    }

    /**
     * Get timeOrientationOK
     *
     * @return \DateTime 
     */
    public function getTimeOrientationOK()
    {
        return $this->timeOrientationOK;
    }

    /**
     * Set noteTemp
     *
     * @param string $noteTemp
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
     * Set temperature
     *
     * @param string $temperature
     * @return NotesDate
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    /**
     * Get temperature
     *
     * @return string 
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Set noteWaterTemp
     *
     * @param string $noteWaterTemp
     * @return NotesDate
     */
    public function setNoteWaterTemp($noteWaterTemp)
    {
        $this->noteWaterTemp = $noteWaterTemp;

        return $this;
    }

    /**
     * Get noteWaterTemp
     *
     * @return string 
     */
    public function getNoteWaterTemp()
    {
        return $this->noteWaterTemp;
    }

    /**
     * Set waterTemperature
     *
     * @param string $waterTemperature
     * @return NotesDate
     */
    public function setWaterTemperature($waterTemperature)
    {
        $this->waterTemperature = $waterTemperature;

        return $this;
    }

    /**
     * Get waterTemperature
     *
     * @return string 
     */
    public function getWaterTemperature()
    {
        return $this->waterTemperature;
    }

    /**
     * Set noteMaree
     *
     * @param string $noteMaree
     * @return NotesDate
     */
    public function setNoteMaree($noteMaree)
    {
        $this->noteMaree = $noteMaree;

        return $this;
    }

    /**
     * Get noteMaree
     *
     * @return string 
     */
    public function getNoteMaree()
    {
        return $this->noteMaree;
    }

    /**
     * Set timeMareeOK
     *
     * @param \DateTime $timeMareeOK
     * @return NotesDate
     */
    public function setTimeMareeOK($timeMareeOK)
    {
        $this->timeMareeOK = $timeMareeOK;

        return $this;
    }

    /**
     * Get timeMareeOK
     *
     * @return \DateTime 
     */
    public function getTimeMareeOK()
    {
        return $this->timeMareeOK;
    }

    /**
     * Set notePrecipitation
     *
     * @param string $notePrecipitation
     * @return NotesDate
     */
    public function setNotePrecipitation($notePrecipitation)
    {
        $this->notePrecipitation = $notePrecipitation;

        return $this;
    }

    /**
     * Get notePrecipitation
     *
     * @return string 
     */
    public function getNotePrecipitation()
    {
        return $this->notePrecipitation;
    }

    /**
     * Set timePrecipitation
     *
     * @param \DateTime $timePrecipitation
     * @return NotesDate
     */
    public function setTimePrecipitation($timePrecipitation)
    {
        $this->timePrecipitation = $timePrecipitation;

        return $this;
    }

    /**
     * Get timePrecipitation
     *
     * @return \DateTime 
     */
    public function getTimePrecipitation()
    {
        return $this->timePrecipitation;
    }

    /**
     * Set noteMeteo
     *
     * @param string $noteMeteo
     * @return NotesDate
     */
    public function setNoteMeteo($noteMeteo)
    {
        $this->noteMeteo = $noteMeteo;

        return $this;
    }

    /**
     * Get noteMeteo
     *
     * @return string 
     */
    public function getNoteMeteo()
    {
        return $this->noteMeteo;
    }

    /**
     * Set timeMeteo
     *
     * @param \DateTime $timeMeteo
     * @return NotesDate
     */
    public function setTimeMeteo($timeMeteo)
    {
        $this->timeMeteo = $timeMeteo;

        return $this;
    }

    /**
     * Get timeMeteo
     *
     * @return \DateTime 
     */
    public function getTimeMeteo()
    {
        return $this->timeMeteo;
    }

    /**
     * Set spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
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
}
