<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Baby\StatBundle\Toolbox;

class StatController extends Controller {

	public function indexAction() {
		return $this->render('BabyStatBundle:Stat:index.html.twig', array(
					'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), 5),
					'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(), 6),
					'tops' => Toolbox\Player::getDailyTops($this->getDoctrine()->getManager()),
		));
	}

	public function playerAction() {
		return $this->render('BabyStatBundle:Stat:player.html.twig', array(
					'players' => Toolbox\Player::getPlayerList($this->getDoctrine()->getManager(), null, true),
		));
	}

	public function playerstatAction() {
		$usr = $this->getUser();
		if ($usr) {

			$id = $usr->getId();

			$st = Toolbox\Stats::getAllStats($id, false);

			if (sizeof($st) == 0) {
				$st = array();
			} else {
				$st['ratio'] = round($st['nbWin'] / $st['nbGames'], 2);
			}

			return $this->render('BabyStatBundle:Stat:playerstat.html.twig', array(
						'user' => $this->getUser()->getUsername(),
						'stat' => $st
			));
		} else {
			return $this->render('BabyUserBundle:Security:login.html.twig', array(
						'error' => array('message' => 'Vous devez vous connecter pour accéder à cette page.'),
			));
		}
	}

	public function playerstatgraphAction() {
		$function = 'get' . strtoupper($this->getRequest()->get('action'));

		$response = new Response(json_encode(Toolbox\Stats::$function($this->getUser()->getId())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function morestatAction() {
		$id = $this->getRequest()->get('playerId');
		$dt = $this->getRequest()->get('date', 'now');

		$em = $this->getDoctrine()->getManager();
		$st = Toolbox\Stats::getAllStats($id, true, $dt);

		if (sizeof($st) == 0) {
			$st = array();
		} else {
			if ($st['nbGames'] == 0) {
				$st['ratio'] = 0;
			} else {
				$st['ratio'] = round($st['nbWin'] / $st['nbGames'], 2);
			}
		}

		$response = new Response(json_encode(array(
					'graph' => Toolbox\Player::getPlayerData($id, $em, $dt),
					'stats' => $st
		)));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function gameAction() {
		$filters = array(
			"date" => $this->getRequest()->get('date', date('d-m-Y')),
			"player" => $this->getRequest()->get('joueur', NULL),
		);

		if($filters["player"] == "") {
			$filters["player"] = NULL;
		}

		return $this->render('BabyStatBundle:Stat:game.html.twig', array(
					'games' => Toolbox\Game::getGameList($this->getDoctrine()->getManager(), null, $filters),
					'date' => $filters['date'],
					'player' => $filters['player'],
		));
	}

	public function nbgameAction() {
		$response = new Response(json_encode(Toolbox\Game::getGameCount($this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function addgameAction() {
		return $this->render('BabyStatBundle:Stat:addgame.html.twig', array(
					'players' => $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->findBy(array(), array('username' => 'ASC')),
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

	public function delgameAction() {
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

	public function matchmakerAction() {
		return $this->render('BabyStatBundle:Stat:matchmaker.html.twig', array(
					'players' => $this->getDoctrine()->getManager()->getRepository('BabyUserBundle:User')->findBy(array('enabled' => 1), array('username' => 'ASC')),
		));
	}

	public function matchmakingAction() {
		$players = $this->getRequest()->get('ids', array());

		$response = new Response(json_encode(Toolbox\Game::matchMaking($players, $this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function useradminAction() {
		$em = $this->getDoctrine()->getManager();

		return $this->render('BabyStatBundle:Stat:useradmin.html.twig', array(
					'users' => $em->getRepository('BabyUserBundle:User')->findBy(array(), array('username' => 'ASC')),
					'roles' => $em->getRepository('BabyUserBundle:Role')->findAll()
		));
	}

	public function saveuserAction() {
		$userData = array(
			'id' => $this->getRequest()->get('id', NULL),
			'enabled' => $this->getRequest()->get('enabled', 0),
			'position' => $this->getRequest()->get('position', 'Avant'),
			'roles' => $this->getRequest()->get('roles', array()),
			'username' => $this->getRequest()->get('username', NULL),
			'password' => $this->getRequest()->get('password', NULL)
		);

		$em = $this->getDoctrine()->getManager();

		if ($userData['id'] === NULL) {
			$pl = new \Baby\UserBundle\Entity\User();
			$pl->setUsername($userData['username']);
			$pl->setPassword(hash('sha512', 'secret'));
			$pl->setSalt('');
		} else {
			$pl = $em->getRepository('BabyUserBundle:User')->find($userData['id']);
		}

		$pl->setEnabled($userData['enabled']);
		$pl->setPosition($userData['position']);
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
