<?php

namespace Profond\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\I18n\Validator\Alnum;
use Zend\Validator\Between;
use Zend\Validator\NotEmpty;

class AuthentificationForm extends Form {

    public function __construct() {
        parent::__construct();

        $email = new Element\Email("email");
        $password = new Element\Password("password");
        $csrf = new Element\Csrf("token_csrf");
        $this->add($email);
        $this->add($password);
        $this->add($csrf);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add($csrf->getInputSpecification());
        $inputFilter->add($email->getInputSpecification());

        $inputFilter->add(array(
            'name' => 'password',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new Alnum(array('allowWhiteSpace' => true)),
                new Between(array('min' => 0, 'max' => 10)),
                new NotEmpty()
            )
        ));
    }

}
