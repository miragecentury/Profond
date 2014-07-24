<?php

namespace Profond\Service;

use DateTime;
use Exception;
use Profond\Entity\Project;
use Profond\Entity\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProjectService implements ServiceLocatorAwareInterface {

    const BASE_PATH_PROJECTS_DIR = "/projects/";

    use ServiceLocatorAwareTrait;

    public function createProject($label, User $user) {
        $UserMapper = $this->getServiceLocator()->get("Profond\Mapper\User");
        $UserMapper->setServiceLocator($this->getServiceLocator());
        $User = $UserMapper->findOneById($user->getId());
        //Create Data
        $project = new Project();
        $project->setLabel($label);
        $project->setUser($User);

        $date = new \DateTime("now");
        $project->setDateCrea($date);
        $project->setDateLastMod($date);

        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->persist($project);
        $em->flush();

        //Create Path
        if (!$this->createProjectPath($project)) {
            $em->remove($project);
            $em->flush();
            echo "ERREUR DE CREATION";
            die();
        }
    }

    public function createProjectCopy($Project_source, $label, $user) {
        
    }

    public function deleteProject(Project $Project) {
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        if ($JobService->deleteAllJobsByProject($Project)) {
            $UserService = $this->getServiceLocator()->get("Profond\Service\User");
            $User_Path = $UserService->getPath($Project->getUser());
            $this->rrmdir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId());
            if (!is_dir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId())) {
                $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
                $em->remove($Project);
                $em->flush();
                return true;
            } else {
                return "Impossible de supprimer le répertoire.";
            }
        } else {
            return "Impossible d'arrêter tous les jobs en cours du projet.";
        }
    }

    public function getExplorer(Project $Project) {
        
    }

    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function getMainPath(Project $Project) {
        $UserService = $this->getServiceLocator()->get("Profond\Service\User");
        $User_Path = $UserService->getPath($Project->getUser());
        return $User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId();
    }

    public function getRepositoryPath(Project $Project) {
        $UserService = $this->getServiceLocator()->get("Profond\Service\User");
        $User_Path = $UserService->getPath($Project->getUser());
        return $User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId() . "/repository";
    }

    public function getJobsPath(Project $Project) {
        $UserService = $this->getServiceLocator()->get("Profond\Service\User");
        $User_Path = $UserService->getPath($Project->getUser());
        return $User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId() . "/jobs";
    }

    private function createProjectPath(Project $Project) {
        $UserService = $this->getServiceLocator()->get("Profond\Service\User");
        $User_Path = $UserService->getPath($Project->getUser());
        if ($User_Path != false) {
            mkdir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId());
            mkdir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId() . "/repository");
            mkdir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId() . "/cache");
            mkdir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId() . "/jobs");
            if (!is_dir($User_Path . ProjectService::BASE_PATH_PROJECTS_DIR . $Project->getId())) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function setShare(Project $Project, $bool) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $Project->setShare($bool);
        $em->persist($Project);
        $em->flush();
    }

    public function setShareAll(Project $Project, $bool) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $Project->setShareAll($bool);
        $em->persist($Project);
        $em->flush();
    }

}
