<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BabyGame
 *
 * @ORM\Table(name="baby_game")
 * @ORM\Entity(repositoryClass="Baby\StatBundle\Entity\BabyGameRepository")
 */
class BabyGame
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="score_team1", type="integer", nullable=false)
     */
    private $scoreTeam1;

    /**
     * @var integer
     *
     * @ORM\Column(name="score_team2", type="integer", nullable=false)
     */
    private $scoreTeam2;



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
     * @return BabyGame
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
     * Set scoreTeam1
     *
     * @param integer $scoreTeam1
     * @return BabyGame
     */
    public function setScoreTeam1($scoreTeam1)
    {
        $this->scoreTeam1 = $scoreTeam1;

        return $this;
    }

    /**
     * Get scoreTeam1
     *
     * @return integer
     */
    public function getScoreTeam1()
    {
        return $this->scoreTeam1;
    }

    /**
     * Set scoreTeam2
     *
     * @param integer $scoreTeam2
     * @return BabyGame
     */
    public function setScoreTeam2($scoreTeam2)
    {
        $this->scoreTeam2 = $scoreTeam2;

        return $this;
    }

    /**
     * Get scoreTeam2
     *
     * @return integer
     */
    public function getScoreTeam2()
    {
        return $this->scoreTeam2;
    }
}
