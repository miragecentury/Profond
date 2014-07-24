<?php

namespace Profond\Controller;

use Exception;
use Profond\Form\AddprojectForm;
use Profondlib\ExplorerControl;
use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ProjectController extends AbstractActionController {

    const URL = "http://profond.local/secure/Project/";
    const needAuth = true;

    public function indexAction() {
        $User = parent::getSessionUser();
        return new ViewModel(array("User" => $User, "Projects" => $User->getProjects()->toArray()));
    }

    public function addAction() {
        $form_addProject = new AddprojectForm();
        if ($this->request->getMethod() != "POST") {
            return new ViewModel(array("form_addProject" => $form_addProject->prepare()));
        }
        $form_addProject->setData($this->request->getPOST());
        if ($form_addProject->isValid()) {
            $auth = $this->getServiceLocator()->get("Zend\Authentication\AuthenticationService");
            $user = $auth->getIdentity();
            $project = $this->getServiceLocator()->get("Profond\Service\Project");
            try {
                $project->createProject($form_addProject->get("label")->getValue(), $user);
            } catch (Exception $e) {
                return new ViewModel(array("form_addProject" => $form_addProject->prepare()));
            }
            return $this->redirect()->toRoute('profond/default', array("controller" => "project"));
        } else {
            return new ViewModel(array("form_addProject" => $form_addProject->prepare()));
        }
    }

    public function detailsAction() {
        $JobMapper = $this->getServiceLocator()->get('Profond\Mapper\Job');
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ExecutableMapper = $this->getServiceLocator()->get("Profond\Mapper\Executable");
        $Project = $ProjectMapper->findOne($this->params("id"));
        $User = parent::getSessionUser();
        $Explorer = new ExplorerControl($Project, $this->getServiceLocator());
        $Explorer->explore();
        $ConfigsByTag = $ConfigService->getConfigsOrderByConfig($Project);
        $projectpath = $ProjectService->getRepositoryPath($Project);
        if ($User->getAdmin()) {
            $Executables = $ExecutableMapper->getAll();
        } else {
            $Executables = $ExecutableMapper->getAllNotAdmin();
        }
        $Jobs = $JobMapper->getAllByUser($User);
        return new ViewModel(array("Jobs" => $Jobs, "URLPROFOND_AJAX" => self::URL, "Project" => $Project, "User" => $User, "Explorer" => $Explorer, "ConfigsByTag" => $ConfigsByTag, "projectpath" => $projectpath, "Executables" => $Executables));
    }

    public function cloneAction() {
        
    }

    public function deleteAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $Project = $ProjectMapper->findOne($this->params("id"));
        $User = parent::getSessionUser();
        if (is_a($Project, "Profond\Entity\Project")) {
            if ($Project->getUser()->getId() == $User->getId() || $User->get) {
                if (($message = $ProjectService->deleteProject($Project)) === true) {
                    $view = new ViewModel();
                } else {
                    $view = new ViewModel(array("message" => "Echec de la suppression du Projet. " . $message));
                    $view->setTemplate('profond/index/fail');
                    return $view;
                }
            } else {
                $view = new ViewModel(array("message" => "Echec, vous n'êtes pas le propriétaire du projet."));
                $view->setTemplate('profond/index/fail');
                return $view;
            }
        } else {
            $view = new ViewModel(array("message" => "Echec, le projet n'existe pas."));
            $view->setTemplate('profond/index/fail');
            return $view;
        }
    }

    public function forcereloadconfigAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ConfigService = $this->getServiceLocator()->get("Profond\Service\Config");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $Project = $ProjectMapper->findOne($this->params("id"));
        $User = parent::getSessionUser();
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $ExplorerControl->explore();
        $rawConfig = $ExplorerControl->getRawConfigs();
        $ConfigService->forcereloadconfig($Project, $rawConfig);
        return $this->redirect()->toRoute('profond/projectdetails', array('controller' => 'Project', 'action' => 'details', 'id' => $Project->getId()));
    }

    public function jtreegetAction() {
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $Project = $ProjectMapper->findOne($this->getRequest()->getQuery()["id"]);
        $User = parent::getSessionUser();
        if ($Project->getUser()->getId() == $User->getId()) {
            $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
            $ExplorerControl->explore();
            $json = array(
                "id" => "__",
                "text" => "/",
                "state" => array("opened" => true, "disabled" => false, "selected" => false),
                "children" => $ExplorerControl->getHasArray()
            );
        } else {
            $json = array();
        }
        return new JsonModel($json);
    }

    public function jtreerenameAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $ExplorerControl->explore();
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["id"]));
        if ($Element != false) {
            if ($Element->getName() == $data["old"]) {
                $Element->rename($data["new"]);
                return new JsonModel(array("newid" => str_replace("/", "__", $Element->getPath())));
            } else {
                
            }
        } else {
            return new JsonModel(array("path" => str_replace("__", "/", $data["id"])));
        }
    }

    public function jtreedeleteAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["id"]));
        if ($Element->delete()) {
            return new JsonModel(array("ok" => true));
        } else {
            return new JsonModel(array("ok" => false));
        }
    }

    public function jtreecreatedirAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["parent"]));
        if ($Element->createDir($data["text"]) !== false) {
            return new JsonModel(array("ok" => true));
        } else {
            return new JsonModel(array("ok" => false));
        }
    }

    public function jtreecreatefileAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["parent"]));
        if ($Element->createFile($data["text"]) !== false) {
            return new JsonModel(array("ok" => true));
        } else {
            return new JsonModel(array("ok" => false));
        }
    }

    public function jtreecopyAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["origin_id"]));
        return new JsonModel(array("ok" => $Element->copyTo($projectpath . str_replace("__", "/", $data["parent"]))));
    }

    public function jtreemoveAction() {
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $data = $this->getRequest()->getPost();
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["origin_id"]));
        return new JsonModel(array("ok" => $Element->moveTo($projectpath . str_replace("__", "/", $data["parent"]))));
    }

    public function jtreeunzipAction() {
        return new JsonModel(array('ok' => 'ok'));
    }

    public function jtreeun7zAction() {
        return new JsonModel(array('ok' => 'ok'));
    }

    public function jtreeuploadAction() {
        $data = array_merge($this->getRequest()->getFiles()->toArray(), $this->getRequest()->getPOST()->toArray());
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $Project = $ProjectMapper->findOne($data["project"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["target"]));
        if (is_a($Element, 'Profondlib\Explorer\Dir')) {
            if ($data['target'] == '__') {
                $id = '__' . $data['file']['name'];
            } else {
                $id = $data["target"] . "__" . $data['file']['name'];
            }
            return new JsonModel(array('ok' => $Element->uploadIn($data['file']), 'target' => $data["target"], 'id' => $id, 'text' => $data['file']['name']));
        } else {
            return new JsonModel(array('ok' => false));
        }
    }

    public function jtreedecompressAction() {
        $data = array_merge($this->getRequest()->getFiles()->toArray(), $this->getRequest()->getPOST()->toArray());
        $ProjectService = $this->getServiceLocator()->get("Profond\Service\Project");
        $ProjectMapper = $this->getServiceLocator()->get("Profond\Mapper\Project");
        $Project = $ProjectMapper->findOne($data["projectid"]);
        $ExplorerControl = new ExplorerControl($Project, $this->getServiceLocator());
        $projectpath = $ProjectService->getRepositoryPath($Project);
        $Element = $ExplorerControl->getElementByPath($projectpath . str_replace("__", "/", $data["path"]));
        if (is_a($Element, 'Profondlib\Explorer\File')) {
            return new JsonModel(array('ok' => $Element->decompress($data['direct'])));
        } else {
            return new JsonModel(array('ok' => false));
        }
    }

}
