<?php

/**
 * Application bootstrap
 * 
 * Module specific layout
 * 
 * @author Juan Pedro Gonzalez Gutierrez
 *
 */

class ZendExt_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	/**
     * Constructor
     *
     * Ensure FrontController resource is registered
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        parent::__construct($application);
    }
    
    /**
     * Run the application
     *
     * Checks to see that we have a default controller directory. If not, an
     * exception is thrown.
     *
     * If so, it registers the bootstrap with the 'bootstrap' parameter of
     * the front controller, and dispatches the front controller.
     *
     * @return mixed
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function run()
    {
    	// Load the layout action helper
        $front   = $this->getResource('FrontController');
        $front->registerPlugin(new ZendExt_Controller_Plugin_Layout());

        return parent::run();
    }
    
}