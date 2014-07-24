<?php

namespace Profond\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Profond\Entity\User;

class UserService implements ServiceLocatorAwareInterface {

    const BASE_PATH_STORAGE = "./data/storage/users/";

    use ServiceLocatorAwareTrait;

    public function getPath(User $User) {
        if ($this->getPathReady($User)) {
            return UserService::BASE_PATH_STORAGE . $User->getId();
        } else {
            return false;
        }
    }

    public function getPathReady(User $User) {
        if (!is_dir(UserService::BASE_PATH_STORAGE . $User->getId())) {
            return $this->setupPath($User);
        }
        return true;
    }

    public function setupPath(User $User) {
        mkdir(UserService::BASE_PATH_STORAGE . $User->getId());
        mkdir(UserService::BASE_PATH_STORAGE . $User->getId() . "/projects");
        mkdir(UserService::BASE_PATH_STORAGE . $User->getId() . "/cache");
        mkdir(UserService::BASE_PATH_STORAGE . $User->getId() . "/download");
        if (!is_dir(UserService::BASE_PATH_STORAGE . $User->getId())) {
            return false;
        } else {
            return true;
        }
    }

    public function refresh(User $User) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->refresh($User);
    }

    public function sendEmail($contenu) {
        
    }

}
