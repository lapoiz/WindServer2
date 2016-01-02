<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\DataWindPrevRepository")
 * @ORM\Table(name="windServer_DataWindPrev")
 */
class DataWindPrev 
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
     * @ORM\Column(type="datetime")
     */    
    private $created;
    
    /**
     * @ORM\OneToMany(targetEntity="PrevisionDate", mappedBy="dataWindPrev", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listPrevisionDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="WebSite", inversedBy="dataWindPrev")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id")
     */
    private $website;
    
    /**
     * @ORM\ManyToOne(targetEntity="Spot", inversedBy="dataWindPrev")
     * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
     */
    private $spot;


    /**
     * @ORM\Column(type="datetime")
     */
    private $lastUpdate;
    

    public function __construct()
    {
        $this->listPrevisionDate = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \Datetime();
        $this->lastUpdate = new \Datetime();
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
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add listPrevisionDate
     *
     * @param LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate
     */
    public function addListPrevisionDate(\LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate)
    {
        $this->listPrevisionDate[] = $listPrevisionDate;
    }

    /**
     * Get listPrevisionDate
     *
     * @return Doctrine\Common\Collections\Collection $listPrevisionDate
     */
    public function getListPrevisionDate()
    {
        return $this->listPrevisionDate;
    }

    /**
     * Set website
     *
     * @param LaPoiz\WindBundle\Entity\WebSite $website
     */
    public function setWebsite(\LaPoiz\WindBundle\Entity\WebSite $website)
    {
        $this->website = $website;
    }

    /**
     * Get website
     *
     * @return LaPoiz\WindBundle\Entity\WebSite $website
     */
    public function getWebsite()
    {
        return $this->website;
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

    /**
     * Add listPrevisionDate
     *
     * @param LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate
     */
    public function addPrevisionDate(\LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate)
    {
        $this->listPrevisionDate[] = $listPrevisionDate;
    }

    /**
     * Remove listPrevisionDate
     *
     * @param \LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate
     */
    public function removeListPrevisionDate(\LaPoiz\WindBundle\Entity\PrevisionDate $listPrevisionDate)
    {
        $this->listPrevisionDate->removeElement($listPrevisionDate);
    }

    /**
     * Set lastUpdate
     *
     * @param \DateTime $lastUpdate
     *
     * @return DataWindPrev
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate
     *
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
