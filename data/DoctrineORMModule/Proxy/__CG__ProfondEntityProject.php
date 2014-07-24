<?php

namespace DoctrineORMModule\Proxy\__CG__\Profond\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Project extends \Profond\Entity\Project implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'id', 'User', 'label', 'share', 'shareAll', 'dateCrea', 'dateLastMod', 'Jobs', 'Configs');
        }

        return array('__isInitialized__', 'id', 'User', 'label', 'share', 'shareAll', 'dateCrea', 'dateLastMod', 'Jobs', 'Configs');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Project $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUser', array());

        return parent::getUser();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLabel', array());

        return parent::getLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getShare()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShare', array());

        return parent::getShare();
    }

    /**
     * {@inheritDoc}
     */
    public function getShareAll()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getShareAll', array());

        return parent::getShareAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getDateCrea()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateCrea', array());

        return parent::getDateCrea();
    }

    /**
     * {@inheritDoc}
     */
    public function getDateLastMod()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDateLastMod', array());

        return parent::getDateLastMod();
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', array($id));

        return parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function setUser(\Profond\Entity\User $user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUser', array($user));

        return parent::setUser($user);
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel($label)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLabel', array($label));

        return parent::setLabel($label);
    }

    /**
     * {@inheritDoc}
     */
    public function setShare($share)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShare', array($share));

        return parent::setShare($share);
    }

    /**
     * {@inheritDoc}
     */
    public function setShareAll($shareAll)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setShareAll', array($shareAll));

        return parent::setShareAll($shareAll);
    }

    /**
     * {@inheritDoc}
     */
    public function setDateCrea($dateCrea)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDateCrea', array($dateCrea));

        return parent::setDateCrea($dateCrea);
    }

    /**
     * {@inheritDoc}
     */
    public function setDateLastMod($dateLastMod)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDateLastMod', array($dateLastMod));

        return parent::setDateLastMod($dateLastMod);
    }

}
