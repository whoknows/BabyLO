<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BabyPlayed
 *
 * @ORM\Table(name="baby_played", indexes={@ORM\Index(name="id_game", columns={"id_game"}), @ORM\Index(name="baby_played_ibfk_1", columns={"id_player"})})
 * @ORM\Entity
 */
class BabyPlayed
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
     * @var integer
     *
     * @ORM\Column(name="team", type="integer", nullable=false)
     */
    private $team;

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
     * @var \BabyGame
     *
     * @ORM\ManyToOne(targetEntity="BabyGame")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_game", referencedColumnName="id")
     * })
     */
    private $idGame;

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
     * Set team
     *
     * @param integer $team
     * @return BabyPlayed
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return integer
     */
    public function getTeam()
    {
        return $this->team;
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
     * @return \Baby\UserBundle\Entity\User
     */
    public function getIdPlayer()
    {
        return $this->idPlayer;
    }

    /**
     * Set idGame
     *
     * @param \Baby\StatBundle\Entity\BabyGame $idGame
     * @return BabyPlayed
     */
    public function setIdGame(\Baby\StatBundle\Entity\BabyGame $idGame = null)
    {
        $this->idGame = $idGame;

        return $this;
    }

    /**
     * Get idGame
     *
     * @return \Baby\StatBundle\Entity\BabyGame
     */
    public function getIdGame()
    {
        return $this->idGame;
    }
}
