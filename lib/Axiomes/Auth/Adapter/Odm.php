<?php
namespace Axiomes\Auth\Adapter;
use \Doctrine\ODM\MongoDB\DocumentManager;

class Odm implements \Zend_Auth_Adapter_Interface
{

    /**
     * @var string
     */
    protected $_className;

    /**
     * $_identityField - the field to use as the identity
     *
     * @var string
     */
    protected $_identityField = null;

    /**
     * @var string
     */
    protected $_identityValue = null;

    /**
     * $_credentialField - field to be used as the credentials
     *
     * @var string
     */
    protected $_credentialField = null;

    /**
     * @var string
     */
    protected $_credentialValue = null;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $_documentManager = null;

    /**
     * @var mixed
     */
    protected $_credentialTreatment = null;

    /**
     * Performs an authentication attempt
     *
     * @throws \Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return \Zend_Auth_Result
     */
    public function authenticate()
    {
        if ($this->_credentialTreatment) {
            $this->_credentialValue = call_user_func($this->_credentialTreatment, $this->_credentialValue);
        }
        $user = $this->_documentManager
                ->getRepository($this->_className)
                ->createQueryBuilder()
                ->field($this->_identityField)->equals($this->_identityValue)
                ->field($this->_credentialField)->equals($this->_credentialValue)
                ->getQuery()
                ->getSingleResult();
        if ($user) {
            return new \Zend_Auth_Result(\Zend_Auth_Result::SUCCESS, $user);
        }
        else {
            return new \Zend_Auth_Result(\Zend_Auth_Result::FAILURE, null);
        }

    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * @param string $className
     * @return Odm
     */
    public function setClassName($className)
    {
        $this->_className = $className;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityField()
    {
        return $this->_identityField;
    }

    /**
     * @param string $identityField
     * @return Odm
     */
    public function setIdentityField($identityField)
    {
        $this->_identityField = $identityField;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialField()
    {
        return $this->_credentialField;
    }

    /**
     * @param string $credentialField
     * @return Odm
     */
    public function setCredentialField($credentialField)
    {
        $this->_credentialField = $credentialField;
        return $this;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->_documentManager;
    }

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @return Odm
     */
    public function setDocumentManager( DocumentManager $documentManager)
    {
        $this->_documentManager = $documentManager;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityValue()
    {
        return $this->_identityValue;
    }

    /**
     * @param string $identityValue
     * @return Odm
     */
    public function setIdentityValue($identityValue)
    {
        $this->_identityValue = $identityValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialValue()
    {
        return $this->_credentialValue;
    }

    /**
     * @param string $credentialValue
     * @return Odm
     */
    public function setCredentialValue($credentialValue)
    {
        $this->_credentialValue = $credentialValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredentialTreatment()
    {
        return $this->_credentialTreatment;
    }

    /**
     * @param mixed $credentialTreatment
     * @return Odm
     */
    public function setCredentialTreatment($credentialTreatment)
    {
        if(is_callable($credentialTreatment)) {
            $this->_credentialTreatment = $credentialTreatment;
        }
        else {
            throw new \Zend_Auth_Adapter_Exception($credentialTreatment . ' is not a valid callback !');
        }
        return $this;
    }

}
