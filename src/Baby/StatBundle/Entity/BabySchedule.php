<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BabySchedule
 *
 * @ORM\Table(name="baby_schedule")
 * @ORM\Entity(repositoryClass="Baby\StatBundle\Entity\BabyScheduleRepository")
 */
class BabySchedule
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="creneau", type="string", length=5)
     */
    private $creneau;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="Baby\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_player", referencedColumnName="id")
     * })
     */
    private $idPlayer;

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
     * Set date
     *
     * @param \DateTime $date
     * @return BabySchedule
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set creneau
     *
     * @param string $creneau
     * @return BabySchedule
     */
    public function setCreneau($creneau)
    {
        $this->creneau = $creneau;

        return $this;
    }

    /**
     * Get creneau
     *
     * @return string
     */
    public function getCreneau()
    {
        return $this->creneau;
    }

    /**
     * Set idPlayer
     *
     * @param \Baby\UserBundle\Entity\User $idPlayer
     * @return BabyPlayed
     */
    public function setIdPlayer(\Baby\UserBundle\Entity\User $idPlayer = null)
    {
        $this->idPlayer = $idPlayer;

        return $this;
    }

    /**
     * Get idPlayer
     *
     * @return integer
     */
    public function getIdPlayer()
    {
        return $this->idPlayer;
    }
}
