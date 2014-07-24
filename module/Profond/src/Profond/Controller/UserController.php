<?php

namespace Profond\Controller;

use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController {

    const needAuth = true;

    public function indexAction() {
        $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
        $User = $auth->getIdentity();
        return new ViewModel(array("User" => $User));
    }

    public function changeinfoAction() {
        return new ViewModel();
    }

    public function changepasswordAction() {
        return new ViewModel();
    }

    public function changeavatarAction() {
        return new ViewModel();
    }

}
