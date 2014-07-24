<?php

namespace Profond\Controller;

use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class LanceurController extends AbstractActionController {

    const needAuth = true;

    public function prepareAction() {
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $ExecutableMapper = $this->getServiceLocator()->get("Profond\Mapper\Executable");
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $ProjectMapper->findOne($this->params("idproject"));
        $Executable = $ExecutableMapper->findOne($this->params("idexec"));
        if (!(is_a($Project, "Profond\Entity\Project") && is_a($Executable, "Profond\Entity\Executable"))) {
            $view = new ViewModel(array("message" => "Echec, le projet ou l'exécutable n'existe pas."));
            $view->setTemplate('profond/index/fail');
            return $view;
        }
        $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
        $Machines = $MachineMapper->getAll();
        $Configs = $ConfigService->getConfigsOrderByConfig($Project);
        return new ViewModel(array("Machines" => $Machines, "Project" => $Project, "Executable" => $Executable, "Configs" => $Configs, "projectpath" => $ProjectService->getRepositoryPath($Project)));
    }

    public function executeAction() {
        if ($this->getRequest()->isPOST()) {
            $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
            $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
            $ExecutableMapper = $this->getServiceLocator()->get("Profond\Mapper\Executable");
            $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
            $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
            $Project = $ProjectMapper->findOne($this->params("idproject"));
            $Executable = $ExecutableMapper->findOne($this->params("idexec"));
            $Configs = $ConfigService->getConfigsOrderByConfig($Project);
            $Data = $this->getRequest()->getPOST()->toArray();
            $JobService = $this->getServiceLocator()->get('Profond\Service\Job');
            $AsynchtaskService = $this->getServiceLocator()->get("Profond\Service\Asynchtask");
            $User = parent::getSessionUser();
            if (($message = $JobService->controlJob($Project, $User, $Data)) === true) {
                $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
                if ($Data['machine'] != "0") {
                    $Machine = $MachineMapper->findOne($Data['machine']);
                } else {
                    $Machine = null;
                }
                $Job = $JobService->createJob($Project, $User, $Executable, $Machine, $Data);
                if (is_a($Job, "Profond\Entity\Job")) {
                    if (($message = $AsynchtaskService->prepareJob($Project, $User, $Executable, $Job, $Data)) === true) {
                        return $this->redirect()->toRoute("profond");
                    } else {
                        $view = new ViewModel(array("message" => "Echec de la suppression du Projet [ASYNCHTASK]. " . $message));
                        $view->setTemplate('profond/index/fail');
                        return $view;
                    }
                } else {
                    var_dump($Job);
                    die();
                    $view = new ViewModel(array("message" => "Echec de la suppression du Projet. [JOB] " . $message));
                    $view->setTemplate('profond/index/fail');
                    return $view;
                }
            } else {
                $view = new ViewModel(array("message" => "Echec de lancement. " . $message));
                $view->setTemplate('profond/index/fail');
                return $view;
            }
        } else {
            return $this->redirect()->toRoute("profond");
        }
    }

}
