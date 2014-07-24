<?php

namespace Profond\Controller;

use Profond\Form\AuthentificationForm;
use Profondlib\Authentication\Adapter\ProfondAdapter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController {

    public function indexAction() {
        if ($this->identity() != null) {
            return $this->redirect()->toRoute('profond');
        }
        $this->layout("layout/layout_login");
        $authentificationForm = new AuthentificationForm();
        return new ViewModel(array('form_login' => $authentificationForm->prepare()));
    }

    public function loginAction() {
        if ($this->request->getMethod() != "POST") {
            $view = new ViewModel();
            $layout = $this->layout();
            $layout->setTemplate("layout/layout_login");
            $view->setTemplate('profond/auth/index');
            return $view;
        }
        if ($this->identity() != null) {
            return $this->redirect()->toRoute('profond');
        }

        $authentificationForm = new AuthentificationForm();
        $authentificationForm->setData($this->request->getPOST());

        if ($authentificationForm->isValid()) {
            $adapter = new ProfondAdapter();
            $adapter->setServiceLocator($this->getServiceLocator());
            $adapter->setEmail($authentificationForm->getData()["email"]);
            $adapter->setPassword($authentificationForm->getData()["password"]);
            $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
            $auth->setAdapter($adapter);
            $auth->authenticate();
            if ($this->identity() != null) {
                $this->redirect()->toRoute("profond");
            } else {
                $view = new ViewModel(array('form_login' => $authentificationForm->prepare(), "error" => "Erreur d'authentification.Vérifier l'email et le password."));
                $layout = $this->layout();
                $layout->setTemplate("layout/layout_login");
                $view->setTemplate('profond/auth/index');
                return $view;
            }
        } else {
            $view = new ViewModel(array('form_login' => $authentificationForm->prepare(), "error" => "Le formatage de l'email ou du password est invalid"));
            $layout = $this->layout();
            $layout->setTemplate("layout/layout_login");
            $view->setTemplate('profond/auth/index');
            return $view;
        }
    }

    public function logoutAction() {
        $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService')->clearIdentity();
        return $this->redirect()->toRoute("root");
    }

}
