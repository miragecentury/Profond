<?php

namespace Profond\Mapper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UserMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getOneUserByEmail($email) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\User");
        $user = $repository->findOneBy(array("Email" => $email));
        return $user;
    }
    
    public function findOneById($id){
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\User");
        $user = $repository->findOneBy(array("id" => $id));
        return $user;
    }

    public function getAll() {
        
    }

}
