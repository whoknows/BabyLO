<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Played
 *
 * @ORM\Table(name="baby_played")
 * @ORM\Entity(repositoryClass="Baby\StatBundle\Entity\PlayedRepository")
 */
class Played
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
     * @var integer
     *
     * @ORM\Column(name="id_player", type="integer")
     */
    private $idPlayer;

    /**
     * @var integer
     *
     * @ORM\Column(name="id_game", type="integer")
     */
    private $idGame;

    /**
     * @var integer
     *
     * @ORM\Column(name="team", type="integer")
     */
    private $team;


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
     * Set idPlayer
     *
     * @param integer $idPlayer
     * @return Played
     */
    public function setIdPlayer($idPlayer)
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

    /**
     * Set idGame
     *
     * @param integer $idGame
     * @return Played
     */
    public function setIdGame($idGame)
    {
        $this->idGame = $idGame;

        return $this;
    }

    /**
     * Get idGame
     *
     * @return integer 
     */
    public function getIdGame()
    {
        return $this->idGame;
    }

    /**
     * Set team
     *
     * @param integer $team
     * @return Played
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
}
