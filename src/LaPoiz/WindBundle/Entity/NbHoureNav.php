<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="windServer_NbHoureNav")
 */
class NbHoureNav
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", scale=2, nullable=true)
     */
    private $nbHoure;

    /**
     * @ORM\ManyToOne(targetEntity="WebSite", cascade={"persist"})
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id")
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="NotesDate", inversedBy="nbHoureNav", cascade={"persist"})
     * @ORM\JoinColumn(name="notesDate_id", referencedColumnName="id")
     */
    private $notesDate;



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
     * Set nbHoure
     *
     * @param string $nbHoure
     *
     * @return NbHoureNav
     */
    public function setNbHoure($nbHoure)
    {
        $this->nbHoure = $nbHoure;

        return $this;
    }

    /**
     * Get nbHoure
     *
     * @return string
     */
    public function getNbHoure()
    {
        return $this->nbHoure;
    }

    /**
     * Set website
     *
     * @param \LaPoiz\WindBundle\Entity\WebSite $website
     *
     * @return NbHoureNav
     */
    public function setWebsite(\LaPoiz\WindBundle\Entity\WebSite $website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return \LaPoiz\WindBundle\Entity\WebSite
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set notesDate
     *
     * @param \LaPoiz\WindBundle\Entity\NotesDate $notesDate
     *
     * @return NbHoureNav
     */
    public function setNotesDate(\LaPoiz\WindBundle\Entity\NotesDate $notesDate = null)
    {
        $this->notesDate = $notesDate;

        return $this;
    }

    /**
     * Get notesDate
     *
     * @return \LaPoiz\WindBundle\Entity\NotesDate
     */
    public function getNotesDate()
    {
        return $this->notesDate;
    }
}
