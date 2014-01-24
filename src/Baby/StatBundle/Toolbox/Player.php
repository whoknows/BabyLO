<?php

namespace Baby\StatBundle\Toolbox;

class Player {

	public static function getPlayerList($em, $limit = null, $multi = false) {
		$gamerepo   = $em->getRepository('BabyStatBundle:BabyGame');
		$playerrepo = $em->getRepository('BabyStatBundle:BabyPlayer');
		$playedrepo = $em->getRepository('BabyStatBundle:BabyPlayed');

		$players = array();

		foreach($playerrepo->findAll() as $player){
			$tmp = array(
				"id" => $player->getId(),
				"name" => $player->getName(),
				"victoires" => 0,
				"defaites" => 0,
				"ratio" => 0
			);

			$playedGames = $playedrepo->findBy(array('idPlayer' => $player->getId()));

			if($playedGames === null) {
				$players[] = $tmp;
				continue;
			}

			foreach($playedGames as $pg){
				$game = $gamerepo->find($pg->getIdGame());

				if($pg->getTeam() == 1){
					if($game->getScoreTeam1() < $game->getScoreTeam2()){
						$tmp['defaites']++;
					} else {
						$tmp['victoires']++;
					}
				} else {
					if($game->getScoreTeam2() < $game->getScoreTeam1()){
						$tmp['defaites']++;
					} else {
						$tmp['victoires']++;
					}
				}
			}

			if($tmp['defaites'] == 0) {
				$tmp['ratio'] = $tmp['victoires'];
			} else {
				$tmp['ratio'] = round($tmp['victoires'] / ($tmp['victoires']+$tmp['defaites']),2);
			}

			$players[] = $tmp;
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

		if($limit !== null){
			if($multi === false){
				$players = array_slice($players, 0, $limit);
			} else {
				$players['defaites'] = array_slice($players['defaites'],0,$limit);
				$players['victoires'] = array_slice($players['victoires'],0,$limit);
				$players['ratio'] = array_slice($players['ratio'],0,$limit);
			}
		}

		return $players;
	}

	public static function getPlayerData($id, $em) {
		$gamerepo   = $em->getRepository('BabyStatBundle:BabyGame');
		$playedrepo = $em->getRepository('BabyStatBundle:BabyPlayed');

		$player = $em->getRepository('BabyStatBundle:BabyPlayer')->find($id);

		$playedGames = $playedrepo->findBy(array('idPlayer' => $player->getId()));

		if($playedGames === null) {
			return array();
		}

		$data = array(
			'dates' => array(),
			'victoires' => array(),
			'defaites' => array(),
			'ratio' => array()
		);

		$dataTmp = array();

		foreach($playedGames as $pg){
			$game = $gamerepo->find($pg->getIdGame());

			$date = $game->getDate()->format('d-m-Y');

			if(!isset($dataTmp[$date])){
				$dataTmp[$date] = array(
					'date' => $date,
					'victoires' => 0,
					'defaites' => 0
				);
			}

			if($pg->getTeam() == 1){
				if($game->getScoreTeam1() < $game->getScoreTeam2()){
					$dataTmp[$date]['defaites']++;
				} else {
					$dataTmp[$date]['victoires']++;
				}
			} else {
				if($game->getScoreTeam2() < $game->getScoreTeam1()){
					$dataTmp[$date]['defaites']++;
				} else {
					$dataTmp[$date]['victoires']++;
				}
			}
		}

		foreach($dataTmp as $d){
			$data['dates'][] = $d['date'];
			$data['victoires'][] = $d['victoires'];
			$data['defaites'][] = $d['defaites'];
			$data['ratio'][] = round($d['victoires'] / ($d['victoires'] + $d['defaites']),2);
		}

		return $data;
	}

	public static function getDailyTops() {
		//
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
