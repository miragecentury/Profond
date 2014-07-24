<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Job {

    const STATUS_WAITING_DATE = "wainting_date";
    const STATUs_LAUNCHING = "launching";
    const STATUS_WAITING_RESOURCE = "wainting_resource";
    const STATUS_LAUNCHING_REMOTE = "launching_remote";
    const STATUS_RUNNING = "running";
    const STATUS_STOP = "stop";
    const STATUS_RECEIVE_RESULT = "receive_result";
    const STATUS_END = "end";
    const STATUS_ERR = "err";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="Jobs")
     */
    protected $Project;

    /**
     * @ORM\ManyToOne(targetEntity="Executable", inversedBy="Jobs")
     */
    protected $Executable;

    /**
     * @ORM\ManyToOne(targetEntity="Profond\Entity\User", inversedBy="Jobs")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Machine", inversedBy="Jobs")
     */
    protected $Machine;

    /** @ORM\Column(type="string") */
    protected $status;

    /** @ORM\Column(type="string",nullable=true) */
    protected $schedid;

    /** @ORM\Column(type="string") */
    protected $label;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $DatetimeCrea;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $DatetimeStartExec;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $DatetimeEndExec;

    /**
     * @ORM\Column(type="text")
     */
    protected $data;

    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @return Project
     */
    public function getProject() {
        return $this->Project;
    }

    /**
     * 
     * @return Executable
     */
    public function getExecutable() {
        return $this->Executable;
    }

    public function getUser() {
        return $this->user;
    }

    /**
     * 
     * @return Machine
     */
    public function getMachine() {
        return $this->Machine;
    }

    public function getLabel() {
        return $this->label;
    }

    public function getDatetimeCrea() {
        return $this->DatetimeCrea;
    }

    public function getDatetimeStartExec() {
        return $this->DatetimeStartExec;
    }

    public function getDatetimeEndExec() {
        return $this->DatetimeEndExec;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProject($Project) {
        $this->Project = $Project;
    }

    public function setExecutable($Executable) {
        $this->Executable = $Executable;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setMachine($Machine) {
        $this->Machine = $Machine;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function setDatetimeCrea($DatetimeCrea) {
        $this->DatetimeCrea = $DatetimeCrea;
    }

    public function setDatetimeStartExec($DatetimeStartExec) {
        $this->DatetimeStartExec = $DatetimeStartExec;
    }

    public function setDatetimeEndExec($DatetimeEndExec) {
        $this->DatetimeEndExec = $DatetimeEndExec;
    }

    public function getSchedid() {
        return $this->schedid;
    }

    public function getData() {
        return json_decode($this->data, true);
    }

    public function setSchedid($schedid) {
        $this->schedid = $schedid;
    }

    public function setData($data) {
        $this->data = json_encode($data);
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

}
