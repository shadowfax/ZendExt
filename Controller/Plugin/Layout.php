<?php

class ZendExt_Controller_Plugin_Layout extends Zend_Controller_Plugin_Abstract
{
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
			$moduleName = $this->getRequest()->getParam('module', null);
			
			if( $moduleName !== null) {
				$config = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption($moduleName);
	        	
	        	if (is_array($config)) {
	        		$config = array_change_key_case($config, CASE_LOWER);
	        		if (isset($config['resources']['layout'])) {
	        			$mvc = Zend_Layout::getMvcInstance();
	        			
	        			$layout = array_change_key_case($config['resources']['layout'], CASE_LOWER);
	        			if (isset($layout['layoutpath'])) {
	        				$mvc->setLayoutPath($layout['layoutpath']);	
	        			}
	        			if (isset($layout['layout'])) {
	        				$mvc->setLayout($layout['layout'], $mvc->isEnabled());
	        			}
	        		}
	        	}
			}
	}
}