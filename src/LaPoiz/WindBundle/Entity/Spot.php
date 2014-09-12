<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\SpotRepository")
 * @ORM\Table(name="windServer_Spot")
 */
class Spot 
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
     * @ORM\Column(type="boolean",nullable=true)
     */    
    private $isKitePractice;
    
    /**
     * @ORM\Column(type="boolean",nullable=true)
     */    
    private $isWindsurfPractice;
    
    
    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */    
    private $googleMapURL;
    
    
    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */    
    private $localisationDescription;
    
    
    /**
     * @ORM\Column(type="decimal",nullable=true,scale=7)
     */    
    private $gpsLat; 
    
    /**
    * @ORM\Column(type="decimal",nullable=true,scale=7)
    */
    private $gpsLong;
    
    
    /**
     * @ORM\OneToOne(targetEntity="Balise", cascade={"persist", "remove"} )
     * @ORM\JoinColumn(name="balise_id", referencedColumnName="id")
     */    
    private $balise;
    
    /**
     * @ORM\OneToMany(targetEntity="DataWindPrev", mappedBy="spot", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $dataWindPrev;      
    
    
    /**
     * @ORM\OneToOne(targetEntity="SpotParameter", cascade={"remove", "persist"})
     * @ORM\JoinColumn(name="parameter_id", referencedColumnName="id")
     */
    private $parameter;


    /**
     * @ORM\OneToMany(targetEntity="MareeDate", mappedBy="spot", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $listMareeDate;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataWindPrev = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listMareeDate = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Spot
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
     * @return Spot
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
     * Set isKitePractice
     *
     * @param boolean $isKitePractice
     * @return Spot
     */
    public function setIsKitePractice($isKitePractice)
    {
        $this->isKitePractice = $isKitePractice;

        return $this;
    }

    /**
     * Get isKitePractice
     *
     * @return boolean 
     */
    public function getIsKitePractice()
    {
        return $this->isKitePractice;
    }

    /**
     * Set isWindsurfPractice
     *
     * @param boolean $isWindsurfPractice
     * @return Spot
     */
    public function setIsWindsurfPractice($isWindsurfPractice)
    {
        $this->isWindsurfPractice = $isWindsurfPractice;

        return $this;
    }

    /**
     * Get isWindsurfPractice
     *
     * @return boolean 
     */
    public function getIsWindsurfPractice()
    {
        return $this->isWindsurfPractice;
    }

    /**
     * Set googleMapURL
     *
     * @param string $googleMapURL
     * @return Spot
     */
    public function setGoogleMapURL($googleMapURL)
    {
        $this->googleMapURL = $googleMapURL;

        return $this;
    }

    /**
     * Get googleMapURL
     *
     * @return string 
     */
    public function getGoogleMapURL()
    {
        return $this->googleMapURL;
    }

    /**
     * Set localisationDescription
     *
     * @param string $localisationDescription
     * @return Spot
     */
    public function setLocalisationDescription($localisationDescription)
    {
        $this->localisationDescription = $localisationDescription;

        return $this;
    }

    /**
     * Get localisationDescription
     *
     * @return string 
     */
    public function getLocalisationDescription()
    {
        return $this->localisationDescription;
    }

    /**
     * Set gpsLat
     *
     * @param string $gpsLat
     * @return Spot
     */
    public function setGpsLat($gpsLat)
    {
        $this->gpsLat = $gpsLat;

        return $this;
    }

    /**
     * Get gpsLat
     *
     * @return string 
     */
    public function getGpsLat()
    {
        return $this->gpsLat;
    }

    /**
     * Set gpsLong
     *
     * @param string $gpsLong
     * @return Spot
     */
    public function setGpsLong($gpsLong)
    {
        $this->gpsLong = $gpsLong;

        return $this;
    }

    /**
     * Get gpsLong
     *
     * @return string 
     */
    public function getGpsLong()
    {
        return $this->gpsLong;
    }

    /**
     * Set balise
     *
     * @param \LaPoiz\WindBundle\Entity\Balise $balise
     * @return Spot
     */
    public function setBalise(\LaPoiz\WindBundle\Entity\Balise $balise = null)
    {
        $this->balise = $balise;

        return $this;
    }

    /**
     * Get balise
     *
     * @return \LaPoiz\WindBundle\Entity\Balise 
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Add dataWindPrev
     *
     * @param \LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     * @return Spot
     */
    public function addDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev[] = $dataWindPrev;

        return $this;
    }

    /**
     * Remove dataWindPrev
     *
     * @param \LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     */
    public function removeDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev->removeElement($dataWindPrev);
    }

    /**
     * Get dataWindPrev
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDataWindPrev()
    {
        return $this->dataWindPrev;
    }

    /**
     * Set parameter
     *
     * @param \LaPoiz\WindBundle\Entity\SpotParameter $parameter
     * @return Spot
     */
    public function setParameter(\LaPoiz\WindBundle\Entity\SpotParameter $parameter = null)
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Get parameter
     *
     * @return \LaPoiz\WindBundle\Entity\SpotParameter 
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * Add listMareeDate
     *
     * @param \LaPoiz\WindBundle\Entity\MareeDate $listMareeDate
     * @return Spot
     */
    public function addListMareeDate(\LaPoiz\WindBundle\Entity\MareeDate $listMareeDate)
    {
        $this->listMareeDate[] = $listMareeDate;

        return $this;
    }

    /**
     * Remove listMareeDate
     *
     * @param \LaPoiz\WindBundle\Entity\MareeDate $listMareeDate
     */
    public function removeListMareeDate(\LaPoiz\WindBundle\Entity\MareeDate $listMareeDate)
    {
        $this->listMareeDate->removeElement($listMareeDate);
    }

    /**
     * Get listMareeDate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getListMareeDate()
    {
        return $this->listMareeDate;
    }
}
