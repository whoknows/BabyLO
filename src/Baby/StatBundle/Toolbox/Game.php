<?php

namespace Baby\StatBundle\Toolbox;

class Game {

	public static function getGameList($em, $limit = null, $filter = array()) {
		$f = array();
		if (isset($filter['date']) && $filter['date'] != '') {
			$f['date'] = new \DateTime($filter['date']);
		}

		$query = $em->createQuery('SELECT g.id, g.date, g.scoreTeam1, g.scoreTeam2
								FROM BabyStatBundle:BabyGame g
								' . (isset($f['date']) ? 'WHERE g.date = :date' : '') . '
								ORDER BY g.date DESC, g.id DESC');
		if (isset($f['date'])) {
			$query->setParameter('date', $f['date']);
		}

		if ($limit !== null) {
			$query->setMaxResults($limit);
		}
		$games = array();
		foreach ($query->getResult() as $row) {

			$t1 = $em->createQuery('SELECT p.username as name
									FROM BabyUserBundle:User p
									INNER JOIN BabyStatBundle:BabyPlayed pl WITH pl.idPlayer = p.id
									WHERE pl.team = 1 AND pl.idGame = :game
									ORDER BY pl.id ASC')->setParameter('game', $row['id']);
			$t2 = $em->createQuery('SELECT p.username as name
									FROM BabyUserBundle:User p
									INNER JOIN BabyStatBundle:BabyPlayed pl WITH pl.idPlayer = p.id
									WHERE pl.team = 2 AND pl.idGame = :game
									ORDER BY pl.id ASC')->setParameter('game', $row['id']);
			$r1 = $t1->getResult();
			$r2 = $t2->getResult();

			$games[] = array_merge($row, array(
				"player1Team1" => $r1[0]['name'],
				"player2Team1" => $r1[1]['name'],
				"player1Team2" => $r2[0]['name'],
				"player2Team2" => $r2[1]['name'],
			));
		}

		return $games;
	}

	public static function getGameCount($em) {
		$gr = $em->getRepository('BabyStatBundle:BabyGame');
		$data = array(
			'date' => array(),
			'nb' => array(),
		);
		foreach ($gr->findAll() as $game) {
			$date = $game->getDate()->format('d-m-Y');
			if (!isset($data['date'][$date])) {
				$data['date'][$date] = $date;
				$data['nb'][$date] = 0;
			}
			$data['nb'][$date] ++;
		}

		return array(
			'date' => array_values($data['date']),
			'nb' => array_values($data['nb'])
		);
	}

	public static function matchMaking($players) {
		return array();
	}

}
