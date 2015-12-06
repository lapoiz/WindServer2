<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\RegionRepository")
 * @ORM\Table(name="windServer_Region")
 */
class Region
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
    private $nom;
    
    /**
     * @ORM\Column(type="string",length=255)
     */    
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Spot", mappedBy="region")
     */
    private $spots;


    /**
     * Ordre d'affichage 0->50
     * @ORM\Column(type="integer", options={"unsigned":true, "default":0})
     */
    protected $numDisplay;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->spots = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set nom
     *
     * @param string $nom
     *
     * @return Region
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Region
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     *
     * @return Region
     */
    public function addSpot(\LaPoiz\WindBundle\Entity\Spot $spot)
    {
        $this->spots[] = $spot;

        return $this;
    }

    /**
     * Remove spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     */
    public function removeSpot(\LaPoiz\WindBundle\Entity\Spot $spot)
    {
        $this->spots->removeElement($spot);
    }

    /**
     * Get spots
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSpots()
    {
        return $this->spots;
    }

    /**
     * Set numDisplay
     *
     * @param integer $numDisplay
     *
     * @return Region
     */
    public function setNumDisplay($numDisplay)
    {
        $this->numDisplay = $numDisplay;

        return $this;
    }

    /**
     * Get numDisplay
     *
     * @return integer
     */
    public function getNumDisplay()
    {
        return $this->numDisplay;
    }
}
