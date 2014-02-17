<?php

namespace Baby\StatBundle\Toolbox;

class Stats {

	public static function getDB() {
		return new \PDO('mysql:host=localhost;dbname=baby_dev', 'root', 'secret');
	}

	public static function getNbGames($id) {
		$sql = "SELECT COUNT(p.id) as ct, `date`
				FROM baby_played p
				INNER JOIN baby_game g ON g.id = id_game
				WHERE id_player = ".$id."
				GROUP BY `date`
				ORDER BY `date` ASC";
		return self::formatData($sql);
	}

	public static function getNbWin($id) {
		$sql = "SELECT COUNT(p.id) as ct, `date`
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team1 > score_team2, score_team1 < score_team2)
				GROUP BY `date`
				ORDER BY `date` ASC";
		return self::formatData($sql);
	}

	public static function getNbLose($id) {
		$sql = "SELECT COUNT(p.id) as ct, `date`
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team1 < score_team2, score_team1 > score_team2)
				GROUP BY `date`
				ORDER BY `date` ASC";
		return self::formatData($sql);
	}

	public static function getNbButScored($id) {
		$sql = "SELECT SUM(IF(team = 1, score_team1, score_team2)) as ct, `date`
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id."
				GROUP BY `date`
				ORDER BY `date` ASC";
		return self::formatData($sql);
	}

	public static function getNbButTaken($id) {
		$sql = "SELECT SUM(IF(team = 1, score_team2, score_team1)) as ct, `date`
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id."
				GROUP BY `date`
				ORDER BY `date` ASC";
		return self::formatData($sql);
	}

	public static function formatData($sql){
		$data = array(
			'date' => array(),
			'data' => array()
		);

		try {
			$req = self::getDB()->query($sql);
		} catch(\Exception $e) {
			return $e->getMessage();
		}

		foreach($req->fetchAll(\PDO::FETCH_ASSOC) as $row){
			$data['data'][] = intval($row['ct']);
			$data['date'][] = date('d-m-Y',strtotime($row['date']));
		}

		return $data;
	}

	public static function getAllStats($id, $filter=true, $periode = '-1 month') {
		$where = "";
		if($filter) {
			$where = " AND g.date BETWEEN '".date('Y-m-01', strtotime($periode))." 00:00:00' AND '".date('Y-m-t', strtotime($periode))." 00:00:00' ";
		}

		$sql = "SELECT
				(SELECT position FROM baby_user WHERE id = ".$id.") as position,
				(
					SELECT COUNT(p.id) FROM baby_played p INNER JOIN baby_game g ON p.id_game = g.id WHERE id_player = ".$id.$where."
				) as nbGames,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 > score_team2, score_team1 < score_team2)".$where."
				) as nbWin,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 < score_team2, score_team1 > score_team2)".$where."
				) as nbLose,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team2 = 0, score_team1 = 0)".$where."
				) as nbWinFanny,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 = 0, score_team2 = 0)".$where."
				) as nbLoseFanny,
				(
					SELECT SUM(IF(team = 1, score_team1, score_team2))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id."".$where."
				) as nbButScored,
				(
					SELECT SUM(IF(team = 1, score_team2, score_team1))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id."".$where."
				) as nbButTaken,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN baby_user pl ON pl.id = p2.id_player
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 < score_team2, p2.team = 1 AND score_team1 > score_team2)".$where."
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
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 > score_team2, p2.team = 1 AND score_team1 < score_team2)".$where."
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
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 > score_team2, p2.team = 2  AND score_team1 < score_team2)".$where."
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
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 < score_team2, p2.team = 2  AND score_team1 > score_team2)".$where."
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as worstMate";
		$bdd = self::getDB();

		try {
			$req = $bdd->query($sql);
			return $req->fetch(\PDO::FETCH_ASSOC);
		} catch(\Exception $e) {
			echo $e->getMessage();
		}
	}

}
