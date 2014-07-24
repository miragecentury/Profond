<?php

namespace Profond\Mapper;

use Profond\Entity\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class JobMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function findOne($id) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Job");
        $job = $repository->findOneBy(array("id" => $id));
        return $job;
    }

    public function getAll() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Job");
        $jobs = $repository->findAll();
        return $jobs;
    }

    public function getAllByUser(User $User) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Job");
        $jobs = $repository->findBy(array("user" => $User));
        return $jobs;
    }

}
