<?php

namespace Profondlib\Explorer;

class Config {

    /**
     *
     * @var File
     */
    protected $file;
    protected $tag;
    protected $line;
    protected $order;

    public function __construct(File $file, $tag, $line, $order) {
        $this->file = $file;
        $this->tag = $tag;
        $this->line = $line;
        $this->order = $order;
    }

    /**
     * 
     * @return File
     */
    public function getFile() {
        return $this->file;
    }

    public function getTag() {
        return $this->tag;
    }

    public function getLine() {
        return $this->line;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setTag($tag) {
        $this->tag = $tag;
    }

    public function setLine($line) {
        $this->line = $line;
    }

    public function setOrder($order) {
        $this->order = $order;
    }

}
