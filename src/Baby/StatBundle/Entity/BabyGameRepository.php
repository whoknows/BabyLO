<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * GameRepository
 */
class BabyGameRepository extends EntityRepository
{

    public function getGameList($limit = null, $filter = array())
    {
        $f = $this->prepareFilters($filter);

        $query = $this->_em->createQuery('SELECT g.id, g.date, g.scoreTeam1, g.scoreTeam2
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

            $t1 = $this->_em->createQuery('SELECT p.username as name
									FROM BabyUserBundle:User p
									INNER JOIN BabyStatBundle:BabyPlayed pl WITH pl.idPlayer = p.id
									WHERE pl.team = 1 AND pl.idGame = :game
									ORDER BY pl.id ASC')->setParameter('game', $row['id']);
            $t2 = $this->_em->createQuery('SELECT p.username as name
									FROM BabyUserBundle:User p
									INNER JOIN BabyStatBundle:BabyPlayed pl WITH pl.idPlayer = p.id
									WHERE pl.team = 2 AND pl.idGame = :game
									ORDER BY pl.id ASC')->setParameter('game', $row['id']);
            $r1 = $t1->getResult();
            $r2 = $t2->getResult();

            $tmp = array(
                "player1Team1" => $r1[0]['name'],
                "player2Team1" => $r1[1]['name'],
                "player1Team2" => $r2[0]['name'],
                "player2Team2" => $r2[1]['name'],
            );

            if ($this->filterResults($tmp, $f)) {
                $games[] = array_merge($row, $tmp);
            }
        }

        return $games;
    }

    private function prepareFilters($filter)
    {
        $f = array('players' => null, 'team1' => array(), 'team2' => array());
        if (isset($filter['date']) && $filter['date'] != '') {
            $f['date'] = new \DateTime($filter['date']);
        }

        if (isset($filter['player']) && $filter['player'] !== null) {
            $f['players'] = explode(',', $filter['player']);

            foreach ($f['players'] as &$p) {
                $p = ucfirst(trim($p));
            }
        }

        return $f;
    }

    private function filterResults($data, $f)
    {
        if ($f['players'] !== null) {
            $in_array = true;
            foreach ($f['players'] as $pl) {
                if (!in_array($pl, $data)) {
                    $in_array = false;
                }
            }

            return sizeof($f['players']) == 0 || $in_array;
        }

        return true;
    }

    public function getGameCount()
    {
        $gr = $this->_em->getRepository('BabyStatBundle:BabyGame');
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
            $data['nb'][$date]++;
        }

        return array(
            'date' => array_values($data['date']),
            'nb' => array_values($data['nb'])
        );
    }

    public function matchMaking($players)
    {
        $pdata = array();
        $teams = array();

        $ple = $this->_em->getRepository('BabyUserBundle:User');

        foreach ($players as $p) {
            $tmp = $ple->getPlayerData($p, 'all', 0);
            $pdata[] = array(
                'score' => round(array_sum($tmp['score']) / sizeof($tmp['score']), 2),
                'id' => $p
            );
        }

        $ple::aasort($pdata, 'score');

        $pdata = array_values($pdata);

        $size = sizeof($pdata);

        $cpt = round($size / 2, 0, PHP_ROUND_HALF_DOWN);

        for ($i = 0; $i < $cpt; $i++) {
            $teams[] = array(
                $ple->findBy(array('id' => $pdata[$size - 1 - $i]['id']))[0]->getUsername(),
                $ple->findBy(array('id' => $pdata[$i]['id']))[0]->getUsername()
            );
        }
        if ($size % 2 !== 0) {
            $teams[] = array(
                $ple->findBy(array('id' => $pdata[$cpt]['id']))[0]->getUsername(),
                $ple->findBy(array('id' => $pdata[$cpt + 1]['id']))[0]->getUsername()
            );

            $teams[] = array(
                $ple->findBy(array('id' => $pdata[$cpt]['id']))[0]->getUsername(),
                $ple->findBy(array('id' => $pdata[$cpt - 1]['id']))[0]->getUsername()
            );
        }

        return $teams;
    }
}
