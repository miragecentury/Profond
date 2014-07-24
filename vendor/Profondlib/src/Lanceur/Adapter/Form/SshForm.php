<?php

namespace Profondlib\Lanceur\Adapter\Form;

use Zend\Form\Element\Csrf;
use Zend\Form\Element\Password;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\I18n\Validator\Alnum;
use Zend\Validator\NotEmpty;

class SshForm extends Form {

    public function __construct() {
        parent::__construct();
        $label = new Text("label");

        $csrf = new Csrf("token_csrf");

        $ip = new Text("ip");

        $username = new Text("username");

        $password = new Password("password");

        $this->add($label);
        $this->add($csrf);
        $this->add($ip);
        $this->add($username);
        $this->add($password);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(array(
            'name' => 'label',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new Alnum(array('allowWhiteSpace' => true)),
                //new \Zend\Validator\Between(array('min' => 2, 'max' => 40)),
                new NotEmpty()
            )
        ));

        $inputFilter->add(array(
            'name' => 'ip',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new NotEmpty()
            )
        ));

        $inputFilter->add(array(
            'name' => 'username',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new NotEmpty()
            )
        ));

        $inputFilter->add(array(
            'name' => 'password',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new NotEmpty()
            )
        ));

        $inputFilter->add($csrf->getInputSpecification());
    }

}
