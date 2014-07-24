<?php

namespace Profond\Mapper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MachineMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getAll() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Machine");
        $machines = $repository->findAll();
        return $machines;
    }

    public function findOne($id) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Machine");
        $machine = $repository->findOneBy(array("id" => $id));
        return $machine;
    }

    public function MachinestoInstall() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Machine");
        $machines = $repository->findBy(array("ready" => false, "installIncomming" => false));
        return $machines;
    }

    public function MachinesReady() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Machine");
        $machines = $repository->findBy(array("ready" => true, "installIncomming" => false));
        return $machines;
    }

}
