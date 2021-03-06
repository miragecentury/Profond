<?php

namespace Profond\Form;

class AddprojectForm extends \Zend\Form\Form {

    public function __construct() {
        parent::__construct();
        $label = new \Zend\Form\Element\Text("label");

        $csrf = new  \Zend\Form\Element\Csrf("token_csrf");

        $this->add($label);
        $this->add($csrf);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add(array(
            'name' => 'label',
            'require' => true,
            'filters' => array(),
            'validators' => array(
                new \Zend\I18n\Validator\Alnum(array('allowWhiteSpace' => true)),
                //new \Zend\Validator\Between(array('min' => 2, 'max' => 40)),
                new \Zend\Validator\NotEmpty()
            )
        ));
        
        $inputFilter->add($csrf->getInputSpecification());
    }

}
