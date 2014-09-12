<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_WebSite")
 */
class WebSite 
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
    private $url;
    
    /**
     * @ORM\Column(type="string",length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */    
    private $nom;
    
    /**
     * @ORM\OneToMany(targetEntity="DataWindPrev", mappedBy="website", cascade={"remove"}, orphanRemoval=true)
     */
    private $dataWindPrev;
    
    
    /**
     * @ORM\Column(type="string",length=255)
     * @Assert\NotBlank()
     * * @Assert\Length(min=3)
     */    
    private $logo;

    public function __construct()
    {
        $this->dataWindPrev = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
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
     * Set logo
     *
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Get logo
     *
     * @return string $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Add dataWindPrev
     *
     * @param LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     */
    public function addDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev[] = $dataWindPrev;
        $dataWindPrev->setWebsite($this);
    }

    /**
     * Get dataWindPrev
     *
     * @return Doctrine\Common\Collections\Collection $dataWindPrev
     */
    public function getDataWindPrev()
    {
        return $this->dataWindPrev;
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
}
