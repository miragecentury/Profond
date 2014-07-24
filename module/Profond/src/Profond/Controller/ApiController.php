<?php

namespace Profond\Controller;

use Profond\Entity\Job;
use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ApiController extends AbstractActionController {

    const needAuth = false;

    public function updatejobAction() {
        
    }

    public function asynchAction() {
        set_time_limit(0);
        $this->checkAsynch();
        $this->checkMachine();
        $this->checkJob();
        return new JsonModel(array('ok' => true));
    }

    private function checkAsynch() {
        $this->checkAsynchJobs();
    }

    private function checkAsynchJobs() {
        $AsynchtaskMapper = $this->getServiceLocator()->get("Profond\Mapper\Asynchtask");
        $AsynchtaskService = $this->getServiceLocator()->get("Profond\Service\Asynchtask");
        $tasks = $AsynchtaskMapper->getAllJobsTodo();
        foreach ($tasks as $task) {
            $AsynchtaskService->launchJob($task);
        }
    }

    private function checkMachine() {
        $this->checkMachineInstall();
        $this->checkMachinePing();
    }

    private function checkMachinePing() {
        $MachineService = $this->getServiceLocator()->get("Profond\Service\Machine");
        $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
        $Machines = $MachineMapper->getAll();
        foreach ($Machines as $Machine) {
            $MachineService->pingMachine($Machine);
        }
    }

    private function checkMachineInstall() {
        $MachineService = $this->getServiceLocator()->get("Profond\Service\Machine");
        $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
        $Machines_toInstall = $MachineMapper->MachinestoInstall();
        foreach ($Machines_toInstall as $Machine) {
            $MachineService->installMachine($Machine);
        }
    }

    private function checkJob() {
        
    }

    public function isreadyAction() {
        if ($this->getRequest()->isPOST()) {
            $data = $this->getRequest()->getPOST();
            $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
            $Machine = $MachineMapper->findOne($data['idmachine']);
            var_dump($data);
            var_dump($_POST);
            if (is_a($Machine, "Profond\Entity\Machine")) {
                if ($Machine->getKeypass() == $data["key"]) {
                    $MachineService = $this->getServiceLocator()->get("Profond\Service\Machine");
                    $MachineService->isReady($Machine);
                    return new JsonModel(array("ok" => true));
                } else {
                    return new JsonModel(array("ok" => -1));
                }
            } else {
                return new JsonModel(array("ok" => -2));
            }
        }
        return new JsonModel(array("ok" => -3));
    }

    public function startjobAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        if ($this->getRequest()->isPOST()) {
            $data = $this->getRequest()->getPOST()->toArray();
            if (isset($data['idjob'])) {
                $Job = $JobMapper->findOne($data['idjob']);
                if (is_a($Job, "Profond\Entity\Job")) {
                    $JobService->startjob($Job);
                    return new JsonModel(array("ok" => true));
                } else {
                    return new JsonModel(array("ok" => -1));
                }
            } else {
                return new JsonModel(array("ok" => -2));
            }
        } else {
            return new JsonModel(array("ok" => -3));
        }
    }

    public function endjobAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        if ($this->getRequest()->isPOST()) {
            $data = $this->getRequest()->getPOST()->toArray();
            if (isset($data['idjob'])) {
                $Job = $JobMapper->findOne($data['idjob']);
                if (is_a($Job, "Profond\Entity\Job")) {
                    $JobService->endjob($Job);
                    return new JsonModel(array("ok" => true));
                } else {
                    return new JsonModel(array("ok" => -1));
                }
            } else {
                return new JsonModel(array("ok" => -2));
            }
        } else {
            return new JsonModel(array("ok" => -3));
        }
    }

}
