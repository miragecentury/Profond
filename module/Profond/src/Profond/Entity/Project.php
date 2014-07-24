<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Project {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Profond\Entity\User", inversedBy="Projects")
     */
    protected $User;

    /**
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $share = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $shareAll = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateCrea;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateLastMod;

    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Job",mappedBy="Project")
     */
    protected $Jobs;
    
    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Config",mappedBy="project")
     */
    protected $Configs;

    public function getId() {
        return $this->id;
    }

    public function getUser() {
        return $this->User;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getShare() {
        return $this->share;
    }

    public function getShareAll() {
        return $this->shareAll;
    }

    public function getDateCrea() {
        return $this->dateCrea;
    }

    public function getDateLastMod() {
        return $this->dateLastMod;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setUser(User $user) {
        $this->User = $user;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setShare($share) {
        $this->share = $share;
    }

    public function setShareAll($shareAll) {
        $this->shareAll = $shareAll;
    }

    public function setDateCrea($dateCrea) {
        $this->dateCrea = $dateCrea;
    }

    public function setDateLastMod($dateLastMod) {
        $this->dateLastMod = $dateLastMod;
    }

}
