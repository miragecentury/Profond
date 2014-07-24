<?php

namespace Profond\Mapper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ExecutableMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getAll() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Executable");
        $executables = $repository->findBy(array());
        return $executables;
    }

    public function getAllNotAdmin() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Executable");
        $executables = $repository->findBy(array("OnlyAdmin" => false));
        return $executables;
    }

    public function findOne($id) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Executable");
        $executable = $repository->find($id);
        return $executable;
    }

}
