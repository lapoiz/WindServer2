<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_Prevision")
 */
class Prevision
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string",length=255)
     * @Assert\NotBlank()
     */    
    private $orientation;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $meteo;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $precipitation;

    /**
     * @ORM\Column(type="integer")
     */
    private $wind;
    
    /**
     * @ORM\Column(type="time")
     */
    private $time;
    
    
    /**
     * @ORM\ManyToOne(targetEntity="PrevisionDate", inversedBy="listPrevision")
     * @Assert\NotBlank()
     */    
    private $previsionDate;
   


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
     * Set orientation
     *
     * @param string $orientation
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
    }

    /**
     * Get orientation
     *
     * @return string $orientation
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set wind
     *
     * @param integer $wind
     */
    public function setWind($wind)
    {
        $this->wind = $wind;
    }

    /**
     * Get wind
     *
     * @return integer $wind
     */
    public function getWind()
    {
        return $this->wind;
    }

    /**
     * Set time
     *
     * @param time $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Get time
     *
     * @return time $time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set previsionDate
     *
     * @param LaPoiz\WindBundle\Entity\PrevisionDate $previsionDate
     */
    public function setPrevisionDate(\LaPoiz\WindBundle\Entity\PrevisionDate $previsionDate)
    {
        $this->previsionDate = $previsionDate;
    }

    /**
     * Get previsionDate
     *
     * @return LaPoiz\WindBundle\Entity\PrevisionDate $previsionDate
     */
    public function getPrevisionDate()
    {
        return $this->previsionDate;
    }

    /**
     * Set meteo
     *
     * @param string $meteo
     * @return Prevision
     */
    public function setMeteo($meteo)
    {
        $this->meteo = $meteo;

        return $this;
    }

    /**
     * Get meteo
     *
     * @return string 
     */
    public function getMeteo()
    {
        return $this->meteo;
    }

    /**
     * Set precipitation
     *
     * @param string $precipitation
     * @return Prevision
     */
    public function setPrecipitation($precipitation)
    {
        $this->precipitation = $precipitation;

        return $this;
    }

    /**
     * Get precipitation
     *
     * @return string 
     */
    public function getPrecipitation()
    {
        return $this->precipitation;
    }
}
