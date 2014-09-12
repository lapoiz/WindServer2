<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_Localisation")
 */
class Localisation 
{
    /**
     * @ORM\GeneratedValue (strategy="IDENTITY")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    
    /**
     * @ORM\Column(type="string",length=255)
     */    
    private $googleMapURL;
    
    
    /**
     * @ORM\Column(type="string",length=255)
     */    
    private $description;
    
    
    /**
     * @ORM\Column(type="string",length=255)
     */    
    private $gpsData;
    
    

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
     * Set googleMapURL
     *
     * @param string $googleMapURL
     */
    public function setGoogleMapURL($googleMapURL)
    {
        $this->googleMapURL = $googleMapURL;
    }

    /**
     * Get googleMapURL
     *
     * @return string $googleMapURL
     */
    public function getGoogleMapURL()
    {
        return $this->googleMapURL;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set gpsData
     *
     * @param string $gpsData
     */
    public function setGpsData($gpsData)
    {
        $this->gpsData = $gpsData;
    }

    /**
     * Get gpsData
     *
     * @return string $gpsData
     */
    public function getGpsData()
    {
        return $this->gpsData;
    }
}
