<?php

namespace Baby\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Baby\UserBundle\Entity\User;
use Baby\UserBundle\Entity\Role;

class SuperAdmin implements FixtureInterface {

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager) {
		$noms = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN');
		$roles = array();

		foreach ($noms as $nom) {
			$roles[$nom] = new Role;
			$roles[$nom]->setName($nom);

			$manager->persist($roles[$nom]);
		}

		$userAdmin = new User();
		$userAdmin->setUsername('admin');
		$userAdmin->setSalt('');
		$userAdmin->setPassword(hash('sha512', 'secret'));
		$userAdmin->setPosition('');
		$userAdmin->setEnabled(0);
		$userAdmin->addRole($roles['ROLE_SUPER_ADMIN']);

		$manager->persist($userAdmin);
		$manager->flush();
	}

}
