<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_Balise")
 */
class Balise 
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
     * @ORM\Column(type="string",length=255,nullable=true)
     */    
    private $description;
    
    /**
     * @ORM\Column(type="string",length=255)
     */    
    private $url;
    
    
    /**
    * @ORM\OneToOne(targetEntity="Spot", cascade={"persist", "remove"} )
    * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
    */
    private $spot;
    
    
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
     * Set nom
     *
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get nom
     *
     * @return string $nom
     */
    public function getNom()
    {
        return $this->nom;
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
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
    * Set spot
    *
    * @param LaPoiz\WindBundle\Entity\Spot $spot
    */
    public function setSpot(\LaPoiz\WindBundle\Entity\Spot $spot)
    {
    $this->spot = $spot;
    }
    
    /**
    	* Get spot
    	*
    	* @return LaPoiz\WindBundle\Entity\Spot $spot
    	*/
    	public function getSpot()
    	{
    	return $this->spot;
    }
}
