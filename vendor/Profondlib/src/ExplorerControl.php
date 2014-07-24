<?php

namespace Profondlib;

use Profond\Entity\Job;
use Profond\Entity\Project;
use Profondlib\Explorer\Config;
use Profondlib\Explorer\Dir;
use Profondlib\Explorer\File;
use Zend\ServiceManager\ServiceManager;

class ExplorerControl {

    protected $Project;
    protected $Configs;
    protected $projectpath;

    /**
     *
     * @var Dir
     */
    protected $Dir;
    protected $ServiceLocator;

    public function __construct(Project $Project, ServiceManager $sl) {
        $this->ServiceLocator = $sl;
        $ProjectService = $this->ServiceLocator->get('Profond\Service\Project');
        $this->Project = $Project;
        $this->Dir = new Dir($ProjectService->getRepositoryPath($Project), $this);
        $this->projectpath = $this->Dir->getPath();
    }

    public function rawGetHasArrayJob(Job $Job, $original) {
        $JobService = $this->ServiceLocator->get("Profond\Service\job");
        if ($original) {
            $path = $JobService->getOriginalPath($Job);
        } else {
            $path = $JobService->getResultPath($Job);
        }
        $this->Dir = new Dir($path, $this);
        $this->explore();
        return $this->Dir->getChildsHasArray($path);
    }

    public function addConfig(Config $config) {
        $this->Configs[] = $config;
    }

    public function explore() {
        $this->Dir->explore();
    }

    public function getRawConfigs() {
        $this->Dir->findConfigs();
        return $this->Configs;
    }

    public function getElementByPath($path) {
        if (file_exists($path)) {
            if (is_dir($path)) {
                return new Dir($path, $this);
            } else {
                return new File($path, $this);
            }
        } else {
            return false;
        }
    }

    public function getHasArray() {
        return $this->Dir->getChildsHasArray($this->projectpath);
    }

}
