<?php

namespace Profond\Controller;

use Profondlib\Lanceur\Adapter\Form\SshForm;
use Profondlib\Lanceur\Adapter\SshAdapter;
use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MachineController extends AbstractActionController {

    const needAuth = true;

    public function indexAction() {
        $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
        $Machines = $MachineMapper->getAll();
        return new ViewModel(array("Machines" => $Machines));
    }

    public function addsshAction() {
        $formAddSshMachine = new SshForm();
        if (!$this->getRequest()->isPOST()) {
            return new ViewModel(array("formAddSshMachine" => $formAddSshMachine->prepare()));
        }
        $formAddSshMachine->setData($this->getRequest()->getPOST());
        if (!$formAddSshMachine->isValid()) {
            return new ViewModel(array("formAddSshMachine" => $formAddSshMachine->prepare()));
        }
        $MachineService = $this->getServiceLocator()->get("Profond\Service\Machine");
        if (($message = $MachineService->addsshMachine(
                $formAddSshMachine->get("label")->getValue(), $formAddSshMachine->get("ip")->getValue(), $formAddSshMachine->get("username")->getValue(), $formAddSshMachine->get("password")->getValue(), parent::getSessionUser()
                )) === true) {
            $this->redirect()->toRoute("profond/default", array("controller" => "Machine", "action" => 'index'));
        } else {
            $view = new ViewModel(array("message" => $message));
            $view->setTemplate('profond/index/fail');
            return $view;
        }
    }

    public function addpbsAction() {
        return new ViewModel();
    }

    public function addAction() {
        return new ViewModel();
    }

    public function detailsActions() {
        return new ViewModel();
    }

    public function delaction() {
        return new ViewModel();
    }

    public function modaction() {
        return new ViewModel();
    }

}
