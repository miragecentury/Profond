<?php

namespace Profond\Service;

use DateTimeZone;
use Exception;
use Profond\Entity\Asynchtask;
use Profond\Entity\Executable;
use Profond\Entity\Job;
use Profond\Entity\Project;
use Profond\Entity\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class AsynchtaskService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function prepareJob(Project $Project, User $User, Executable $Executable, Job $Job, $Data) {
        try {
            $Asynchtask = new Asynchtask();
            $Asynchtask->setType("launch_job");
            $datetimezone = new DateTimeZone("Europe/Paris");
            $date = \DateTime::createFromFormat("d F Y - G:i", $Data["date"], $datetimezone);
            $Asynchtask->setDatetime($date);
            $Asynchtask->setData(array(
                'User' => $User->getId(),
                'Project' => $Project->getId(),
                'Data' => $Data,
                'Executable' => $Executable->getId(),
                'Job' => $Job->getId(),
            ));
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Asynchtask);
            $em->flush();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function launchJob(Asynchtask $task) {
        $AsynchtaskMapper = $this->getServiceLocator()->get('Profond\Mapper\Asynchtask');
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        $MachineService = $this->getServiceLocator()->get("Profond\Service\Machine");
        $MachineMapper = $this->getServiceLocator()->get("Profond\Mapper\Machine");
        $task = $AsynchtaskMapper->findOne($task->getId());
        if (is_a($task, 'Profond\Entity\Asynchtask')) {
            if (!$task->getInexec()) {
                $task->setInexec(true);
                $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
                $em->persist($task);
                $em->flush();

                $Job = $JobMapper->findOne($task->getData()['Job']);
                $Job->setStatus(Job::STATUs_LAUNCHING);
                $em->persist($Job);
                $em->flush();
                //Prepare_PRODONUI_REP
                $JobService->prepare_local_directory($Job);
                //Get_Machine
                if ($task->getData()['Data']['machine'] != 0) {
                    $Machine = $MachineMapper->findOne($task->getData()['Data']['machine']);
                    if ($MachineService->reserveCpuatMachine($Machine, $task->getData()['Data']['%nbcpu%'], $Job)) {
                        //OK GO TO CALCUL
                        $Job->setStatus(Job::STATUS_LAUNCHING_REMOTE);
                        $Job->setMachine($Machine);
                        $em->persist($Job);
                        $em->flush();
                        $JobService->prepare_distant_directory($Job);
                        return true;
                    } else {
                        //NARF WAIT ...
                        $Job->setStatus(Job::STATUS_WAITING_RESOURCE);
                        $task->setInexec(false);
                        $em->persist($task);
                        $em->persist($Job);
                        $em->flush();
                        return false;
                    }
                } else {
                    $Machine = $MachineService->reserveCpu($task->getData()['%nbcpu%'], $Job);
                    if ($Machine === false) {
                        //NARF WAIT ...
                        $Job->setStatus(Job::STATUS_WAITING_RESOURCE);
                        $em->persist($Job);
                        $task->setInexec(false);
                        $em->persist($task);
                        $em->flush();
                        return false;
                    } elseif (is_a($Machine, "Profond\Entity\Machine")) {
                        //OK GO TO CALCUL
                        $Job->setStatus(Job::STATUS_LAUNCHING_REMOTE);
                        $Job->setMachine($Machine);
                        $em->persist($Job);
                        $em->flush();
                        $JobService->prepare_distant_directory($Job);
                        $JobService->start($Job);
                        return true;
                    } else {
                        //????? WTF
                        $Job->setStatus(Job::STATUS_ERR);
                        $em->persist($Job);
                        $em->flush();
                        return false;
                    }
                }
            }
        }
    }

    public function receptJob(Asynchtask $task) {
        
    }

    public function deleteAsynch(Job $Job) {
        $AsynchtaskMapper = $this->getServiceLocator()->get('Profond\Mapper\Asynchtask');
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $Asynchtasks = $AsynchtaskMapper->getAllByJob($Job);
        foreach ($Asynchtasks as $Asynchtask) {
            if ($Asynchtask->getData()["Job"] == $Job->getId()) {
                $em->remove($Asynchtask);
            }
        }
        $em->flush();
    }

}
