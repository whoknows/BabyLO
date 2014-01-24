<?php

namespace Baby\StatBundle\Toolbox;

class Stats {

	public static function calculate($id) {
		$bdd = new \PDO('mysql:host=localhost;dbname=baby', 'root', 'secret');
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
				SELECT pl.name
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN baby_player pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 < score_team2, p2.team = 1 AND score_team1 > score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.name
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN baby_player pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 2 AND score_team1 > score_team2, p2.team = 1 AND score_team1 < score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.name
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN baby_player pl ON pl.id = p2.id_player
				WHERE p.id_player = ".$id." AND IF(p.team = 1, p2.team = 1 AND score_team1 > score_team2, p2.team = 2  AND score_team1 < score_team2)
				GROUP BY p2.id_player
				ORDER BY COUNT(p.id) DESC
				LIMIT 0,1
			),
			(
				SELECT pl.name
				FROM baby_played p
				INNER JOIN baby_game g ON p.id_game = g.id
				INNER JOIN baby_played p2 ON p2.id_game = g.id AND p2.id_player != p.id_player
				INNER JOIN baby_player pl ON pl.id = p2.id_player
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
