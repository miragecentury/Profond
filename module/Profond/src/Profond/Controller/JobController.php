<?php

namespace Profond\Controller;

use Profondlib\ExplorerControl;
use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class JobController extends AbstractActionController {

    const needAuth = true;

    public function indexAction() {
        $JobMapper = $this->getServiceLocator()->get('Profond\Mapper\Job');
        $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
        $User = $auth->getIdentity();
        $Jobs = $JobMapper->getAllByUser($User);
        return new ViewModel(array("User" => $User, "Jobs" => $Jobs));
    }

    public function detailsAction() {
        $JobMapper = $this->getServiceLocator()->get('Profond\Mapper\Job');
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $Job = $JobMapper->findOne($this->params('id'));
        $Project = $Job->getProject();
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $ConfigsByTag = $ConfigService->getConfigsOrderByConfig($Project);
        return new ViewModel(array("ConfigsByTag" => $ConfigsByTag, "projectpath" => $projectpath, "Job" => $Job, 'Project' => $Job->getProject(), 'Executable' => $Job->getExecutable()));
    }

    public function jtreeoriginalgetAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        $Job = $JobMapper->findOne($this->getRequest()->getQuery()["id"]);
        if (is_a($Job, "Profond\Entity\Job")) {
            $Explorer = new ExplorerControl($Job->getProject(), $this->getServiceLocator());
            $json = array(
                "id" => "__",
                "text" => "/",
                "state" => array("opened" => true, "disabled" => false, "selected" => false),
                "children" => $Explorer->rawGetHasArrayJob($Job, true)
            );
            return new JsonModel($json);
        } else {
            return new JsonModel(array("ok" => -1));
        };
    }

    public function jtreeoriginaldownloadAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");

        $Job = $JobMapper->findOne($_POST["Job"]);
        $fileName = $JobService->prepareFileForDownload($Job, str_replace('__', '/', $_POST["path"]), true);
        
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen($fileName, 'r'));
        $response->setStatusCode(200);

        $headers = new \Zend\Http\Headers();
        $headers->addHeaderLine('Content-Type', 'whatever your content type is')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="' . basename($fileName) . '"')
                ->addHeaderLine('Content-Length', filesize($fileName));

        $response->setHeaders($headers);
        return $response;
    }

    public function jtreeresultatgetAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        $Job = $JobMapper->findOne($this->getRequest()->getQuery()["id"]);
        if (is_a($Job, "Profond\Entity\Job")) {
            $Explorer = new ExplorerControl($Job->getProject(), $this->getServiceLocator());
            $json = array(
                "id" => "__",
                "text" => "/",
                "state" => array("opened" => true, "disabled" => false, "selected" => false),
                "children" => $Explorer->rawGetHasArrayJob($Job, false)
            );
            return new JsonModel($json);
        } else {
            return new JsonModel(array("ok" => -1));
        };
    }

    public function jtreeresultatdownloadAction() {
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobMapper = $this->getServiceLocator()->get("Profond\Mapper\Job");
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");

        $Job = $JobMapper->findOne($_POST["Job"]);
        $fileName = $JobService->prepareFileForDownload($Job, str_replace("__", "/",$_POST["path"]), false);
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen($fileName, 'r'));
        $response->setStatusCode(200);

        $headers = new \Zend\Http\Headers();
        $headers->addHeaderLine('Content-Type', 'whatever your content type is')
                ->addHeaderLine('Content-Disposition', 'attachment; filename="' . basename($fileName) . '"')
                ->addHeaderLine('Content-Length', filesize($fileName));

        $response->setHeaders($headers);
        return $response;
    }

    public function gettreeAction() {
        
    }

    public function deleteAction() {
        $JobMapper = $this->getServiceLocator()->get('Profond\Mapper\Job');
        $JobService = $this->getServiceLocator()->get("Profond\Service\Job");
        $Job = $JobMapper->findOne($this->params('id'));
        if (is_a($Job, "Profond\Entity\Job")) {
            $JobService->deleteJob($Job);
        }
        return $this->redirect()->toRoute("profond/default", array("controller" => "Job"));
    }

}
