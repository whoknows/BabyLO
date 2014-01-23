<?php

namespace Baby\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;

class StatController extends Controller {

	public function indexAction() {

		return $this->render('BabyStatBundle:Stat:index.html.twig', array(
			'games' => \Baby\StatBundle\Entity\GameRepository::getGameList($this->getDoctrine()->getManager(), 5),
			'players' => \Baby\StatBundle\Entity\PlayerRepository::getPlayerList($this->getDoctrine()->getManager(),6)
		));
	}

	public function playerAction() {
		return $this->render('BabyStatBundle:Stat:player.html.twig', array(
			'players' => \Baby\StatBundle\Entity\PlayerRepository::getPlayerList($this->getDoctrine()->getManager(), null, true),
		));
	}

	public function morestatAction() {
		$id = $this->getRequest()->get('playerId');

		$response = new Response(json_encode(\Baby\StatBundle\Entity\PlayerRepository::getPlayerData($id, $this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function gameAction() {
		return $this->render('BabyStatBundle:Stat:game.html.twig', array(
			'games' => \Baby\StatBundle\Entity\GameRepository::getGameList($this->getDoctrine()->getManager())
		));
	}

	public function nbgameAction() {
		$response = new Response(json_encode(\Baby\StatBundle\Entity\GameRepository::getGameCount($this->getDoctrine()->getManager())));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

	public function addgameAction() {
		$session = new Session();
		$session->start();

		$postPwd = $this->getRequest()->get('passwd_addgame', null);

		$pwd = '42f0ec45b09b4ba1db89586ed8e0ed8ea6b836ea';

		if($session->get('password') == $pwd || sha1($postPwd) == $pwd){
			$session->set('password', $pwd);
			return $this->render('BabyStatBundle:Stat:addgame.html.twig', array(
				'players' => $playerrepo = $this->getDoctrine()->getManager()->getRepository('BabyStatBundle:Player')->findAll(),
			));
		} else {
			return $this->render('BabyStatBundle:Stat:addgamelogin.html.twig',array(
				'msg' => $postPwd !== null ? 'Erreur : Mauvais mot de passe' : 'login'
			));
		}

	}

	public function savegameAction() {
		$request = $this->getRequest();

		$em = $this->getDoctrine()->getManager();

		try {

			$game = new \Baby\StatBundle\Entity\Game();
			$game->setDate(new \DateTime($request->get('date')));
			$game->setScoreTeam1($request->get('score1'));
			$game->setScoreTeam2($request->get('score2'));

			$em->persist($game);
			$em->flush();

			for($i=1; $i<=2; $i++){
				for($j=1; $j<=2; $j++){
					$played = new \Baby\StatBundle\Entity\Played();
					$played->setIdGame($game->getId());
					$played->setIdPlayer($request->get('joueur'.$j.'equipe'.$i));
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
}
