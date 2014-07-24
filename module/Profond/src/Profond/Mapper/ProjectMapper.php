<?php

namespace Profond\Mapper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProjectMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getAll() {
        
    }

    public function findOne($id) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Project");
        $project = $repository->findOneBy(array("id" => $id));
        return $project;
    }

    public function findByLabelByUser($User, $label) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Project");
        $project = $repository->findOneBy(array("user" => $User, 'label' => $label));
    }

    public function findAllShared() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Project");
        $projects = $repository->findBy(array("shared" => true, "sharedAll" => false));
        return $projects;
    }

    public function findAllSharedAll() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Project");
        $projects = $repository->findBy(array("shared" => true, "sharedAll" => true));
        return $projects;
    }

}
