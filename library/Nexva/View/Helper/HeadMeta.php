<?php
/**
 * 
 * Had to put in a custom view helper to generate property attributes instead of name attributes
 * @author John
 * @see http://stackoverflow.com/questions/4457467/zend-framework-meta-property-integration/4457798#4457798
 */
class Nexva_View_Helper_HeadMeta extends Zend_View_Helper_HeadMeta {

protected $_typeKeys     = array('name', 'http-equiv', 'charset', 'property');

    public function __call($method, $args)
    {
        if (preg_match('/^(?P<action>set|(pre|ap)pend|offsetSet)(?P<type>Name|HttpEquiv|Property)$/', $method, $matches)) {
            $action = $matches['action'];
            $type   = $this->_normalizeType($matches['type']);
            $argc   = count($args);
            $index  = null;

            if ('offsetSet' == $action) {
                if (0 < $argc) {
                    $index = array_shift($args);
                    --$argc;
                }
            }

            if (2 > $argc) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception('Too few arguments provided; requires key value, and content');
                $e->setView($this->view);
                throw $e;
            }

            if (3 > $argc) {
                $args[] = array();
            }

            $item  = $this->createData($type, $args[0], $args[1], $args[2]);

            if ('offsetSet' == $action) {
                return $this->offsetSet($index, $item);
            }

            $this->$action($item);
            return $this;
        }

        return parent::__call($method, $args);
    }

    protected function _normalizeType($type)
    {
        switch ($type) {
            case 'Property':
                return 'property';
            default:
                return parent::_normalizeType($type);
        }
    }

}