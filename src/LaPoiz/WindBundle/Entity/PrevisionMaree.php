<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_PrevisionMaree")
 */
class PrevisionMaree
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="time")
     */
    private $time;


    /**
     * @ORM\ManyToOne(targetEntity="MareeDate", inversedBy="listPrevision")
     * @ORM\JoinColumn(name="mareeDate_id", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $mareeDate;


    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private $hauteur;



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
     * Set time
     *
     * @param \DateTime $time
     * @return PrevisionMaree
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return \DateTime 
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set hauteur
     *
     * @param string $hauteur
     * @return PrevisionMaree
     */
    public function setHauteur($hauteur)
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    /**
     * Get hauteur
     *
     * @return string 
     */
    public function getHauteur()
    {
        return $this->hauteur;
    }

    /**
     * Set mareeDate
     *
     * @param \LaPoiz\WindBundle\Entity\MareeDate $mareeDate
     * @return PrevisionMaree
     */
    public function setMareeDate(\LaPoiz\WindBundle\Entity\MareeDate $mareeDate = null)
    {
        $this->mareeDate = $mareeDate;

        return $this;
    }

    /**
     * Get mareeDate
     *
     * @return \LaPoiz\WindBundle\Entity\MareeDate 
     */
    public function getMareeDate()
    {
        return $this->mareeDate;
    }
}
