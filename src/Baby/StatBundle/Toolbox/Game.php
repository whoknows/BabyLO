<?php

namespace Baby\StatBundle\Toolbox;

class Game {

	public static function getGameList($em, $limit = null, $filter = array()) {
		$gamerepo   = $em->getRepository('BabyStatBundle:BabyGame');
		$playerrepo = $em->getRepository('BabyStatBundle:BabyPlayer');
		$playedrepo = $em->getRepository('BabyStatBundle:BabyPlayed');

		$f = array();
		if(isset($filter['date']) && $filter['date'] != ''){
			$f['date'] = new \DateTime($filter['date']);
		}

		$repoGames = $gamerepo->findBy($f, array('date' => 'DESC', 'id' => 'DESC'));

		$games = array();

		if($repoGames !== null){
			foreach($repoGames as $game){
				$games[] = array(
					"id" => $game->getId(),
					"date" => $game->getDate(),
					"scoreTeam1" => $game->getScoreTeam1(),
					"scoreTeam2" => $game->getScoreTeam2(),
					"player1Team1" => $playerrepo->find($playedrepo->findBy(array('idGame' => $game->getId(), 'team' => 1), array('idPlayer' => 'ASC'),1,0)[0]->getIdPlayer())->getName(),
					"player2Team1" => $playerrepo->find($playedrepo->findBy(array('idGame' => $game->getId(), 'team' => 1), array('idPlayer' => 'DESC'),1,0)[0]->getIdPlayer())->getName(),
					"player1Team2" => $playerrepo->find($playedrepo->findBy(array('idGame' => $game->getId(), 'team' => 2), array('idPlayer' => 'ASC'),1,0)[0]->getIdPlayer())->getName(),
					"player2Team2" => $playerrepo->find($playedrepo->findBy(array('idGame' => $game->getId(), 'team' => 2), array('idPlayer' => 'DESC'),1,0)[0]->getIdPlayer())->getName(),
				);
			}
		}

		if($limit !== null){
			$games = array_slice($games, 0, $limit);
		}

		return $games;
	}

	public static function getGameCount($em) {
		$gr = $em->getRepository('BabyStatBundle:BabyGame');
		$data = array(
			'date' => array(),
			'nb' => array(),
		);
		foreach($gr->findAll() as $game) {
			$date = $game->getDate()->format('d-m-Y');
			if(!isset($data['date'][$date])){
				$data['date'][$date] = $date;
				$data['nb'][$date] = 0;
			}
			$data['nb'][$date]++;
		}

		$data = array(
			'date' => array_values($data['date']),
			'nb' => array_values($data['nb'])
		);

		return $data;
	}
}
