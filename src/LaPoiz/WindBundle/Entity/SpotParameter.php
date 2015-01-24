<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_SpotParameter")
 */
class SpotParameter
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
        /**
     * @ORM\OneToOne(targetEntity="Spot", cascade={"persist", "remove"} )
     * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
     */
    private $spot;

    /**
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $thermique;





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
     * Set thermique
     *
     * @param string $thermique
     * @return SpotParameter
     */
    public function setThermique($thermique)
    {
        $this->thermique = $thermique;

        return $this;
    }

    /**
     * Get thermique
     *
     * @return string 
     */
    public function getThermique()
    {
        return $this->thermique;
    }

    /**
     * Set spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     * @return SpotParameter
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
