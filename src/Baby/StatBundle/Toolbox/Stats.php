<?php

namespace Baby\StatBundle\Toolbox;

class Stats {

	public static function calculData($em, $player) {
		$gamerepo   = $em->getRepository('BabyStatBundle:BabyGame');
		$playedrepo = $em->getRepository('BabyStatBundle:BabyPlayed');
		//
	}

}
