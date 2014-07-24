<?php

namespace Profond\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Config {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Profond\Entity\Project", inversedBy="Configs")
     */
    protected $project;

    /**
     * @ORM\Column(type="string")
     */
    protected $relpath;

    /**
     *  @ORM\Column(type="integer")
     */
    protected $line;

    /**
     *  @ORM\Column(type="integer")
     */
    protected $ordre;

    /**
     * @ORM\Column(type="string")
     */
    protected $tag;

    public function getId() {
        return $this->id;
    }

    public function getProject() {
        return $this->project;
    }

    public function getRelpath() {
        return $this->relpath;
    }

    public function getLine() {
        return $this->line;
    }

    public function getTag() {
        return $this->tag;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setProject($project) {
        $this->project = $project;
    }

    public function setRelpath($relpath) {
        $this->relpath = $relpath;
    }

    public function setLine($line) {
        $this->line = $line;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }

    public function getOrdre() {
        return $this->ordre;
    }

    public function setOrdre($ordre) {
        $this->ordre = $ordre;
    }

}
