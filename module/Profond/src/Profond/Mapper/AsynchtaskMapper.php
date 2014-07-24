<?php

namespace Profond\Mapper;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Profond\Entity\Job;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class AsynchtaskMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function findOne($id) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Asynchtask");
        $task = $repository->find($id);
        return $task;
    }

    public function getAllJobsTodo() {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $qb = $em->createQueryBuilder();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('s')
                ->from('Profond\Entity\Asynchtask', 's')
                ->where('s.Datetime <= :date_to')
                ->setParameter('date_to', new DateTime('now'), Type::DATETIME)
                ->getQuery()
        ;

        $tasks = $query->getResult();
        return $tasks;
    }

    public function getAllByJob(Job $Job) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $result = $em->getRepository("Profond\Entity\Asynchtask")->createQueryBuilder('o')
                ->andWhere('o.data LIKE :product')
                ->setParameter('product', '%"Job":' . $Job->getId() . '%')
                ->getQuery()
                ->getResult();
        return $result;
    }

}
