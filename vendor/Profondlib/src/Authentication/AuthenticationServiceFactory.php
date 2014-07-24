<?php

namespace Profondlib\Authentication;

class AuthenticationServiceFactory implements \Zend\ServiceManager\FactoryInterface {

    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        //$storage = $serviceLocator->get("Zend\Session\Storage\SessionArrayStorage");
        $AuthenticationService = new \Zend\Authentication\AuthenticationService();
        return $AuthenticationService;
    }

}
