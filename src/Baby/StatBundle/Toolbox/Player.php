<?php

namespace Baby\StatBundle\Toolbox;

class Player {

	public static function getPlayerList($em, $limit = null, $multi = false) {
		$players = self::getBasePlayers($em, $limit);

		$query = $em->createQuery(
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
			WHERE g.date BETWEEN :start AND :end
			GROUP BY p.id')->setParameters(array(
				'start' => new \DateTime(date('Y-m-01')),
				'end' => new \DateTime(date('Y-m-t'))
			));

		foreach($query->getResult() as $p) {
			$p['ratio'] = round($p['victoires'] / ($p['victoires'] + $p['defaites']),2);
			$players[$p['id']] = $p;
		}

		if($multi) {
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
			if($limit !== null) {
				$players = array_slice($players, 0, $limit);
			}
		}


		return $players;
	}

	public static function getBasePlayers($em) {
		$query = $em->createQuery('SELECT p.id, p.username as name, 0 as victoires, 0 as defaites FROM BabyUserBundle:User p');

		$players = array();
		try {
			foreach($query->getResult() as $p){
				$players[$p['id']] = $p;
				$players[$p['id']]['ratio'] = 0;
			}
		} catch (\Exception $e){
			echo $e->getMessage();
		}
		return $players;
	}

	public static function getPlayerData($id, $em) {
		$query = $em->createQuery(
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
				'start' => new \DateTime(date('Y-m-01')),
				'end' => new \DateTime(date('Y-m-t')),
				'id' => $id
			));

		$data = array(
			'dates' => array(),
			'ratio' => array(),
			'victoires' => array(),
			'defaites' => array(),
		);

		foreach($query->getResult() as $d){
			$data['dates'][] = $d['date']->format('d-m-Y');
			$data['victoires'][] = intval($d['victoires']);
			$data['defaites'][] = intval($d['defaites']);
			$data['ratio'][] = round(intval($d['victoires']) / (intval($d['victoires']) + intval($d['defaites'])),2);
		}

		return $data;
	}

	public static function getDailyTops($em) {
		$q1 = $em->createQuery("SELECT p.username as name, COUNT(p.id) as ct
								FROM BabyStatBundle:BabyPlayed pl
								INNER JOIN BabyUserBundle:User p WITH p.id = pl.idPlayer
								INNER JOIN BabyStatBundle:BabyGame g WITH pl.idGame = g.id
								WHERE ((pl.team = 1 AND g.scoreTeam1 > g.scoreTeam2) OR (pl.team = 2 AND g.scoreTeam1 < g.scoreTeam2)) AND g.date = :date
								GROUP BY p.id
								ORDER BY ct DESC")->setParameter('date', new \Datetime(date('Y-m-d', strtotime('-1 day'))))->setMaxResults(1);

		$q2 = $em->createQuery("SELECT p.username as name, COUNT(p.id) as ct
								FROM BabyStatBundle:BabyPlayed pl
								INNER JOIN BabyUserBundle:User p WITH p.id = pl.idPlayer
								INNER JOIN BabyStatBundle:BabyGame g WITH pl.idGame = g.id
								WHERE ((pl.team = 1 AND g.scoreTeam1 < g.scoreTeam2) OR (pl.team = 2 AND g.scoreTeam1 > g.scoreTeam2)) AND g.date = :date
								GROUP BY p.id
								ORDER BY ct DESC")->setParameter('date', new \Datetime(date('Y-m-d', strtotime('-1 day'))))->setMaxResults(1);
		$q1 = $q1->getResult();
		$q2 = $q2->getResult();
		$q3 = self::getPlayerList($em);

		return array(
			'best' => sizeof($q1) > 0 ? $q1[0]['name'] : 'N/A',
			'worst' => sizeof($q2) > 0 ? $q2[0]['name'] : 'N/A',
			'nextchoco' => sizeof($q3) > 0 ? $q3[sizeof($q3)-1]['name'] : 'N/A',
			'lastchoco' => 'N/A',
		);
	}

	private static function aasort(&$array, $key) {
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
