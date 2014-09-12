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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mareeURL;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $maree;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $windOrientation;


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
     * Set mareeURL
     *
     * @param string $mareeURL
     * @return SpotParameter
     */
    public function setMareeURL($mareeURL)
    {
        $this->mareeURL = $mareeURL;

        return $this;
    }

    /**
     * Get mareeURL
     *
     * @return string 
     */
    public function getMareeURL()
    {
        return $this->mareeURL;
    }

    /**
     * Set maree
     *
     * @param string $maree
     * @return SpotParameter
     */
    public function setMaree($maree)
    {
        $this->maree = $maree;

        return $this;
    }

    /**
     * Get maree
     *
     * @return string 
     */
    public function getMaree()
    {
        return $this->maree;
    }

    /**
     * Set windOrientation
     *
     * @param string $windOrientation
     * @return SpotParameter
     */
    public function setWindOrientation($windOrientation)
    {
        $this->windOrientation = $windOrientation;

        return $this;
    }

    /**
     * Get windOrientation
     *
     * @return string 
     */
    public function getWindOrientation()
    {
        return $this->windOrientation;
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
