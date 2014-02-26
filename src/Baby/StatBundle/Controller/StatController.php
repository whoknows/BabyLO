<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
		$function = 'get' . strtoupper($this->getRequest()->get('action'));
		$agregate = $this->getRequest()->get('agregate', 1);

		$data = array(
			'date' => array(),
			'data' => array()
		);

		foreach ($this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->$function($this->getUser()->getId()) as $row) {
			$data['date'][] = $row['date']->format('d-m-Y');
			if($agregate == 1){
				$prevVal = isset($data['data'][sizeof($data['data'])-1]) ? $data['data'][sizeof($data['data'])-1] : 0;
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
		$id = $this->getRequest()->get('playerId');
		$dt = $this->getRequest()->get('date', 'now');
		$ag = $this->getRequest()->get('aggregate', 0);

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
		$filters = array(
			"date" => $this->getRequest()->get('date', date('d-m-Y')),
			"player" => $this->getRequest()->get('joueur', NULL),
		);

		if ($filters["player"] == "") {
			$filters["player"] = NULL;
		}

		return $this->render('BabyStatBundle:Stat:game.html.twig', array(
					'games' => $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyGame')->getGameList(null, $filters),
					'date' => $filters['date'],
					'player' => $filters['player'],
		));
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

		foreach($userEnt->getStandardUserList() as &$player) {
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
		$request = $this->getRequest();

		$em = $this->getDoctrine()->getManager();

		try {
			$game = new \Baby\StatBundle\Entity\BabyGame();
			$game->setDate(new \DateTime($request->get('date')));
			$game->setScoreTeam1($request->get('score1'));
			$game->setScoreTeam2($request->get('score2'));

			$em->persist($game);
			$em->flush();
			for ($i = 1; $i <= 2; $i++) {
				for ($j = 1; $j <= 2; $j++) {
					$played = new \Baby\StatBundle\Entity\BabyPlayed();
					$played->setIdGame($em->getRepository('BabyStatBundle:BabyGame')->find($game->getId()));
					$played->setIdPlayer($em->getRepository('BabyUserBundle:User')->find($request->get('joueur' . $j . 'equipe' . $i)));
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
		$id = $this->getRequest()->get('id');

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

		foreach($userEnt->getStandardUserList() as &$player) {
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
		$players = $this->getRequest()->get('ids', array());

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
			'id' => $this->getRequest()->get('id', NULL),
			'enabled' => $this->getRequest()->get('enabled', 0),
			'position' => $this->getRequest()->get('position', 'Avant'),
			'roles' => $this->getRequest()->get('roles', array()),
			'username' => $this->getRequest()->get('username', NULL),
			'password' => $this->getRequest()->get('password', 'secret'),
			'email' => $this->getRequest()->get('email', NULL),
		);

		$em = $this->getDoctrine()->getManager();

		if ($userData['id'] === NULL) {
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

		if ($userData['id'] === NULL) {
			$em->persist($pl);
		}

		$em->flush();

		return new Response('OK');
	}

}
