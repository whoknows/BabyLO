<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use \Baby\StatBundle\Entity;

class StatController extends Controller
{

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('BabyStatBundle:Stat:index.html.twig', array(
            'games' => $em->getRepository('BabyStatBundle:BabyGame')->getGameList(5),
            'players' => $em->getRepository('BabyUserBundle:User')->getPlayerList(4),
            'tops' => $em->getRepository('BabyUserBundle:User')->getDailyTops(),
        ));
    }

    public function playerAction()
    {
        return $this->render('BabyStatBundle:Stat:player.html.twig', array(
            'players' => $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->getPlayerList(null, true),
        ));
    }

    public function playerstatAction()
    {
        return $this->render('BabyStatBundle:Stat:playerstat.html.twig', array(
            'user' => $this->getUser()->getUsername(),
            'stat' => $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->getAllStats($this->getUser()->getId(), false)
        ));
    }

    public function playerstatgraphAction()
    {
        $request = Request::createFromGlobals();
        $function = 'get' . strtoupper($request->request->get('action'));
        $agregate = $request->request->get('agregate', 0);

        $data = array(
            'date' => array(),
            'data' => array()
        );

        foreach ($this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->$function($this->getUser()->getId()) as $row) {
            $data['date'][] = $row['date']->format('d-m-Y');
            if ($agregate == 1) {
                $prevVal = isset($data['data'][sizeof($data['data']) - 1]) ? $data['data'][sizeof($data['data']) - 1] : 0;
                $data['data'][] = $prevVal + intval($row['ct']);
            } else {
                $data['data'][] = intval($row['ct']);
            }
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function morestatAction()
    {
        $request = Request::createFromGlobals();

        $id = $request->request->get('playerId');
        $dt = $request->request->get('date', 'now');
        $ag = $request->request->get('aggregate', 0);

        $usr = $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User');

        $response = new Response(json_encode(array(
            'graph' => $usr->getPlayerData($id, $dt, $ag),
            'stats' => $usr->getAllStats($id, true, $dt)
        )));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function gameAction()
    {
        $request = Request::createFromGlobals();
        $filters = array(
            "date" => $request->request->get('date', date('d-m-Y')),
            "player" => $request->request->get('joueur', null),
        );

        if ($filters["player"] == "") {
            $filters["player"] = null;
        }

        return $this->render('BabyStatBundle:Stat:game.html.twig', array(
            'games' => $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyGame')->getGameList(null, $filters),
            'date' => $filters['date'],
            'player' => $filters['player'],
        ));
    }

    public function scheduleAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userEnt = $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User');

        $tmp = $em->getRepository('BabyStatBundle:BabySchedule')->findBy(array('date' => new \DateTime(date('Y-m-d'))));

        $data = array();

        foreach ($tmp as $osef) {
            $data[] = array(
                'id' => $osef->getId(),
                'creneau' => $osef->getCreneau(),
                'player' => array(
                    'id' => $osef->getIdPlayer()->getId(),
                    'username' => $osef->getIdPlayer()->getUsername(),
                    'img' => $userEnt::getGravatar($osef->getIdPlayer()->getEmail()),
                )
            );
        }

        return $this->render('BabyStatBundle:Stat:schedule.html.twig', array(
            'data' => $data,
        ));
    }

    public function changeScheduleAction()
    {
        $em = $this->getDoctrine()->getManager();

        $data = array();

        $request = Request::createFromGlobals();

        $id = $request->request->get('id');

        if ($id === null) {
            $schedule = new Entity\BabySchedule();
            $schedule->setDate(new \DateTime(date('Y-m-d')));
            $schedule->setCreneau($request->request->get('creneau'));
            $schedule->setIdPlayer($this->getUser());
            $em->persist($schedule);
            $em->flush();

            $userEnt = $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User');

            $data = array(
                'id' => $schedule->getId(),
                'img' => $userEnt::getGravatar($this->getUser()->getEmail()),
                'username' => $this->getUser()->getUsername()
            );
        } else {
            $schedule = $em->getRepository('BabyStatBundle:BabySchedule')->find($id);
            $em->remove($schedule);
            $em->flush();
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function nbgameAction()
    {
        $response = new Response(json_encode($this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyGame')->getGameCount()));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function addgameAction()
    {
        $userEnt = $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User');
        $players = array();

        foreach ($userEnt->getStandardUserList() as &$player) {
            $players[] = array(
                'id' => $player->getId(),
                'img' => $userEnt::getGravatar($player->getEmail()),
                'name' => $player->getUsername(),
                'position' => $player->getPosition()
            );
        }

        return $this->render('BabyStatBundle:Stat:addgame.html.twig', array(
            'players' => $players
        ));
    }

    public function savegameAction()
    {
        $request = Request::createFromGlobals();

        $em = $this->getDoctrine()->getManager();

        try {
            $game = new Entity\BabyGame();
            $game->setDate(new \DateTime($request->request->get('date')));
            $game->setScoreTeam1($request->request->get('score1'));
            $game->setScoreTeam2($request->request->get('score2'));

            $em->persist($game);
            $em->flush();
            for ($i = 1; $i <= 2; $i++) {
                for ($j = 1; $j <= 2; $j++) {
                    $played = new Entity\BabyPlayed();
                    $played->setIdGame($em->getRepository('BabyStatBundle:BabyGame')->find($game->getId()));
                    $played->setIdPlayer($em->getRepository('BabyUserBundle:User')->find($request->request->get('joueur' . $j . 'equipe' . $i)));
                    $played->setTeam($i);
                    $em->persist($played);
                    $em->flush();
                }
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }

        return new Response('ok');
    }

    public function delgameAction()
    {
        $em = $this->getDoctrine()->getManager();
        $id = Request::createFromGlobals()->request->get('id');

        $game = $em->getRepository('BabyStatBundle:BabyGame')->find($id);

        $played = $em->getRepository('BabyStatBundle:BabyPlayed')->findBy(array('idGame' => $id));

        foreach ($played as $p) {
            $em->remove($p);
            $em->flush();
        }

        $em->remove($game);
        $em->flush();

        return new Response('ok');
    }

    public function matchmakerAction()
    {
        $userEnt = $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User');
        $players = array();

        foreach ($userEnt->getStandardUserList() as &$player) {
            $players[] = array(
                'id' => $player->getId(),
                'img' => $userEnt::getGravatar($player->getEmail()),
                'username' => $player->getUsername(),
            );
        }

        return $this->render('BabyStatBundle:Stat:matchmaker.html.twig', array(
            'players' => $players,
        ));
    }

    public function matchmakingAction()
    {
        $players = Request::createFromGlobals()->request->get('ids', array());

        $response = new Response(json_encode($this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyGame')->matchMaking($players)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function useradminAction()
    {
        $em = $this->getDoctrine()->getManager();

        return $this->render('BabyStatBundle:Stat:useradmin.html.twig', array(
            'users' => $em->getRepository('BabyUserBundle:User')->getStandardUserList(-1),
            'roles' => $em->getRepository('BabyUserBundle:Role')->findAll()
        ));
    }

    public function saveuserAction()
    {
        $userData = array(
            'id' => $this->getRequest()->get('id', null),
            'enabled' => $this->getRequest()->get('enabled', 0),
            'position' => $this->getRequest()->get('position', 'Avant'),
            'roles' => $this->getRequest()->get('roles', array()),
            'username' => $this->getRequest()->get('username', null),
            'password' => $this->getRequest()->get('password', 'secret'),
            'email' => $this->getRequest()->get('email', null),
        );

        $em = $this->getDoctrine()->getManager();

        if ($userData['id'] === null) {
            $pl = new \Baby\UserBundle\Entity\User();
            $pl->setUsername($userData['username']);
            $pl->setPassword(hash('sha512', $userData['password']));
            $pl->setSalt('');
        } else {
            $pl = $em->getRepository('BabyUserBundle:User')->find($userData['id']);
        }

        $pl->setEnabled($userData['enabled']);
        $pl->setPosition($userData['position']);
        $pl->setEmail($userData['email']);
        $pl->removeAllRoles();

        foreach ($userData['roles'] as $rid) {
            $rl = $em->getRepository('BabyUserBundle:Role')->find($rid);
            $pl->addRole($rl);
        }

        if ($userData['id'] === null) {
            $em->persist($pl);
        }

        $em->flush();

        return new Response('OK');
    }
}
