<?php

namespace Baby\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{

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
				'victoires' => 0,
				'defaites' => 0
			);
			$players[$p->getId()]['ratio'] = 0;
		}

		$query = $this->_em->createQuery(
						'SELECT p.id, p.username as name,
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
			$p['ratio'] = $nb != 0 ? round($p['victoires'] / ($nb), 2) : 0;
			$players[$p['id']] = $p;
		}

		if ($multi) {
			$tmp = $players;
			$players = array();
			self::aasort($tmp, 'ratio');
			$players['ratio'] = array_values($tmp);
			self::aasort($tmp, 'victoires');
			$players['victoires'] = array_values($tmp);
			self::aasort($tmp, 'defaites');
			$players['defaites'] = array_values($tmp);
		} else {
			self::aasort($players, 'ratio');
			$players = array_values($players);
			if ($limit !== null) {
				$players = array_slice($players, 0, $limit);
			}
		}

		return $players;
	}

	public function getPlayerData($id, $dt)
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
			$data['victoires'][] = intval($d['victoires']);
			$data['defaites'][] = intval($d['defaites']);
			$data['ratio'][] = round(intval($d['victoires']) / (intval($d['victoires']) + intval($d['defaites'])), 2);
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
