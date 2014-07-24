<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Machine {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $connectionType;

    /**
     * @ORM\Column(type="text")
     */
    protected $config;

    /**
     * @ORM\Column(type="string")
     */
    protected $label;

    /**
     * @ORM\Column(type="string")
     */
    protected $keypass;

    /**
     * @ORM\Column(type="text")
     */
    protected $cpu;

    /** @ORM\Column(type="boolean") */
    protected $ready;

    /** @ORM\Column(type="boolean") */
    protected $installIncomming;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dateCrea;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastping;

    /**
     * @ORM\OneToMany(targetEntity="Profond\Entity\Job",mappedBy="Machine")
     */
    protected $Jobs;

    public function getId() {
        return $this->id;
    }

    public function getConnectionType() {
        return $this->connectionType;
    }

    public function getConfig() {
        return json_decode($this->config, true);
    }

    public function getLabel() {
        return $this->label;
    }

    public function getCpu() {
        return json_decode($this->cpu, true);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setConnectionType($connectionType) {
        $this->connectionType = $connectionType;
    }

    public function setConfig($config) {
        $this->config = json_encode($config);
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setCpu($cpu) {
        $this->cpu = json_encode($cpu);
    }

    public function getReady() {
        return $this->ready;
    }

    public function getInstallIncomming() {
        return $this->installIncomming;
    }

    public function getDateCrea() {
        return $this->dateCrea;
    }

    public function setReady($ready) {
        $this->ready = $ready;
    }

    public function setInstallIncomming($installIncomming) {
        $this->installIncomming = $installIncomming;
    }

    public function setDateCrea($dateCrea) {
        $this->dateCrea = $dateCrea;
    }

    public function getKeypass() {
        return $this->keypass;
    }

    public function setKeypass($keypass) {
        $this->keypass = $keypass;
    }

        
    public function getLastping() {
        return $this->lastping;
    }

    public function setLastping($lastping) {
        $this->lastping = $lastping;
    }

}
