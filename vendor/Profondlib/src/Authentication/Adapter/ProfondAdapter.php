<?php

namespace Profondlib\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProfondAdapter implements AdapterInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $salt = "0123456789";
    private $email = null;
    private $password = null;

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *               If authentication cannot be performed
     */
    public function authenticate() {
        if ($this->email == null || $this->password == null) {
            return new Result(Result::FAILURE);
        }
        $UserMapper = $this->getServiceLocator()->get("Profond\Mapper\User");
        $UserMapper->setServiceLocator($this->getServiceLocator());
        $User = $UserMapper->getOneUserByEmail($this->email);
        if ($User != null) {
            if ($this->password == $User->getPassword()) {
                return new Result(Result::SUCCESS, $User);
            } else {
                return new Result(Result::FAILURE_CREDENTIAL_INVALID, null);
            }
        } else {
            return new result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        }
    }

}
