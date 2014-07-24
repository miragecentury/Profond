<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Asynchtask {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $Datetime;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="text")
     */
    protected $data;

    /** @ORM\Column(type="boolean") */
    protected $inexec = false;

    public function getId() {
        return $this->id;
    }

    public function getDatetime() {
        return $this->Datetime;
    }

    public function getType() {
        return $this->type;
    }

    public function getData() {
        return json_decode($this->data, true);
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDatetime($Datetime) {
        $this->Datetime = $Datetime;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function setData($data) {
        $this->data = json_encode($data);
    }

    public function getInexec() {
        return $this->inexec;
    }

    public function setInexec($inexec) {
        $this->inexec = $inexec;
    }

}
