<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// A EFFACER ?


/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_DataWindSpot")
 */
class DataWindSpot
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
     * @Assert\Length(min=3)
     */    
    private $orientation;

    /**
     * @ORM\Column(type="integer")
     */
    private $wind;
    
    /**
     * @ORM\Column(type="time")
     */
    private $time;
    
    
    /**
    * @ORM\ManyToOne(targetEntity="DataSpot")
    * @ORM\JoinColumn(name="dataSpot_id", referencedColumnName="id")
    */
    private $dataSpot;

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
     * Set dataSpot
     *
     * @param LaPoiz\WindBundle\Entity\DataSpot $dataSpot
     */
    public function setDataSpot(\LaPoiz\WindBundle\Entity\DataSpot $dataSpot)
    {
        $this->dataSpot = $dataSpot;
    }

    /**
     * Get dataSpot
     *
     * @return LaPoiz\WindBundle\Entity\DataSpot 
     */
    public function getDataSpot()
    {
        return $this->dataSpot;
    }
}
