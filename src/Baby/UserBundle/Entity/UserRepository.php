<?php

namespace Baby\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{

	private static $POIDS_RATIO = 0.65;

	public function getStandardUserList($enabled = 0)
	{
		$qb = $this->_em->createQueryBuilder();
		$query = $qb->select('p')
						->from('BabyUserBundle:User', 'p')
						->where($qb->expr()->neq('p.username', ':name'), $qb->expr()->neq('p.enabled', $enabled))
						->orderBy('p.username', 'asc')
						->getQuery()->setParameter('name', 'admin');
		return $query->execute();
	}

	public function getPlayerList($limit = null, $multi = false)
	{
		$players = array();

		foreach ($this->getStandardUserList() as $p) {
			$players[$p->getId()] = array(
				'id' => $p->getId(),
				'name' => $p->getUsername(),
				'img' => self::getGravatar($p->getEmail(), 35),
				'victoires' => 0,
				'defaites' => 0
			);
			$players[$p->getId()]['ratio'] = 0;
		}

		$query = $this->_em->createQuery(
						'SELECT p.id, p.username as name,p.email,
					SUM(
						CASE
							WHEN pl.team = 1 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
							WHEN pl.team = 2 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
						ELSE 0 END
					) as victoires,
					SUM(
						CASE
							WHEN pl.team = 1 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
							WHEN pl.team = 2 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
						ELSE 0 END
					) as defaites
			FROM BabyUserBundle:User p
			INNER JOIN BabyStatBundle:BabyPlayed pl WITH p.id = pl.idPlayer
			INNER JOIN BabyStatBundle:BabyGame g WITH g.id = pl.idGame
			WHERE p.username != :name AND g.date BETWEEN :start AND :end AND p.enabled = 1
			GROUP BY p.id')
				->setParameters(array(
			'start' => new \DateTime(date('Y-m-01')),
			'end' => new \DateTime(date('Y-m-t')),
			'name' => 'admin'
		));

		foreach ($query->getResult() as $p) {
			$nb = $p['victoires'] + $p['defaites'];
			$p['ratio'] = $this->calculRatio($nb, $p['victoires']);
			$players[$p['id']] = array_merge($players[$p['id']], $p);
		}

		if ($multi) {
			$tmp = $players;
			$players = array();
			self::aasort($tmp, 'ratio');
			$players['ratio'] = array_values($tmp);
			self::aasort($tmp, 'victoires');
			$players['victoires'] = array_values($tmp);
			//self::aasort($tmp, 'defaites');
			$players['defaites'] = array_values($this->getPlayersAllTime());
		} else {
			self::aasort($players, 'ratio');
			$players = array_values($players);
			if ($limit !== null) {
				$players = array_slice($players, 0, $limit);
			}
		}

		return $players;
	}

    public function getPlayersAllTime()
    {
        $players = array();

        foreach ($this->getStandardUserList() as $p) {
            $players[$p->getId()] = array(
                'id' => $p->getId(),
                'name' => $p->getUsername(),
                'img' => self::getGravatar($p->getEmail(), 35),
                'victoires' => 0,
                'defaites' => 0
            );
            $players[$p->getId()]['ratio'] = 0;
        }

        $query = $this->_em->createQuery(
            'SELECT p.id, p.username as name,p.email,
                    SUM(
                        CASE
                            WHEN pl.team = 1 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
                            WHEN pl.team = 2 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
                        ELSE 0 END
                    ) as victoires,
                    SUM(
                        CASE
                            WHEN pl.team = 1 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
                            WHEN pl.team = 2 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
                        ELSE 0 END
                    ) as defaites
            FROM BabyUserBundle:User p
            INNER JOIN BabyStatBundle:BabyPlayed pl WITH p.id = pl.idPlayer
            INNER JOIN BabyStatBundle:BabyGame g WITH g.id = pl.idGame
            WHERE p.username != :name AND p.enabled = 1
            GROUP BY p.id')->setParameters(array('name' => 'admin'));

        foreach ($query->getResult() as $p) {
            $nb = $p['victoires'] + $p['defaites'];
            $p['ratio'] = $this->calculRatio($nb, $p['victoires'], false);
            $players[$p['id']] = array_merge($players[$p['id']], $p);
        }

        self::aasort($players, 'ratio');

        return $players;
    }

	public function getPlayerData($id, $dt, $ag)
	{
		$query = $this->_em->createQuery(
						'SELECT p.id, p.username as name, g.date,
					SUM(
						CASE
							WHEN pl.team = 1 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
							WHEN pl.team = 2 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
						ELSE 0 END
					) as victoires,
					SUM(
						CASE
							WHEN pl.team = 1 AND g.scoreTeam1 < g.scoreTeam2 THEN 1
							WHEN pl.team = 2 AND g.scoreTeam1 > g.scoreTeam2 THEN 1
						ELSE 0 END
					) as defaites
			FROM BabyUserBundle:User p
			INNER JOIN BabyStatBundle:BabyPlayed pl WITH p.id = pl.idPlayer
			INNER JOIN BabyStatBundle:BabyGame g WITH g.id = pl.idGame
			WHERE p.id = :id AND g.date BETWEEN :start AND :end
			GROUP BY g.date')->setParameters(array(
			'start' => new \DateTime(date('Y-m-01', strtotime($dt))),
			'end' => new \DateTime(date('Y-m-t', strtotime($dt))),
			'id' => $id
		));

		$data = array(
			'dates' => array(),
			'ratio' => array(),
			'victoires' => array(),
			'defaites' => array(),
		);

		foreach ($query->getResult() as $d) {
			$data['dates'][] = $d['date']->format('d-m-Y');
			if ($ag == 1) {
				$prevVic = isset($data['victoires'][sizeof($data['victoires'])-1]) ? $data['victoires'][sizeof($data['victoires'])-1] : 0;
				$prevDef = isset($data['defaites'][sizeof($data['defaites'])-1]) ? $data['defaites'][sizeof($data['defaites'])-1] : 0;

				$data['victoires'][] = $prevVic + intval($d['victoires']);
				$data['defaites'][] = $prevDef + intval($d['defaites']);
				$data['ratio'][] = $this->calculRatio(intval($data['victoires'][sizeof($data['victoires'])-1]) + intval($data['defaites'][sizeof($data['defaites'])-1]), intval($data['victoires'][sizeof($data['victoires'])-1]));
			} else {
				$data['victoires'][] = intval($d['victoires']);
				$data['defaites'][] = intval($d['defaites']);
				$data['ratio'][] = $this->calculRatio(intval($d['victoires']) + intval($d['defaites']), intval($d['victoires']));
			}
		}

		return $data;
	}

	public function getDailyTops()
	{
		$q1 = $this->_em->createQuery("SELECT p.username as name, COUNT(p.id) as ct
								FROM BabyStatBundle:BabyPlayed pl
								INNER JOIN BabyUserBundle:User p WITH p.id = pl.idPlayer
								INNER JOIN BabyStatBundle:BabyGame g WITH pl.idGame = g.id
								WHERE ((pl.team = 1 AND g.scoreTeam1 > g.scoreTeam2) OR (pl.team = 2 AND g.scoreTeam1 < g.scoreTeam2)) AND g.date = :date AND p.enabled = 1
								GROUP BY p.id
								ORDER BY ct DESC")->setParameter('date', new \Datetime(date('Y-m-d', strtotime('-1 day'))))->setMaxResults(1);

		$q2 = $this->_em->createQuery("SELECT p.username as name, COUNT(p.id) as ct
								FROM BabyStatBundle:BabyPlayed pl
								INNER JOIN BabyUserBundle:User p WITH p.id = pl.idPlayer
								INNER JOIN BabyStatBundle:BabyGame g WITH pl.idGame = g.id
								WHERE ((pl.team = 1 AND g.scoreTeam1 < g.scoreTeam2) OR (pl.team = 2 AND g.scoreTeam1 > g.scoreTeam2)) AND g.date = :date AND p.enabled = 1
								GROUP BY p.id
								ORDER BY ct DESC")->setParameter('date', new \Datetime(date('Y-m-d', strtotime('-1 day'))))->setMaxResults(1);

		$q3 = $this->_em->createQuery("SELECT p.username as name, AVG(CASE WHEN pl.team = 1 THEN g.scoreTeam2 ELSE g.scoreTeam1 END) as ct
								FROM BabyStatBundle:BabyPlayed pl
								INNER JOIN BabyUserBundle:User p WITH p.id = pl.idPlayer
								INNER JOIN BabyStatBundle:BabyGame g WITH pl.idGame = g.id
								WHERE g.date BETWEEN :start AND :end AND p.enabled = 1
								GROUP BY p.id
								ORDER BY ct DESC")
				->setParameters(array(
					'start' => new \DateTime(date('Y-m-01')),
					'end' => new \DateTime(date('Y-m-t'))))
				->setMaxResults(1);
		$q1 = $q1->getResult();
		$q2 = $q2->getResult();
		$q3 = $q3->getResult();
		$q4 = $this->getPlayerList();

		$default = array('name' => 'N/A', 'ct' => 'N/A');

		$q4 = sizeof($q4) > 0 ? $q4[sizeof($q4) - 1] : $default;
		$q4['ratio'] = isset($q4['ratio']) ? $q4['ratio'] : $q4['ct'];

		return array(
			'best' => sizeof($q1) > 0 ? $q1[0] : $default,
			'worst' => sizeof($q2) > 0 ? $q2[0] : $default,
			'buts' => sizeof($q3) > 0 ? $q3[0] : $default,
			'nextchoco' => $q4,
		);
	}

	public function getNbGames($id)
	{
		$dql = "SELECT COUNT(p.id) as ct, g.date
				FROM BabyStatBundle:BabyPlayed p
				INNER JOIN BabyStatBundle:BabyGame g WITH g.id = p.idGame
				WHERE p.idPlayer = :id
				GROUP BY g.date
				ORDER BY g.date ASC";
		return $this->_em->createQuery($dql)->setParameter('id', $id)->getResult();
	}

	public function getNbWin($id)
	{
		$dql = "SELECT COUNT(p.id) as ct, g.date
				FROM BabyStatBundle:BabyPlayed p
				INNER JOIN BabyStatBundle:BabyGame g WITH p.idGame = g.id
				WHERE p.idPlayer = :id AND ((p.team = 1 AND g.scoreTeam1 > g.scoreTeam2) OR (p.team = 2 AND g.scoreTeam1 < g.scoreTeam2))
				GROUP BY g.date
				ORDER BY g.date ASC";
		return $this->_em->createQuery($dql)->setParameter('id', $id)->getResult();
	}

	public function getNbLose($id)
	{
		$dql = "SELECT COUNT(p.id) as ct, g.date
				FROM BabyStatBundle:BabyPlayed p
				INNER JOIN BabyStatBundle:BabyGame g WITH p.idGame = g.id
				WHERE p.idPlayer = :id AND ((p.team = 1 AND g.scoreTeam1 < g.scoreTeam2) OR (p.team = 2 AND g.scoreTeam1 > g.scoreTeam2))
				GROUP BY g.date
				ORDER BY g.date ASC";
		return $this->_em->createQuery($dql)->setParameter('id', $id)->getResult();
	}

	public function getNbButScored($id)
	{
		$dql = "SELECT SUM(CASE
							WHEN p.team = 1 THEN g.scoreTeam1
							WHEN p.team = 2 THEN g.scoreTeam2
						ELSE 0 END) as ct, g.date
				FROM BabyStatBundle:BabyPlayed p
				INNER JOIN BabyStatBundle:BabyGame g WITH p.idGame = g.id
				WHERE p.idPlayer = :id
				GROUP BY g.date
				ORDER BY g.date ASC";
		return $this->_em->createQuery($dql)->setParameter('id', $id)->getResult();
	}

	public function getNbButTaken($id)
	{
		$dql = "SELECT SUM(CASE
							WHEN p.team = 1 THEN g.scoreTeam2
							WHEN p.team = 2 THEN g.scoreTeam1
						ELSE 0 END) as ct, g.date
				FROM BabyStatBundle:BabyPlayed p
				INNER JOIN BabyStatBundle:BabyGame g WITH p.idGame = g.id
				WHERE p.idPlayer = :id
				GROUP BY g.date
				ORDER BY g.date ASC";
		return $this->_em->createQuery($dql)->setParameter('id', $id)->getResult();
	}

	public function getAllStats($id, $filter = true, $periode = '-1 month')
	{
		$where = "";
		if ($filter) {
			$where = " AND g.date BETWEEN '" . date('Y-m-01', strtotime($periode)) . " 00:00:00' AND '" . date('Y-m-t', strtotime($periode)) . " 00:00:00' ";
		}

		$sql = "SELECT
				(SELECT position FROM baby_user WHERE id = " . $id . ") as position,
				(
					SELECT COUNT(p.id) FROM baby_played p INNER JOIN baby_game g ON p.id_game = g.id WHERE id_player = " . $id . $where . "
				) as nbGames,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = " . $id . " AND IF(team = 1, score_team1 > score_team2, score_team1 < score_team2)" . $where . "
				) as nbWin,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = " . $id . " AND IF(team = 1, score_team1 < score_team2, score_team1 > score_team2)" . $where . "
				) as nbLose,
				(
					SELECT SUM(IF(team = 1, score_team1, score_team2))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = " . $id . "" . $where . "
				) as nbButScored,
				(
					SELECT SUM(IF(team = 1, score_team2, score_team1))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = " . $id . "" . $where . "
				) as nbButTaken,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN baby_user pl ON pl.id = p2.id_player
					WHERE p.id_player = " . $id . " AND IF(p.team = 1, p2.team = 2 AND score_team1 < score_team2, p2.team = 1 AND score_team1 > score_team2)" . $where . "
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as bestOponent,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN baby_user pl ON pl.id = p2.id_player
					WHERE p.id_player = " . $id . " AND IF(p.team = 1, p2.team = 2 AND score_team1 > score_team2, p2.team = 1 AND score_team1 < score_team2)" . $where . "
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as worstOponent,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN baby_user pl ON pl.id = p2.id_player
					WHERE p.id_player = " . $id . " AND IF(p.team = 1, p2.team = 1 AND score_team1 > score_team2, p2.team = 2  AND score_team1 < score_team2)" . $where . "
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as bestMate,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN baby_user pl ON pl.id = p2.id_player
					WHERE p.id_player = " . $id . " AND IF(p.team = 1, p2.team = 1 AND score_team1 < score_team2, p2.team = 2  AND score_team1 > score_team2)" . $where . "
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as worstMate";

		$query = $this->_em->getConnection();
		$data = $query->fetchAll($sql)[0];

		if (sizeof($data) == 0) {
			$data = array();
		} else {
			if ($data['nbGames'] == 0) {
				$data['ratio'] = 0;
				$data['nbButTakenAvg'] = 0;
				$data['nbButScoredAvg'] = 0;
			} else {
				$data['ratio'] = $this->calculRatio($data['nbGames'], $data['nbWin'], false);
				$data['nbButTakenAvg'] = round($data['nbButTaken'] / $data['nbGames'], 3);
				$data['nbButScoredAvg'] = round($data['nbButScored'] / $data['nbGames'], 3);
			}
		}

		return $data;
	}

