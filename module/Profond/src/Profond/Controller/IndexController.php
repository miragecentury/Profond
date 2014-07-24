<?php

namespace Profond\Controller;

use Profondlib\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController {

    const needAuth = true;

    public function indexAction() {
        
        return new ViewModel(array("test" => "Ok!"));
    }

}
