<?php

namespace Baby\StatBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BabyScheduleRepository extends EntityRepository
{
    public function changeSchedule($id, $creneau, $user)
    {
        $data = array();

        if ($id === null) {
            if ($this->isFull($creneau)) {
                return array('error' => 'full');
            }
            $schedule = new BabySchedule();
            $schedule->setDate(new \DateTime(date('Y-m-d')));
            $schedule->setCreneau($creneau);
            $schedule->setIdPlayer($user);
            $this->_em->persist($schedule);
            $this->_em->flush();

            $userEnt = $this->_em->getRepository('BabyUserBundle:User');

            $data = array(
                'id' => $schedule->getId(),
                'img' => $userEnt::getGravatar($user->getEmail()),
                'username' => $user->getUsername()
            );
        } else {
            $schedule = $this->_em->getRepository('BabyStatBundle:BabySchedule')->find($id);
            $this->_em->remove($schedule);
            $this->_em->flush();
        }

        return $data;
    }

    public function isFull($creneau)
    {
        $data = $this->_em->getRepository('BabyStatBundle:BabySchedule')->findBy(array(
            'date' => new \DateTime(date('Y-m-d')),
            'creneau' => $creneau
        ));

        return sizeof($data) == 4;
    }

    public function getComingGames($aggreg = false)
    {
        $rows = $this->_em->getRepository('BabyStatBundle:BabySchedule')->findBy(array('date' => new \DateTime(date('Y-m-d'))), array('creneau' => 'ASC'));
        $userEnt = $this->_em->getRepository('BabyUserBundle:User');
        $data = array();

        foreach ($rows as $osef) {
            $data[] = array(
                'id' => $osef->getId(),
                'creneau' => $osef->getCreneau(),
                'player' => array(
                    'id' => $osef->getIdPlayer()->getId(),
                    'username' => $osef->getIdPlayer()->getUsername(),
                    'img' => $userEnt::getGravatar($osef->getIdPlayer()->getEmail()),
                    'team' => 'NO'
                )
            );
        }

        if($aggreg) {
            return $this->aggregateGames($data);
        } else {
            return $data;
        }
    }

    private function aggregateGames($data)
    {
        $return = array();

        foreach($data as $row) {
            if(!isset($return[$row['creneau']])) {
                $cr = substr($row['creneau'],0,2).'h'.substr($row['creneau'],2,2);
                $return[$row['creneau']] = array('creneau' => $cr, 'players' => array());
            }
            $row['player']['creneau_id'] = $row['id'];
            $return[$row['creneau']]['players'][] = $row['player'];
        }

        return $this->doMatchMaking($return);
    }

    private function doMatchMaking($data)
    {
        foreach ($data as &$creneau) {
            if(sizeof($creneau['players']) == 4) {
                $players = array();
                foreach ($creneau['players'] as &$pl) {
                    $players[] = $pl['id'];
                }
                $teams = $this->_em->getRepository('BabyStatBundle:BabyGame')->matchMaking($players);

                foreach ($creneau['players'] as &$pl) {
                    if(in_array($pl['username'], $teams[0])) {
                        $pl['team'] = 0;
                    } else {
                        $pl['team'] = 1;
                    }
                }
            }
        }

        return $data;
    }

}
