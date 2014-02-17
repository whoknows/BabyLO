<?php

namespace Baby\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="baby_user")
 * @ORM\Entity(repositoryClass="Baby\UserBundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable {

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="username", type="string", length=255)
	 */
	private $username;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="position", type="string", length=255, nullable=false)
	 */
	private $position;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="password", type="string", length=255)
	 */
	private $password;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="salt", type="string", length=255)
	 */
	private $salt;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="enabled", type="integer", nullable=false)
	 */
	private $enabled = '0';

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 *
	 * @ORM\ManyToMany(targetEntity="Role", inversedBy="user")
	 * @ORM\JoinTable(name="baby_user_role",
	 *   joinColumns={
	 *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 *   },
	 *   inverseJoinColumns={
	 *     @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	 *   }
	 * )
	 */
	private $role;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->role = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Set username
	 *
	 * @param string $username
	 * @return User
	 */
	public function setUsername($username) {
		$this->username = $username;

		return $this;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * Set position
	 *
	 * @param string $position
	 * @return User
	 */
	public function setPosition($position) {
		$this->position = $position;

		return $this;
	}

	/**
	 * Get position
	 *
	 * @return string
	 */
	public function getPosition() {
		return $this->position;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 * @return User
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	/**
	 * Get password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * Set salt
	 *
	 * @param string $salt
	 * @return User
	 */
	public function setSalt($salt) {
		$this->salt = $salt;

		return $this;
	}

	/**
	 * Get salt
	 *
	 * @return string
	 */
	public function getSalt() {
		return $this->salt;
	}

	/**
	 * Set enabled
	 *
	 * @param integer $enabled
	 * @return User
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;

		return $this;
	}

	/**
	 * Get enabled
	 *
	 * @return integer
	 */
	public function getEnabled() {
		return $this->enabled;
	}

	/**
	 * Set roles
	 *
	 * @param string $roles
	 * @return User
	 */
	public function setRoles($roles) {
		$this->role = $roles;

		return $this;
	}

	/**
	 * Get roles
	 *
	 * @return array
	 */
	public function getRoles() {
		return $this->role->toArray();
	}

	public function eraseCredentials() {

	}

	/**
	 * Has role
	 *
	 * @param \Baby\UserBundle\Entity\Roles $role
	 * @return Bool
	 */
	public function hasRole(\Baby\UserBundle\Entity\Role $role) {
		foreach ($this->role as $r) {
			if ($r === $role) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove all roles
	 *
	 * @param \Baby\UserBundle\Entity\Roles $role
	 */
	public function removeAllRoles() {
		$this->role->clear();
	}

	/**
	 * Get role
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * Add roles
	 *
	 * @param \Baby\UserBundle\Entity\Roles $roles
	 * @return User
	 */
	public function addRole(\Baby\UserBundle\Entity\Role $roles) {
		$this->role[] = $roles;

		return $this;
	}

	/**
	 * Remove roles
	 *
	 * @param \Baby\UserBundle\Entity\Roles $roles
	 */
	public function removeRole(\Baby\UserBundle\Entity\Role $roles) {
		$this->role->removeElement($roles);
	}

	/**
	 * Serializes the content of the current User object
	 * @return string
	 */
	public function serialize() {
		return \json_encode(
				array($this->username, $this->password, $this->salt,
					$this->role, $this->id));
	}

	/**
	 * Unserializes the given string in the current User object
	 * @param serialized
	 */
	public function unserialize($serialized) {
		list($this->username, $this->password, $this->salt,
				$this->roles, $this->id) = \json_decode(
				$serialized);
	}

}
