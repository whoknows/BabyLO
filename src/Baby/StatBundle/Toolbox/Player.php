<?php

namespace Baby\StatBundle\Toolbox;

class Player {

	public static function getPlayerList($em, $limit = null, $multi = false) {
		$query = $em->createQuery(
			'SELECT p.id, p.name,
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
			FROM BabyStatBundle:BabyPlayer p
			INNER JOIN BabyStatBundle:BabyPlayed pl WITH p.id = pl.idPlayer
			INNER JOIN BabyStatBundle:BabyGame g WITH g.id = pl.idGame
			GROUP BY p.id');

		if($limit !== null) {
			$query->setMaxResults($limit);
		}

		$players = $query->getResult();

		foreach($players as &$p) {
			$p['ratio'] = round($p['victoires'] / ($p['victoires'] + $p['defaites']),2);
		}

		if($multi) {
			$tmp = $players;
			$players = array();
			self::aasort($tmp, 'ratio');
			$players['ratio'] = $tmp;
			self::aasort($tmp, 'victoires');
			$players['victoires'] = $tmp;
			self::aasort($tmp, 'defaites');
			$players['defaites'] = $tmp;
		} else {
			self::aasort($players, 'ratio');
		}

		return $players;
	}

	public static function getPlayerData($id, $em) {
		$query = $em->createQuery(
			'SELECT p.id, p.name, g.date,
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
			FROM BabyStatBundle:BabyPlayer p
			INNER JOIN BabyStatBundle:BabyPlayed pl WITH p.id = pl.idPlayer
			INNER JOIN BabyStatBundle:BabyGame g WITH g.id = pl.idGame
			WHERE p.id = :id
			GROUP BY g.date')->setParameter('id', $id);

		$data = array(
			'dates' => array(),
			'ratio' => array(),
			'victoires' => array(),
			'defaites' => array(),
		);

		foreach($query->getResult() as $d){
			$data['dates'][] = $d['date'];
			$data['victoires'][] = intval($d['victoires']);
			$data['defaites'][] = intval($d['defaites']);
			$data['ratio'][] = round(intval($d['victoires']) / (intval($d['victoires']) + intval($d['defaites'])),2);
		}

		return $data;
	}

	public static function getDailyTops() {
		//self::getPlayerList($this->getDoctrine()->getManager(), null, true);
		return array(
			'best' => 'test',
			'worst' => 'test',
			'nextchoco' => 'test',
			'lastchoco' => 'test',
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
