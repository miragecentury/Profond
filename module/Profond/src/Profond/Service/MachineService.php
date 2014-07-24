<?php

namespace Profond\Service;

use DateTime;
use Profond\Entity\Job;
use Profond\Entity\Machine;
use Profond\Entity\User;
use Profondlib\Lanceur\Adapter\SshAdapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class MachineService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getMachines() {
        $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
        $User = $auth->getIdentity();
        if ($User != null) {
            return $User->getMachines();
        } else {
            return null;
        }
    }

    public function isReady(Machine $Machine) {
        $Machine->setReady(true);
        $Machine->setInstallIncomming(false);
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->persist($Machine);
        $em->flush();
    }

    public function addsshMachine($label, $ip, $username, $password, User $User) {
        if ($User->getAdmin()) {
            $configSsh = SshAdapter::formatConfig($ip, $username, $password);
            if (SshAdapter::checkConnection($configSsh)) {
                $Machine = new Machine();
                $Machine->setLabel($label);
                $Machine->setConnectionType("SSH");
                $Machine->setConfig(SshAdapter::formatConfig($ip, $username, $password));
                $Ssh = new SshAdapter($Machine);
                $Ssh->connect();
                $Machine->setCpu(array_fill(0, $Ssh->getNbCPU(), 0));
                $Ssh->disconnect();
                $Machine->setDateCrea(new DateTime("now"));
                $Machine->setLastping(new DateTime("now"));
                $Machine->setReady(false);
                $Machine->setInstallIncomming(false);
                $Machine->setKeypass($this->generateRandomString(20));
                $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
                $em->persist($Machine);
                $em->flush();
            } else {
                return "Impossible de se connecter à la machine.";
            }
        } else {
            return "Vous n'êtes pas administrateur sur l'application. Vous ne pouvez donc ajouter des machines.";
        }
    }

    public function installMachine(Machine $Machine) {
        if (!$Machine->getInstallIncomming()) {
            $Machine->setInstallIncomming(true);
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Machine);
            $em->flush();
            if ($Machine->getConnectionType() == "SSH") {
                $ssh = new SshAdapter($Machine);
                $ssh->connect();
                $ssh->installPROFONDUI();
            }
        }
    }

    public function pingMachine(Machine $Machine) {
        if (SshAdapter::checkConnection($Machine->getConfig())) {
            $Machine->setLastping(new DateTime('now'));
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Machine);
            $em->flush();
        }
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public function reserveCpuatMachine(Machine $Machine, $nbcpu, Job $Job) {
        $MachineMapper = $this->getServiceLocator()->get('Profond\Mapper\Machine');
        $Machine = $MachineMapper->findOne($Machine->getId());
        if (count(array_count_values($Machine->getCpu())[0]) >= $nbcpu) {
            $cpus = $Machine->getCpu();
            $cpt = $nbcpu;
            foreach ($cpus as $key => $value) {
                if ($value == 0 && $cpt != 0) {
                    $cpt--;
                    $cpus[$key] = $Job->getId();
                }
            }
            $Machine->setCpu($cpus);
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Machine);
            $em->flush();
            return true;
        } else {
            false;
        }
    }

    public function reserveCpu($nbcpu, Job $Job) {
        $MachineMapper = $this->getServiceLocator()->get('Profond\Mapper\Machine');
        $Machines = $MachineMapper->MachinesReady();
        $returnMachine = null;
        $cpt = 0;
        do {
            if ($this->reserveCpuatMachine($Machines[0], $nbcpu) == true) {
                $returnMachine = $Machines[0];
            }
            $cpt++;
        } while ($returnMachine == null && $cpt < count($Machines));
        if (is_a($returnMachine, "Profond\Entity\Machine")) {
            return $returnMachine;
        } else {
            return false;
        }
    }

}
