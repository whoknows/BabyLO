<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BabyStats
 *
 * @ORM\Table(name="baby_stats", indexes={@ORM\Index(name="player_id", columns={"player_id"})})
 * @ORM\Entity
 */
class BabyStats
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
     * @ORM\Column(name="nb_games", type="integer", nullable=false)
     */
    private $nbGames;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_win", type="integer", nullable=false)
     */
    private $nbWin;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_lose", type="integer", nullable=false)
     */
    private $nbLose;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_win_fanny", type="integer", nullable=false)
     */
    private $nbWinFanny;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_lose_fanny", type="integer", nullable=false)
     */
    private $nbLoseFanny;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_but_scored", type="integer", nullable=false)
     */
    private $nbButScored;

    /**
     * @var integer
     *
     * @ORM\Column(name="nb_but_taken", type="integer", nullable=false)
     */
    private $nbButTaken;

    /**
     * @var string
     *
     * @ORM\Column(name="best_oponent", type="string", length=50, nullable=false)
     */
    private $bestOponent;

    /**
     * @var string
     *
     * @ORM\Column(name="worst_oponent", type="string", length=50, nullable=false)
     */
    private $worstOponent;

    /**
     * @var string
     *
     * @ORM\Column(name="best_mate", type="string", length=50, nullable=false)
     */
    private $bestMate;

    /**
     * @var string
     *
     * @ORM\Column(name="worst_mate", type="string", length=50, nullable=false)
     */
    private $worstMate;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="player_id", referencedColumnName="id")
     * })
     */
    private $player;



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
     * Set nbGames
     *
     * @param integer $nbGames
     * @return BabyStats
     */
    public function setNbGames($nbGames)
    {
        $this->nbGames = $nbGames;

        return $this;
    }

    /**
     * Get nbGames
     *
     * @return integer 
     */
    public function getNbGames()
    {
        return $this->nbGames;
    }

    /**
     * Set nbWin
     *
     * @param integer $nbWin
     * @return BabyStats
     */
    public function setNbWin($nbWin)
    {
        $this->nbWin = $nbWin;

        return $this;
    }

    /**
     * Get nbWin
     *
     * @return integer 
     */
    public function getNbWin()
    {
        return $this->nbWin;
    }

    /**
     * Set nbLose
     *
     * @param integer $nbLose
     * @return BabyStats
     */
    public function setNbLose($nbLose)
    {
        $this->nbLose = $nbLose;

        return $this;
    }

    /**
     * Get nbLose
     *
     * @return integer 
     */
    public function getNbLose()
    {
        return $this->nbLose;
    }

    /**
     * Set nbWinFanny
     *
     * @param integer $nbWinFanny
     * @return BabyStats
     */
    public function setNbWinFanny($nbWinFanny)
    {
        $this->nbWinFanny = $nbWinFanny;

        return $this;
    }

    /**
     * Get nbWinFanny
     *
     * @return integer 
     */
    public function getNbWinFanny()
    {
        return $this->nbWinFanny;
    }

    /**
     * Set nbLoseFanny
     *
     * @param integer $nbLoseFanny
     * @return BabyStats
     */
    public function setNbLoseFanny($nbLoseFanny)
    {
        $this->nbLoseFanny = $nbLoseFanny;

        return $this;
    }

    /**
     * Get nbLoseFanny
     *
     * @return integer 
     */
    public function getNbLoseFanny()
    {
        return $this->nbLoseFanny;
    }

    /**
     * Set nbButScored
     *
     * @param integer $nbButScored
     * @return BabyStats
     */
    public function setNbButScored($nbButScored)
    {
        $this->nbButScored = $nbButScored;

        return $this;
    }

    /**
     * Get nbButScored
     *
     * @return integer 
     */
    public function getNbButScored()
    {
        return $this->nbButScored;
    }

    /**
     * Set nbButTaken
     *
     * @param integer $nbButTaken
     * @return BabyStats
     */
    public function setNbButTaken($nbButTaken)
    {
        $this->nbButTaken = $nbButTaken;

        return $this;
    }

    /**
     * Get nbButTaken
     *
     * @return integer 
     */
    public function getNbButTaken()
    {
        return $this->nbButTaken;
    }

    /**
     * Set bestOponent
     *
     * @param string $bestOponent
     * @return BabyStats
     */
    public function setBestOponent($bestOponent)
    {
        $this->bestOponent = $bestOponent;

        return $this;
    }

    /**
     * Get bestOponent
     *
     * @return string 
     */
    public function getBestOponent()
    {
        return $this->bestOponent;
    }

    /**
     * Set worstOponent
     *
     * @param string $worstOponent
     * @return BabyStats
     */
    public function setWorstOponent($worstOponent)
    {
        $this->worstOponent = $worstOponent;

        return $this;
    }

    /**
     * Get worstOponent
     *
     * @return string 
     */
    public function getWorstOponent()
    {
        return $this->worstOponent;
    }

    /**
     * Set bestMate
     *
     * @param string $bestMate
     * @return BabyStats
     */
    public function setBestMate($bestMate)
    {
        $this->bestMate = $bestMate;

        return $this;
    }

    /**
     * Get bestMate
     *
     * @return string 
     */
    public function getBestMate()
    {
        return $this->bestMate;
    }

    /**
     * Set worstMate
     *
     * @param string $worstMate
     * @return BabyStats
     */
    public function setWorstMate($worstMate)
    {
        $this->worstMate = $worstMate;

        return $this;
    }

    /**
     * Get worstMate
     *
     * @return string 
     */
    public function getWorstMate()
    {
        return $this->worstMate;
    }

    /**
     * Set player
     *
     * @param \Baby\StatBundle\Entity\User $player
     * @return BabyStats
     */
    public function setPlayer(\Baby\StatBundle\Entity\User $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \Baby\StatBundle\Entity\User 
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
