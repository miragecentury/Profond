<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Executable {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $label;

    /** @ORM\Column(type="boolean") */
    protected $onlyAdmin = true;

    /**
     * @ORM\Column(type="string")
     */
    protected $exec;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $install;

    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Job",mappedBy="Executable")
     */
    protected $Jobs;

    public function getId() {
        return $this->id;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getOnlyAdmin() {
        return $this->onlyAdmin;
    }

    public function getExec() {
        return $this->exec;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setOnlyAdmin($onlyAdmin) {
        $this->onlyAdmin = $onlyAdmin;
    }

    public function setExec($exec) {
        $this->exec = $exec;
    }

    public function getJobs() {
        return $this->Jobs;
    }

}
