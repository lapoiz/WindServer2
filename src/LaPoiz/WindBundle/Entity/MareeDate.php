<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\MareeDateRepository")
 * @ORM\Table(name="windServer_MareeDate")
 */
class MareeDate
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $datePrev;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Spot", inversedBy="listMareeDate")
     * @ORM\JoinColumn(name="spot_id", referencedColumnName="id")
     */
    private $spot;

    /**
     * @ORM\OneToMany(targetEntity="PrevisionMaree", mappedBy="mareeDate", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listPrevision;




    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listPrevision = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created = new \Datetime();
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
     * Set datePrev
     *
     * @param \DateTime $datePrev
     * @return MareeDate
     */
    public function setDatePrev($datePrev)
    {
        $this->datePrev = $datePrev;

        return $this;
    }

    /**
     * Get datePrev
     *
     * @return \DateTime 
     */
    public function getDatePrev()
    {
        return $this->datePrev;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return MareeDate
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set spot
     *
     * @param \LaPoiz\WindBundle\Entity\Spot $spot
     * @return MareeDate
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
     * Add listPrevision
     *
     * @param \LaPoiz\WindBundle\Entity\PrevisionMaree $listPrevision
     * @return MareeDate
     */
    public function addListPrevision(\LaPoiz\WindBundle\Entity\PrevisionMaree $listPrevision)
    {
        $this->listPrevision[] = $listPrevision;

        return $this;
    }

    /**
     * Remove listPrevision
     *
     * @param \LaPoiz\WindBundle\Entity\PrevisionMaree $listPrevision
     */
    public function removeListPrevision(\LaPoiz\WindBundle\Entity\PrevisionMaree $listPrevision)
    {
        $this->listPrevision->removeElement($listPrevision);
    }

    /**
     * Get listPrevision
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getListPrevision()
    {
        return $this->listPrevision;
    }
}
