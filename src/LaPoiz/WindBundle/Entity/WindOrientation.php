<?php
namespace LaPoiz\WindBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_WindOrientation")
 */
class WindOrientation
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string",length=20)
     * @Assert\NotBlank()
     * "nord", "nord-nord-est",...
     */    
    private $orientation;

    /**
     * @ORM\Column(type="decimal", scale=1)
     * @Assert\NotBlank()
     * 0, 22.5, ...
     */
    private $orientationDeg;

    /**
     * @ORM\Column(type="string",length=20, nullable=true)
     * "OK", "KO", "warn"
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity="Spot", inversedBy="windOrientation")
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
     * Set orientation
     *
     * @param string $orientation
     * @return WindOrientation
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;
        $this->orientationDeg=WebsiteGetData::transformeOrientationDeg($orientation);

        return $this;
    }

    /**
     * Get orientation
     *
     * @return string 
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return WindOrientation
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     * @return WindOrientation
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
     * Set orientationDeg
     *
     * @param string $orientationDeg
     *
     * @return WindOrientation
     */
    public function setOrientationDeg($orientationDeg)
    {
        $this->orientationDeg = $orientationDeg;
        $this->orientation=WebsiteGetData::transformeOrientationDegToNom($orientationDeg);

        return $this;
    }

    /**
     * Get orientationDeg
     *
     * @return string
     */
    public function getOrientationDeg()
    {
        return $this->orientationDeg;
    }
}
