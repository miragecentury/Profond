<?php

namespace Profondlib\Explorer;

class Dir {

    protected $path;
    protected $name;
    protected $Files;
    protected $Dirs;
    protected $ctr;

    public function __construct($path, $ctr) {
        $this->path = $path;
        $this->ctr = $ctr;
        $this->Dirs = array();
        $this->Files = array();
        $this->name = basename($this->path);
    }

    public function createFile($name) {
        if (!file_exists($this->getPath() . "/" . $name)) {
            touch($this->getPath() . "/" . $name);
            chmod($this->getPath() . "/" . $name, 0775);
            return new File($this->getPath() . "/" . $name, $ctr);
        } else {
            return false;
        }
    }

    public function createDir($name) {
        if (!file_exists($this->getPath() . "/" . $name)) {
            mkdir($this->getPath() . "/" . $name);
            chmod($this->getPath() . "/" . $name, 0775);
            return new Dir($this->getPath() . "/" . $name, $ctr);
        } else {
            return false;
        }
    }

    public function delete() {
        return exec("rm -r " . $this->getPath());
    }

    public function copyTo($path) {
        if (!file_exists($path . '/' . $this->getName())) {

            return exec("cp -r " . $this->getPath() . " " . $path . '/' . $this->getName()) && chmod($path . '/' . $this->getName(), 0775);
        } else {
            return false;
        }
    }

    public function moveTo($path) {
        if (!file_exists($path . '/' . $this->getName())) {
            return exec("mv " . $this->getPath() . " " . $path . '/' . $this->getName()) && chmod($path . '/' . $this->getName(), 0775);
        } else {
            return false;
        }
    }

    public function rename($name) {
        $path = substr($this->path, 0, strlen($this->getPath()) - strlen(basename($this->getPath())));
        rename($this->getPath(), $path . $name);
        //TODO problème CONFIG
    }

    public function getName() {
        return $this->name;
    }

    public function getPath() {
        return $this->path;
    }

    public function explore() {
        $elements = scandir($this->path);
        foreach ($elements as $element) {
            if ($element != "." && $element != "..") {
                if (is_file($this->path . "/" . $element)) {
                    $this->addFile(new File($this->path . "/" . $element, $this->ctr));
                } elseif (is_dir($this->path . "/" . "$element")) {
                    $Dir = new Dir($this->path . "/" . $element, $this->ctr);
                    $this->addDir($Dir);
                    $Dir->explore();
                } else {
                    // ?--?
                }
            }
        }
    }

    private function addDir(Dir $Dir) {
        $this->Dirs[] = $Dir;
    }

    private function addFile(File $File) {
        $this->Files[] = $File;
    }

    private function getFiles() {
        return $this->Files;
    }

    private function getDirs() {
        return $this->Dirs;
    }

    public function getHasArray($projectpath) {
        return array(
            "id" => str_replace("/", "__", str_replace($projectpath, "", $this->getPath())),
            "text" => $this->name,
            "children" => $this->getChildsHasArray($projectpath),
        );
    }

    public function getChildsHasArray($projectpath) {
        $Childs = array();
        foreach ($this->Files as $el) {
            $Childs[] = $el->getHasArray($projectpath);
        }
        foreach ($this->Dirs as $el) {
            $Childs[] = $el->getHasArray($projectpath);
        }
        return $Childs;
    }

    public function findConfigs() {
        foreach ($this->Dirs as $dir) {
            $dir->findConfigs();
        }
        foreach ($this->Files as $file) {
            $file->findConfigs();
        }
    }

    public function uploadIn($file) {
        $return = move_uploaded_file($file['tmp_name'], $this->getPath() . '/' . $file['name']);
        chmod($this->getPath() . '/' . $file['name'], 0775);
        return $return;
    }

}