	public function calculRatio($parties, $victoires, $currentMonth = true)
	{
		$dql = "SELECT COUNT(g.id) as total FROM BabyStatBundle:BabyGame g";
		if($currentMonth) {
			$dql.= " WHERE g.date >= :date";
		}

		$query = $this->_em->createQuery($dql);

		if($currentMonth) {
			$query->setParameter('date', new \Datetime(date('Y-m-01')));
		}

		$total = $query->getResult();
		$total = intval($total[0]['total']);
		$poids = $parties / $total;
		$ratio = $parties != 0 ? round($victoires / ($parties), 2, PHP_ROUND_HALF_DOWN) : 0;

		$classement = round(($ratio * self::$POIDS_RATIO) + ($poids * (1-self::$POIDS_RATIO)), 2);

		return $classement;
	}

	public static function getGravatar( $email, $s = 40, $d = 'mm', $r = 'x', $img = false, $atts = array() )
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5( strtolower( trim( $email ) ) );
		$url .= "?s=$s&d=$d&r=$r";
		if ( $img ) {
			$url = '<img src="' . $url . '"';
			foreach ( $atts as $key => $val ) {
				$url .= ' ' . $key . '="' . $val . '"';
			}
			$url .= ' />';
		}
		return $url;
	}

	public static function aasort(&$array, $key)
	{
		$sorter = array();
		$ret = array();
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii] = $va[$key];
		}
		arsort($sorter);
		foreach ($sorter as $ii => $va) {
			$ret[$ii] = $array[$ii];
		}
		$array = $ret;
	}

}
