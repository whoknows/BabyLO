<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Baby\StatBundle\Toolbox;

class StatController extends Controller {

	public function indexAction() {
		$session = new Session();
		$session->start();

		return $this->render('BabyStatBundle:Stat:index.html.twig', array(
			'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), 5),
			'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(),6),
			'user' => $session->get('user', 'null'),
			'rank' => $session->get('rank', -1)
		));
	}

	public function playerAction() {
		$session = new Session();
		$session->start();

		return $this->render('BabyStatBundle:Stat:player.html.twig', array(
			'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(), null, true),
			'user' => $session->get('user', 'null'),
			'rank' => $session->get('rank', -1)
		));
	}

	public function playerstatAction() {
		$session = new Session();
		$session->start();

		if($session->get('user') != null){

			$em = $this->getDoctrine()->getManager();

			$stat = $em->getRepository('BabyStatBundle:BabyStats')->findBy(array('player' => $em->getRepository('BabyStatBundle:BabyPlayer')->findBy(array('name' => $session->get('user')))))[0];

			return $this->render('BabyStatBundle:Stat:playerstat.html.twig', array(
				'user' => $session->get('user', 'null'),
				'rank' => $session->get('rank', -1),
				'stat' => $stat
			));
		} else {
			return $this->redirect($this->generateUrl('babystat_accueil'));
		}
	}

	public function morestatAction() {
		$id = $this->getRequest()->get('playerId');

		$response = new Response(json_encode(Toolbox\Player::getPlayerData($id, $this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function gameAction() {
		$session = new Session();
		$session->start();

		$filters = array();

		return $this->render('BabyStatBundle:Stat:game.html.twig', array(
			'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), null, $filters),
			'user' => $session->get('user', 'null'),
			'rank' => $session->get('rank', -1)
		));
	}

	public function nbgameAction() {
		$response = new Response(json_encode(Toolbox\Game::getGameCount($this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function addgameAction() {
		$session = new Session();
		$session->start();

		if($session->get('user') !== null || $session->get('rank') >= 1){
			return $this->render('BabyStatBundle:Stat:addgame.html.twig', array(
				'players' => $playerrepo = $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyPlayer')->findAll(),
				'user' => $session->get('user', 'null'),
				'rank' => $session->get('rank', -1)
			));
		} else {
			return $this->redirect($this->generateUrl('babystat_accueil'));
		}

	}

	public function savegameAction() {
		$request = $this->getRequest();

		$em = $this->getDoctrine()->getManager();

		try {
			$game = new \Baby\StatBundle\Entity\BabyGame();
			$game->setDate(new \DateTime($request->get('date')));
			$game->setScoreTeam1($request->get('score1'));
			$game->setScoreTeam2($request->get('score2'));

			$em->persist($game);
			$em->flush();

			for($i=1; $i<=2; $i++){
				for($j=1; $j<=2; $j++){
					$played = new \Baby\StatBundle\Entity\BabyPlayed();
					$played->setIdGame($em->getRepository('BabyStatBundle:BabyGame')->find($game->getId()));
					$played->setIdPlayer($em->getRepository('BabyStatBundle:BabyPlayer')->find($request->get('joueur'.$j.'equipe'.$i)));
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

	public function loginAction() {
		$session = new Session();
		$session->start();

		$return = array(
			"type" => "",
			"msg" => ""
		);

		$login = $this->getRequest()->get('login',null);
		$password = $this->getRequest()->get('password',null);

		if($session->get('user', null) !== null){
			$return = array(
				"type" => "warning",
				"msg" => "Déjà connecté"
			);
		} elseif($login === "" || $password === "") {
			$return = array(
				"type" => "error",
				"msg" => "Spécifiez un login/password."
			);
		} else {
			$plr = $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyPlayer');
			$usr = $plr->findBy(array('name' => $login, 'password' => sha1($password)));

			if(sizeof($usr) != 1){
				$return = array(
					"type" => "error",
					"msg" => "Login/password invalides."
				);
			} else {
				$session->set('user', $login);
				$session->set('rank', $usr[0]->getRank());

				$return = array(
					"type" => "success",
					"msg" => "ok"
				);
			}
		}

		$response = new Response(json_encode($return));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function logoutAction() {
		$session = new Session();
		$session->clear();
		$session->remove('user');
		$session->remove('rank');

		return $this->redirect($this->generateUrl('babystat_accueil'));
	}
}
