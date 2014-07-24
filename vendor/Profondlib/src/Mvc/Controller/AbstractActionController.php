<?php

namespace Profondlib\Mvc\Controller;

use Zend\Mvc\Controller as ZController;
use Zend\View\Model\ViewModel;
use Profond\Entity\User;

class AbstractActionController extends ZController\AbstractActionController {

    const needAuth = false;

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        if (static::needAuth) {
            $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
            if (!$auth->hasIdentity()) {
                return $this->redirect()->toRoute("root");
            }
        }
        $return = parent::onDispatch($e);
        $this->setupLayout();
        return $return;
    }

    private function setupLayout() {
        $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");

        $view_header = new ViewModel(array("identity" => $auth->getIdentity()));
        $view_header->setTemplate("layout/header");
        $view_sidebar = new ViewModel();
        $view_sidebar->setTemplate("layout/sidebar");
        $view_quicksidebar = new ViewModel();
        $view_quicksidebar->setTemplate("layout/quicksidebar");
        $this->layout()->addChild($view_header, "template_header");
        $this->layout()->addChild($view_sidebar, "template_sidebar");
        $this->layout()->addChild($view_quicksidebar, "template_quicksidebar");
    }

    /**
     * 
     * @return User
     */
    protected function getSessionUser() {
        $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
        $user_session = $auth->getIdentity();
        $UserMapper = $this->getServiceLocator()->get("Profond\Mapper\User");
        $User = $UserMapper->findOneById($user_session->getId());
        return $User;
    }

}
