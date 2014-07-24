<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class User {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $FullName;

    /** @ORM\Column(type="string") */
    protected $Email;

    /** @ORM\Column(type="boolean") */
    protected $Email_verif = false;

    /** @ORM\Column(type="string") */
    protected $Salt;

    /** @ORM\Column(type="string") */
    protected $Password;

    /** @ORM\Column(type="boolean") */
    protected $Active;

    /** @ORM\Column(type="boolean") */
    protected $Admin = false;

    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Project",mappedBy="User")
     */
    protected $Projects;

    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Job",mappedBy="User")
     */
    protected $Jobs;

    // getters/setters

    public function getId() {
        return $this->id;
    }

    public function getFullName() {
        return $this->FullName;
    }

    public function getEmail() {
        return $this->Email;
    }

    public function getSalt() {
        return $this->Salt;
    }

    public function getPassword() {
        return $this->Password;
    }

    public function getActive() {
        return $this->Active;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setFullName($FullName) {
        $this->FullName = $FullName;
    }

    public function setEmail($Email) {
        $this->Email = $Email;
    }

    public function setSalt($Salt) {
        $this->Salt = $Salt;
    }

    public function setPassword($Password) {
        $this->Password = $Password;
    }

    public function setActive($Active) {
        $this->Active = $Active;
    }

    public function getEmail_verif() {
        return $this->Email_verif;
    }

    public function setEmail_verif($Email_verif) {
        $this->Email_verif = $Email_verif;
    }

    public function getProjects() {
        return $this->Projects;
    }

    public function getAdmin() {
        return $this->Admin;
    }

    public function setAdmin($Admin) {
        $this->Admin = $Admin;
    }

}
