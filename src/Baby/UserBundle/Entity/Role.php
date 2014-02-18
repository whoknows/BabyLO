<?php

namespace Baby\UserBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="baby_roles")
 */
class Role implements RoleInterface
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="name", type="string", length=255)
	 */
	protected $name;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="role")
	 */
	private $user;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->user = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getRole()
	{
		return $this->getName();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return Role
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Add user
	 *
	 * @param \Baby\UserBundle\Entity\User $user
	 * @return Roles
	 */
	public function addUser(\Baby\UserBundle\Entity\User $user)
	{
		$this->user[] = $user;

		return $this;
	}

	/**
	 * Remove user
	 *
	 * @param \Baby\UserBundle\Entity\User $user
	 */
	public function removeUser(\Baby\UserBundle\Entity\User $user)
	{
		$this->user->removeElement($user);
	}

	/**
	 * Get user
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUser()
	{
		return $this->user;
	}

}
