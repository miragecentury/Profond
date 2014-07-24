<?php

namespace Profond\Service;

use Profond\Entity\Executable;
use Profond\Entity\User;
use Profond\Form\AddexecutableForm;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ExecutableService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function createExecutable(AddexecutableForm $form, User $User) {
        if ($User->getAdmin()) {
            $Executable = new Executable();
            $Executable->setLabel($form->get("label")->getValue());
            $Executable->setExec($form->get("exec")->getValue());
            $Executable->setOnlyAdmin($form->get("only_admin")->getValue());
            $em = $this->getServiceLocator()->get("Doctrine\ORM\EntityManager");
            $em->persist($Executable);
            $em->flush();
            return true;
        } else {
            return "Impossible d'enregistrer l'Exécutable.";
        }
    }

    public function editExecutable() {
        
    }

    public function delExecutable() {
        
    }

}
