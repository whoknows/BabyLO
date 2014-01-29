<?php

namespace Baby\StatBundle\Toolbox;

class Stats {

	public static function getDB() {
		return new \PDO('mysql:host=localhost;dbname=baby', 'root', 'secret');
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

	public static function getRatio($id) {
		$sql = "";
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

	public static function getDailyBest() {
		$sql = "";
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

	public static function getAllStats($id) {
		$sql = "SELECT
				(
					SELECT COUNT(id) FROM baby_played WHERE id_player = ".$id."
				) as nbGames,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 > score_team2, score_team1 < score_team2)
				) as nbWin,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 < score_team2, score_team1 > score_team2)
				) as nbLose,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team2 = 0, score_team1 = 0)
				) as nbWinFanny,
				(
					SELECT COUNT(p.id)
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id." AND IF(team = 1, score_team1 = 0, score_team2 = 0)
				) as nbLoseFanny,
				(
					SELECT SUM(IF(team = 1, score_team1, score_team2))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id."
				) as nbButScored,
				(
					SELECT SUM(IF(team = 1, score_team2, score_team1))
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					WHERE id_player = ".$id."
				) as nbButTaken,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN User pl ON pl.id = p2.id_player
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 < score_team2, p2.team = 1 AND score_team1 > score_team2)
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as bestOponent,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN User pl ON pl.id = p2.id_player
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 > score_team2, p2.team = 1 AND score_team1 < score_team2)
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as worstOponent,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN User pl ON pl.id = p2.id_player
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 > score_team2, p2.team = 2  AND score_team1 < score_team2)
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as bestMate,
				(
					SELECT pl.username
					FROM baby_played p
					INNER JOIN baby_game g ON p.id_game = g.id
					INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
					INNER JOIN User pl ON pl.id = p2.id_player
					WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 < score_team2, p2.team = 2  AND score_team1 > score_team2)
					GROUP BY p2.id_player
					ORDER BY COUNT(p.id) DESC
					LIMIT 0,1
				) as worstMate";
		$bdd = self::getDB();
		try {
			$req = $bdd->query($sql);
			return $req->fetch(\PDO::FETCH_ASSOC);
		} catch(\Exception $e) {
			return $e->getMessage();
		}
	}

	public static function calculate($id) {
		$bdd = self::getDB();
		$sql = "REPLACE INTO `baby_stats` VALUES (
			default,
			".$id.",
			(
				SELECT COUNT(id) FROM baby_played WHERE id_player = ".$id."
			),
			(
				SELECT COUNT(p.id)
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team1 > score_team2, score_team1 < score_team2)
			),
			(
				SELECT COUNT(p.id)
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team1 < score_team2, score_team1 > score_team2)
			),
			(
				SELECT COUNT(p.id)
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team2 = 0, score_team1 = 0)
			),
			(
				SELECT COUNT(p.id)
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id." AND IF(team = 1, score_team1 = 0, score_team2 = 0)
			),
			(
				SELECT SUM(IF(team = 1, score_team1, score_team2))
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id."
			),
			(
				SELECT SUM(IF(team = 1, score_team2, score_team1))
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				WHERE id_player = ".$id."
			),
			(
				SELECT pl.username
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN User pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 < score_team2, p2.team = 1 AND score_team1 > score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.username
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN User pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 > score_team2, p2.team = 1 AND score_team1 < score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.username
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN User pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 > score_team2, p2.team = 2  AND score_team1 < score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.username
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN User pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 < score_team2, p2.team = 2  AND score_team1 > score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			)
		)";
		try {
			$bdd->exec($sql);
		} catch(\Exception $e) {
			return $e->getMessage();
		}
		return 'ok';
	}

}
