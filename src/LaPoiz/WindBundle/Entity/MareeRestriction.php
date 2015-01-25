<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_MareeRestriction")
 */
class MareeRestriction
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */    
    private $hauteurMax;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hauteurMin;

    /**
     * @ORM\Column(type="string",length=20, nullable=true)
     * "OK", "KO", "warn"
     */
    private $state;

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
     * Set hauteurMax
     *
     * @param integer $hauteurMax
     * @return MareeRestriction
     */
    public function setHauteurMax($hauteurMax)
    {
        $this->hauteurMax = $hauteurMax;

        return $this;
    }

    /**
     * Get hauteurMax
     *
     * @return integer 
     */
    public function getHauteurMax()
    {
        return $this->hauteurMax;
    }

    /**
     * Set hauteurMin
     *
     * @param integer $hauteurMin
     * @return MareeRestriction
     */
    public function setHauteurMin($hauteurMin)
    {
        $this->hauteurMin = $hauteurMin;

        return $this;
    }

    /**
     * Get hauteurMin
     *
     * @return integer 
     */
    public function getHauteurMin()
    {
        return $this->hauteurMin;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return MareeRestriction
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
     * @return MareeRestriction
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
