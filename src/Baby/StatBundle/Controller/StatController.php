<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Baby\StatBundle\Toolbox;

class StatController extends Controller {

	public function indexAction() {
		$session = new Session();

		return $this->render('BabyStatBundle:Stat:index.html.twig', array(
			'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), 5),
			'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(),6),
			'tops' => Toolbox\Player::getDailyTops($this->getDoctrine()->getManager()),
		));
	}

	public function playerAction() {
		$session = new Session();

		return $this->render('BabyStatBundle:Stat:player.html.twig', array(
			'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(), null, true),
		));
	}

	public function playerstatAction() {
		$usr = $this->getUser();
		if($usr){

			$id = $usr->getId();

			$st = Toolbox\Stats::getAllStats($id);

			if(sizeof($st) == 0){
				$st = array();
			} else {
				$st['ratio'] = round($st['nbWin'] / $st['nbGames'],2);
			}

			return $this->render('BabyStatBundle:Stat:playerstat.html.twig', array(
				'user' => $this->getUser()->getUsername(),
				'stat' => $st
			));
		} else {
			return $this->redirect($this->generateUrl('babystat_accueil'));
		}
	}

	public function playerstatgraphAction() {
		$function = 'get'.strtoupper($this->getRequest()->get('action'));

		$response = new Response(json_encode(Toolbox\Stats::$function($this->getUser()->getId())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function morestatAction() {
		$id = $this->getRequest()->get('playerId');
		$em = $this->getDoctrine()->getManager();
		$st = Toolbox\Stats::getAllStats($id);

		if(sizeof($st) == 0){
			$st = array();
		} else {
			$st['ratio'] = round($st['nbWin'] / $st['nbGames'],2);
		}

		$response = new Response(json_encode(array(
			'graph' => Toolbox\Player::getPlayerData($id, $em),
			'stats' => $st
		)));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function gameAction() {
		$session = new Session();

		$filters = array(
			"date" => $this->getRequest()->get('date', date('d-m-Y')),
		);

		return $this->render('BabyStatBundle:Stat:game.html.twig', array(
			'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), null, $filters),
			'date' => $filters['date'],
		));
	}

	public function nbgameAction() {
		$response = new Response(json_encode(Toolbox\Game::getGameCount($this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function addgameAction() {
		$session = new Session();

		if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
			throw new AccessDeniedHttpException('Accès limité aux admin');
		}

		return $this->render('BabyStatBundle:Stat:addgame.html.twig', array(
			'players' => $playerrepo = $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:BabyPlayer')->findBy(array(),array('name' => 'ASC')),
			'user' => $session->get('user', 'null'),
			'rank' => $session->get('rank', -1)
		));
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

	/*public function loginAction() {
		if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return $this->redirect($this->generateUrl('sdzblog_accueil'));
		}

		$request = $this->getRequest();
		$session = $request->getSession();

		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		} else {
			$error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
			$session->remove(SecurityContext::AUTHENTICATION_ERROR);
		}

		return $this->render('BabyUserBundle:Security:login.html.twig', array(
			'last_username' => $session->get(SecurityContext::LAST_USERNAME),
			'error'         => $error,
		));

		$session = new Session();

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
	}*/

	public function logoutAction() {
		$session = new Session();
		$session->clear();
		$session->remove('user');
		$session->remove('rank');

		return $this->redirect($this->generateUrl('babystat_accueil'));
	}
}
