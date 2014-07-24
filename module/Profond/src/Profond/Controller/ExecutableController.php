<?php

namespace Profond\Controller;

use Profond\Form\AddexecutableForm;
use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ExecutableController extends AbstractActionController {

    const needAuth = true;

    public function indexAction() {
        $ExecutableMapper = $this->getServiceLocator()->get("Profond\Mapper\Executable");
        $User = parent::getSessionUser();
        if ($User->getAdmin()) {
            $Executables = $ExecutableMapper->getAll();
        } else {
            $Executables = $ExecutableMapper->getAllNotAdmin();
        }

        return new ViewModel(array("Executables" => $Executables, "User" => $User));
    }

    public function editAction() {
        $User = parent::getSessionUser();
        if ($User->getAdmin()) {
            
        } else {
            $this->redirect()->toRoute("profond/default", array("controller" => "Executable"));
        }
    }

    public function addAction() {
        $User = parent::getSessionUser();
        $ExecutableService = $this->getServiceLocator()->get("Profond\Service\Executable");
        if (!$User->getAdmin()) {
            $this->redirect()->toRoute("profond/default", array("controller" => "Executable"));
        }
        $formAddExecutable = new AddexecutableForm();
        if (!$this->getRequest()->isPost()) {
            return new ViewModel(array("formAddExecutable" => $formAddExecutable->prepare()));
        }
        $formAddExecutable->setData($this->getRequest()->getPOST());
        if ($formAddExecutable->isValid()) {

            if (($message = $ExecutableService->createExecutable($formAddExecutable, $User)) == true) {
                return $this->redirect()->toRoute('profond/default', array("controller" => "Executable"));
            } else {
                $view = new ViewModel(array("message" => $message));
                $view->setTemplate('profond/index/fail');
                return $view;
            }
        } else {
            return new ViewModel(array("formAddExecutable" => $formAddExecutable->prepare()));
        }
    }

    public function delAction() {
        $User = parent::getSessionUser();
        if (!$User->getAdmin()) {
            $this->redirect()->toRoute("profond/default", array("controller" => "Executable"));
        }
    }

}
