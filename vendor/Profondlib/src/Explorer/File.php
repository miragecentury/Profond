<?php

namespace Profondlib\Explorer;

use Exception;
use Profondlib\ExplorerControl;
use Profondlib\Explorer\Config;

class File {

    protected $path;
    protected $name;
    protected $ctr;
    protected $configs;

    public function __construct($path, ExplorerControl $ctr) {
        $this->path = $path;
        $this->ctr = $ctr;
        $this->name = basename($this->path);
    }

    public function edit($content) {
        
    }

    public function copyTo($path) {
        if (!file_exists($path . '/' . $this->getName())) {
            return copy($this->getPath(), $path . '/' . $this->getName()) && chmod($path . '/' . $this->getName(), 0775);
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

    public function delete() {
        return unlink($this->getPath());
    }

    public function rename($name) {
        $path = substr($this->path, 0, strlen($this->getPath()) - strlen(basename($this->getPath())));
        rename($this->getPath(), $path . $name);
        //PROBLEME CONFIG
    }

    public function getConfigs() {
        return $this->configs;
    }

    public function getPath() {
        return $this->path;
    }

    public function getName() {
        return $this->name;
    }

    public function findConfigs() {
        if (
                preg_match("#(.*)zip$#", $this->getPath()) ||
                preg_match("#(.*)7z#", $this->getPath()) ||
                preg_match("#(.*)xls#", $this->getPath()) ||
                preg_match("#(.*)xl#", $this->getPath()) ||
                preg_match("#(.*)pdf#", $this->getPath())) {
            //NO ITS BAD VERY BAD XD
        } else {
            $configs = array();
            try {
                $r = fopen($this->path, "r");
            } catch (Exception $e) {
                
            }
            if (is_resource($r)) {
                $cpt = 0;
                do {
                    $cpt++;
                    $line = fgets($r);
                    $matches = array();
                    preg_match("#(%[a-zA-Z0-9]{2,20}%)#", $line, $matches);
                    if (count($matches) > 0) {
                        $matches = null;
                        preg_match_all("#(%[a-zA-Z0-9]{2,20}%)#", $line, $matches);
                        foreach ($matches[1] as $key => $match) {
                            $this->ctr->addConfig(new Config($this, $match, $cpt, $key));
                            $this->configs[] = new Config($this, $match, $cpt, $key);
                        }
                    }
                } while ($line !== false);

                fclose($r);
            } else {
                
            }
        }
    }

    public function getHasArray($projectpath) {
        return array(
            "id" => str_replace("/", "__", str_replace($projectpath, "", $this->getPath())),
            "text" => $this->name,
            "type" => "file"
        );
    }

    public function decompress($method) {
        if (preg_match("#(.*)7z#", $this->getPath())) {
            $path = substr($this->path, 0, strlen($this->getPath()) - strlen(basename($this->getPath())));
            if ($method) {
                exec("cd " . $path . " && p7zip -d ./" . $this->getName());
                exec("chmod -R 775 " . $path);
                exec("mv " . $path . '' . substr_replace($this->getName(), "", strlen($this->getName()) - 3, 3) . "/* " . $path);
                exec("rm -r " . $path . '' . substr_replace($this->getName(), "", strlen($this->getName()) - 3, 3));
                return true;
            } else {
                exec("cd " . $path . " && p7zip -d ./" . $this->getName());
                exec("chmod -R 775 " . $path);
                return true;
            }
        } elseif (preg_match("#(.*)zip$#", $this->getPath())) {
            $path = substr($this->path, 0, strlen($this->getPath()) - strlen(basename($this->getPath())));
            if ($method) {
                exec("unzip " . $this->getPath() . " -d " . $path);
                exec("chmod -R 775 " . $path);
                exec("mv " . $path . '' . substr_replace($this->getName(), "", strlen($this->getName()) - 4, 4) . "/* " . $path);
                exec("rm -r " . $path . '' . substr_replace($this->getName(), "", strlen($this->getName()) - 4, 4));
                return true;
            } else {
                exec("unzip " . $this->getPath() . " -d " . $path);
                exec("chmod -R 775 " . $path);
                return true;
            }
        } else {
            return false;
        }
    }

}
