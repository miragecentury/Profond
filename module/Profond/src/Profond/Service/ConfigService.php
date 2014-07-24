<?php

namespace Profond\Service;

use Profond\Entity\Config;
use Profond\Entity\Project;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ConfigService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function forcereloadconfig($Project, $rawdata) {
        $MapperConfig = $this->getServiceLocator()->get('Profond\Mapper\Config');
        $MapperConfig->deleteAllConfigByProject($Project);

        foreach ($rawdata as $config) {
            $newConfig = new Config();
            $newConfig->setProject($Project);
            $newConfig->setRelpath($config->getFile()->getPath());
            $newConfig->setLine($config->getLine());
            $newConfig->setOrdre($config->getOrder());
            $newConfig->setTag($config->getTag());

            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($newConfig);
            $em->flush();
        }
    }

    public function getConfigsOrderByConfig(Project $Project) {
        $ConfigMapper = $this->getServiceLocator()->get("Profond\Mapper\Config");
        $Configs = $ConfigMapper->getAllByProject($Project);
        $ConfigsByTag = array();
        foreach ($Configs as $Config) {
            if (!isset($ConfigsByTag[$Config->getTag()])) {
                $ConfigsByTag[$Config->getTag()] = array();
            }
            $ConfigsByTag[$Config->getTag()][] = $Config;
        }
        return $ConfigsByTag;
    }

}
