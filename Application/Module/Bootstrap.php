<?php

/**
 * Module Bootstrap for module specific configuration
 * 
 * @author Juan Pedro Gonzalez Gutierrez
 * @license http://opensource.org/licenses/gpl-license.php
 */

class ZendExt_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
	
	/**
     * Constructor
     *
     * @param Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
		parent::__construct($application);
		$this->init();
    }

    
    /**
     * Initialize
     *
     * @return void
     */
	public function init()
	{
		$this->_loadModuleConfig();
		
		// Layout must be set as global option for plugins to work
		$resources = $this->getOption('resources');
		if (is_array($resources)) {
			$resources = array_change_key_case($resources, CASE_LOWER);
			if (isset($resources['layout'])) {
				$app = $this->getApplication();
				$app->setOptions(
					array(
						strtolower($this->getModuleName()) => array(
							'resources' => array(
								'layout'	=> array_change_key_case($resources['layout'], CASE_LOWER)
							)
						)
					)
				);
			}
		}
	}
	
	/**
     * Ensure resource loader is loaded
     *
     * @return void
     */
    public function initResourceLoader()
    {
    	$moduleLoader = parent::initResourceLoader();
    	
    	if ($moduleLoader === null) {
    		$r    = new ReflectionClass($this);
            $path = $r->getFileName();
            $moduleLoader = new Zend_Application_Module_Autoloader(array(
                'namespace' => $this->getModuleName(),
                'basePath'  => dirname($path),
            ));
            
            $this->setResourceLoader($moduleLoader);
    	}
    	
    	// Add plugin resources ...
        $moduleLoader->addResourceTypes(array(  
			'plugins' => array(  
				'path' 		=> 'controllers/plugins',  
				'namespace'	=> 'Controller_Plugin'  
			)  
		));
		
		return $moduleLoader;
    }
    
	/**
     * Load the module's config
     * 
     * @return Zend_Config
     */
    protected function _loadModuleConfig()
    {
    	$path = null;
    	
    	$options = $this->getApplication()->getOption('resources');
    	$options = array_change_key_case($options, CASE_LOWER);
    	if (isset($options['frontcontroller'])) {
    		$options = array_change_key_case($options['frontcontroller'], CASE_LOWER);
    		if (isset($options['moduledirectory'])) {
    			$path = $options['moduledirectory'];
    		}	
    	}
    	unset($options);
    	
    	if (null === $path) {
    		$path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules';
    	}
    	$path .= DIRECTORY_SEPARATOR . strtolower($this->getModuleName()) . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
        
        if (is_dir($path)) {
	        $cfgdir = new DirectoryIterator($path);
	        $modOptions = $this->getOptions();
	        foreach ($cfgdir as $file) {
	            if ($file->isFile()) {
	                $filename = $file->getFilename();
	                $options = $this->_loadConfig($path . $filename);
	                if (($len = strpos($filename, '.')) !== false) {
	                    $cfgtype = substr($filename, 0, $len);
	                } else {
	                    $cfgtype = $filename;
	                }
	                if (strtolower($cfgtype) == 'module') {
	                    $modOptions = array_merge($modOptions, $options);
	                } elseif (!empty($options)) {
	                    $modOptions['resources'][$cfgtype] = $options;
	                }
	            }
	        }
	        
	        $this->setOptions($modOptions);
        }
    }
    
    /**
     * Load configuration file of options
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
	protected function _loadConfig($file)
    {
    	if (!file_exists($file)) {
    		throw new Zend_Application_Resource_Exception('File \'' . $file . '\'does not exist');
    	}
    	
        $environment = $this->getEnvironment();
        $suffix      = pathinfo($file, PATHINFO_EXTENSION);
        $suffix      = ($suffix === 'dist')
                     ? pathinfo(basename($file, ".$suffix"), PATHINFO_EXTENSION)
                     : $suffix;

		try {
			switch (strtolower($suffix)) {
	            case 'ini':
	                $config = new Zend_Config_Ini($file, $environment);
	                break;
	
	            case 'xml':
	                $config = new Zend_Config_Xml($file, $environment);
	                break;
	
	            case 'json':
	                $config = new Zend_Config_Json($file, $environment);
	                break;
	
	            case 'yaml':
	            case 'yml':
	                $config = new Zend_Config_Yaml($file, $environment);
	                break;
	
	            case 'php':
	            case 'inc':
	                $config = include $file;
	                if (!is_array($config)) {
	                    throw new Zend_Application_Exception('Invalid configuration file provided; PHP file does not return array value');
	                }
	                return $config;
	                break;
	
	            default:
	                throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
	        }
	
	        return $config->toArray();	
		} catch (Exception $e) {
			// Be silent
			return array();
		}
        
    }
}