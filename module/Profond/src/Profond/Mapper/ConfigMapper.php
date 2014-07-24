<?php

namespace Profond\Mapper;

use Profond\Entity\Project;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ConfigMapper implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function deleteAllConfigByProject(Project $Project) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $configs = $this->getAllByProject($Project);
        foreach ($configs as $config) {
            $em->remove($config);
            $em->flush();
        }
    }

    public function getAllByProject(Project $Project) {
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $repository = $em->getRepository("Profond\Entity\Config");
        $configs = $repository->findBy(array("project" => $Project), array("tag" => "ASC","ordre"=>"ASC"));
        return $configs;
    }

}
