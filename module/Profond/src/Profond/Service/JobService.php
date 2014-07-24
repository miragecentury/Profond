<?php

namespace Profond\Service;

use DateTime;
use Exception;
use Profond\Entity\Executable;
use Profond\Entity\Job;
use Profond\Entity\Project;
use Profond\Entity\User;
use Profondlib\Lanceur\Adapter\SshAdapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class JobService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function deleteAllJobsByProject(Project $Project) {
        return true;
    }

    public function stopAllJobsByProject(Project $Project) {
        return true;
    }

    public function prepareFileForDownload(Job $Job, $path, $original) {
        if ($original) {
            $pathR = $this->getOriginalPath($Job);
        } else {
            $pathR = $this->getResultPath($Job);
        }
        $path = $pathR . $path;
        if (file_exists($path)) {
            if (is_dir($path)) {
                exec("p7zip " . $path);
                exec("mv " . $path . ".7z ./data/cache/");
                return "./data/cache/" . basename($path) . ".7z";
            } else {
                return $path;
            }
        }
    }

    public function createJob(Project $Project, User $User, Executable $Executable, $Machine, $Data) {
        try {
            $Job = new Job();
            $Job->setProject($Project);
            $Job->setUser($User);
            $Job->setData($Data);
            if (is_a($Machine, "Profond\Entity\Machine")) {
                $Job->setMachine($Machine);
            }
            $Job->setStatus(Job::STATUS_WAITING_DATE);
            $Job->setLabel($Data['%label%']);
            $Job->setDatetimeCrea(new DateTime('now'));
            $Job->setExecutable($Executable);
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Job);
            $em->flush();
            return $Job;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 
     * @param Project $Project
     * @param User $User
     * @param array $Data
     * @return mixed
     */
    public function controlJob(Project $Project, User $User, $Data) {
        $MachineMapper = $this->getServiceLocator()->get('Profond\Mapper\Machine');

//Test des droits
        if (!($Project->getUser()->getId() == $User->getId() || $Project->getShareAll())) {
            return "Le projet ne vous appartient pas.";
        }

        if (!isset($Data['%nbcpu%'])) {
            return "Vous avez oubliez de renseigner le nombre de cpu";
        }
        if (empty($Data['%nbcpu%'])) {
            return "Vous avez oubliez de renseigner le nombre de cpu";
        }

//Test des cpus
        if ($Data['machine'] != 0) {
            $Machine = $MachineMapper->findOne($Data['machine']);
            if (is_a($Machine, 'Profond\Entity\Machine')) {
                if ($Data['%nbcpu%'] >= count($Machine->getCpu())) {
                    return 'La Machine ne dispose pas assez de cpu.';
                }
            } else {
                return 'La Machine que vous avez selectionné n\'existe pas.';
            }
        } else {
// TEST la machine la plus puissante
        }

//test des configs
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $Configs = $ConfigService->getConfigsOrderByConfig($Project);
        $control = true;
        $err_configs = array();
        foreach ($Configs as $key => $config) {
            if (!isset($Data[$key])) {
                $control = false;
                $err_configs[] = $key;
            } else {
                if (empty($Data[$key]) && $Data[$key] != "0") {
                    $control = false;
                    $err_configs[] = $key;
                }
            }
        }
        if (!$control) {
            return 'Les configs n\' ont pas été correctement remplie';
        }

        if (!isset($Data['date'])) {
            return 'Vous avez oubliez de renseigner la date et l\'heure de lancement';
        }

        return true;
    }

    public function getPath(Job $Job) {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $Job->getProject();
        $JobsPath = $ProjectService->getJobsPath($Project);
        return $JobsPath . '/' . $Job->getId();
    }

    public function getOriginalPath(Job $Job) {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $Job->getProject();
        $JobsPath = $ProjectService->getJobsPath($Project);
        return $JobsPath . '/' . $Job->getId() . '/original';
    }

    public function getResultPath(job $Job) {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $Job->getProject();
        $JobsPath = $ProjectService->getJobsPath($Project);
        return $JobsPath . '/' . $Job->getId() . '/resultat';
    }

    public function prepare_local_directory(Job $Job) {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $Job->getProject();
        $JobsPath = $ProjectService->getJobsPath($Project);
        $ProjectPath = $ProjectService->getRepositoryPath($Project);
        @mkdir($JobsPath . '/' . $Job->getId(), 0775);
        @mkdir($JobsPath . '/' . $Job->getId() . '/original', 0775);
        @mkdir($JobsPath . '/' . $Job->getId() . '/resultat', 0775);
        exec("cp -r " . $ProjectPath . "/* " . $JobsPath . '/' . $Job->getId() . '/original/');
        @+chmod($JobsPath . '/' . $Job->getId() . '/original/', 0775);
        $this->applyConfigs($Job);
        return true;
    }

    private function applyConfigs(Job $Job) {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Project = $Job->getProject();
        $ProjectPath = $ProjectService->getRepositoryPath($Project);
        $JobPathOriginal = $this->getOriginalPath($Job);
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $ConfigsByTag = $ConfigService->getConfigsOrderByConfig($Project);
        foreach ($ConfigsByTag as $tag => $configs) {
            foreach ($configs as $config) {
                $originalpath = str_replace($ProjectPath, $JobPathOriginal, $config->getRelpath());
                $content = file_get_contents($originalpath);
                $content = str_replace($tag, $Job->getData()[$tag], $content);
                file_put_contents($originalpath, $content);
            }
        }
    }

    public function prepare_distant_directory(Job $Job) {
        set_time_limit(0);
        $Machine = $Job->getMachine();
        $ssh = new SshAdapter($Machine);
        $ssh->connect();
        exec("p7zip " . $this->getOriginalPath($Job));
        $ssh->send("/root/profondui/jobs/" . $Job->getId() . ".7z", $this->getOriginalPath($Job) . '.7z');
        $stream = $ssh->exec("chmod -R 775 /root/profondui/jobs");
        stream_set_blocking($stream, true);
        fclose($stream);
        $stream = $ssh->exec("chmod +x " . "/root/profondui/jobs/" . $Job->getId() . ".7z");
        stream_set_blocking($stream, true);
        fclose($stream);
        $stream = $ssh->exec("cd /root/profondui/jobs/ && p7zip -d " . $Job->getId() . ".7z >> /dev/null");
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        var_dump(stream_get_contents($errorStream));
        stream_set_blocking($stream, true);
        stream_get_contents($stream);
        fclose($errorStream);
        fclose($stream);
        $ssh->exec("mv /root/profondui/jobs/original /root/profondui/jobs/" . $Job->getId());
        $temp_file = "./data/cache/execJob" . $Job->getId() . ".sh";
        $job_exec = "";
        $job_exec.= "curl -X POST --data 'idjob=" . $Job->getId() . "&key=" . $Job->getMachine()->getKeypass() . "' http://profond.local/startjob " . PHP_EOL;
        $job_exec.= "cd /root/profondui/jobs/" . $Job->getId() . "/" . PHP_EOL;
        $job_exec.= $Job->getExecutable()->getExec() . PHP_EOL;
        $job_exec.= "sleep 60" . PHP_EOL;
        $job_exec.= "curl -X POST --data 'idjob=" . $Job->getId() . "&key=" . $Job->getMachine()->getKeypass() . "' http://profond.local/endjob " . PHP_EOL;
        file_put_contents($temp_file, $job_exec);
        $pathToexec = "/root/profondui/jobs/" . $Job->getId() . "/" . basename($temp_file);
        $ssh->send($pathToexec, $temp_file);
        $ssh->exec("chmod +x " . $pathToexec);
        $cpus = $Machine->getCpu();
        $listcpu = "";
        foreach ($cpus as $key => $value) {
            if ($Job->getId() == $value) {
                if (empty($listcpu)) {
                    $listcpu = $key;
                } else {
                    $listcpu.=',' . $key;
                }
            }
        }
        $path_err_shed = "/root/profondui/system/sched.sh install root";
        $stream3 = $ssh->exec($path_err_shed);
        stream_set_blocking($stream3, true);
        fclose($stream3);
        $launch_cmd = "/root/profondui/system/sched.sh create -d" . $pathToexec . " -c '" . $listcpu . "' &" . PHP_EOL;
        $stream2 = $ssh->exec($launch_cmd);
        stream_set_blocking($stream2, true);
        $return_sched = stream_get_contents($stream2);
        fclose($stream2);



        return true;
    }

    public function startjob(Job $Job) {
        $Job->setStatus(Job::STATUS_RUNNING);
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->persist($Job);
        $em->flush();
    }

    public function endjob(Job $Job) {
        $Job->setStatus(Job::STATUS_RECEIVE_RESULT);
        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->persist($Job);
        $em->flush();

        //Récupération des données

        $Machine = $Job->getMachine();
        $ssh = new SshAdapter($Machine);
        $ssh->connect();
        $stream = $ssh->exec("cd /root/profondui/jobs/ && rm " . $Job->getId() . ".7z >> /dev/null");
        stream_set_blocking($stream, true);
        fclose($stream);
        $stream = $ssh->exec("cd /root/profondui/jobs/ && p7zip " . $Job->getId() . " >> /dev/null");
        stream_set_blocking($stream, true);
        fclose($stream);
        $pathToResultat = $this->getResultPath($Job);
        $ssh->recept("/root/profondui/jobs/" . $Job->getId() . ".7z", $pathToResultat . '/../');
        //TODO ADD CLEANUP
        $ssh->disconnect();
        exec("cd " . $pathToResultat . '/../ && p7zip -d ' . $Job->getId() . ".7z");
        exec("rm -r " . $pathToResultat);
        exec("mv " . $this->getPath($Job) . "/" . $Job->getId() . " ./resultat");

        //Libére CPU
        $Machine = $Job->getMachine();
        $cpus = $Machine->getCpu();
        foreach ($cpus as $key => $value) {
            if ($value == $Job->getId()) {
                $cpus[$key] = 0;
            }
        }
        $Machine->setCpu($cpus);
        $em->persist($Machine);
        $em->flush();

        //Re-Setup Job
        $Job->setStatus(Job::STATUS_END);
        $em->persist($Job);
        $em->flush();
        //Avertissement Utilisateur
    }

    public function stopjob(Job $Job) {
        if ($Job->getStatus() == Job::STATUS_LAUNCHING_REMOTE || $Job->getStatus() == Job::STATUS_RECEIVE_RESULT) {
            //Add Asynch for stop when change status
        } else {

            $Job->setStatus(Job::STATUS_STOP);
            $AsynchService = $this->getServiceLocator()->get("Profond\Service\Asynchtask");
            $AsynchService->deleteAsynch($Job);
        }
    }

    public function deletejob(Job $Job) {
        if ($Job->getStatus() != Job::STATUS_END && $Job->getStatus() != Job::STATUS_ERR && $Job->getStatus() != Job::STATUS_STOP) {
            $this->stopjob($Job);
        }

        $AsynchService = $this->getServiceLocator()->get("Profond\Service\Asynchtask");
        $AsynchService->deleteAsynch($Job);

        $this->removedir($Job);

        $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
        $em->remove($Job);
        $em->flush();
    }

    public function removedir(Job $Job) {
        exec("rmdir -r " . $this->getPath($Job));
        //Remove dir in machine
    }

}
