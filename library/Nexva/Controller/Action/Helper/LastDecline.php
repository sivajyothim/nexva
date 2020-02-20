<?php
class Nexva_Controller_Action_Helper_LastDecline
    extends Zend_Controller_Action_Helper_Abstract
{
    
    protected $_namespace = __CLASS__;

    
    protected $_session = null;

   
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        return $this;
    }

    public function getNamespace()
    {
        return $this->_namespace;
    }

  
    public function setSession($session)
    {
        $this->_session = $session;
        return $this;
    }

   
    public function getSession()
    {
        if (null === $this->_session) {
            $this->_session = new Zend_Session_Namespace($this->getNamespace());
        }
        return $this->_session;
    }

    
    protected function _getRedirector()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
    }

    
    public function saveRequestUri($requestUri = '')
    {
        if ('' === $requestUri) {
            $requestUri = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        }
        $this->getSession()->lastRequestUri = $requestUri;

        return $this;
    }

    
    public function hasRequestUri()
    {
        $session = $this->getSession();
        return isset($session->lastRequestUri);
    }

   
    public function getRequestUri()
    {
        $session = $this->getSession();
        if ($this->hasRequestUri()) {
            $lastRequestUri = $session->lastRequestUri;
            unset($session->lastRequestUri);
            return $lastRequestUri;
        } else {
            return null;
        }
    }

   
    public function redirect()
    {
        if (null === ($lastRequestUri = $this->getRequestUri())) {
            $this->_getRedirector()->gotoUrl('/');
        } else {
            $this->_getRedirector()->gotoUrl($lastRequestUri);
        }
    }

   
    public function direct()
    {
        $this->redirect();
    }
}
