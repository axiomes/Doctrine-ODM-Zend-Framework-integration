<?php

namespace Axiomes\Application\Resource;
use Doctrine\MongoDB\Connection,
    Doctrine\ODM\MongoDB\Configuration,
    Doctrine\ODM\MongoDB\Mapping\Driver,
    Doctrine\ODM\MongoDB\DocumentManager;

class Odm extends \Zend_Application_Resource_ResourceAbstract{

    /**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager
	 */
	protected $_documentManager = null;

	/**
	 * @var \Doctrine\ODM\MongoDB\Configuration
	 */
	protected $_configurationInstance = null;

    /**
     * @var \Doctrine\MongoDB\Connection
     */
    protected $_connectionInstance = null;

    /**
     * @var array
     *
     */
    protected $_connection = array();

    /**
     * @var array
     */
    protected $_configuration = array();

    /**
     * Strategy pattern: initialize resource
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function init()
    {
        $configuration = $this->getConfigurationInstance();
        $connection = $this->getConnectionInstance();

        $proxyAutoloader = new \Doctrine\Common\ClassLoader($configuration->getProxyNamespace(), $configuration->getProxyDir());
        $HydratorAutoloader = new \Doctrine\Common\ClassLoader($configuration->getHydratorNamespace(), $configuration->getHydratorDir());

        $zendLoader = \Zend_Loader_Autoloader::getInstance();
        $zendLoader->pushAutoloader(array($proxyAutoloader, 'loadClass'),$configuration->getProxyNamespace());
        $zendLoader->pushAutoloader(array($HydratorAutoloader, 'loadClass'),$configuration->getHydratorNamespace());

        $this->_documentManager = DocumentManager::create($connection, $configuration, new \Doctrine\Common\EventManager());
        \Zend_Registry::set('odm', $this->_documentManager);
        return $this->_documentManager;
    }

    /**
     * @param array $config
     * @return Odm
     */
    public function setConfiguration(array $config){
        $this->_configuration = array_merge_recursive($this->_configuration, $config);
        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration(){
        return $this->_configuration;
    }

    /**
     * @param array $connection
     * @return Odm
     */
    public function setConnection(array $connection){
        $this->_connection = array_merge($this->_connection, $connection);
        return $this;
    }

    /**
     * @return array
     */
    public function getConnection(){
        return $this->_connection;
    }

    /**
     * @return \Doctrine\MongoDB\Connection
     */
    public function getConnectionInstance(){
        if($this->_connectionInstance == null){
            $this->_connectionInstance = $this->_buildConnectionInstance($this->getConnection());
        }
        return $this->_connectionInstance;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Configuration
     */
    public function getConfigurationInstance(){
        if($this->_configurationInstance == null){
            $this->_configurationInstance = $this->_buildConfigurationInstance($this->_configuration);
        }
        return $this->_configurationInstance;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager|null
     */
    public function getDocumentManager(){
        return $this->_documentManager;
    }


    /**
     * @param array $settings
     * @return \Doctrine\ODM\MongoDB\Configuration
     */
    protected function _buildConnectionInstance(array $settings){
        $server = ( isset($settings['server']) and is_string($settings['server']) ) ? $settings['server'] : null;
        $options = ( isset($settings['options']) and is_array($settings['options']) ) ? $settings['options'] : array();
        return new Connection($server, $options);
    }

    /**
     * @param array $settings
     * @throws Exception
     * @return \Doctrine\ODM\MongoDB\Configuration
     */
    protected function _buildConfigurationInstance(array $settings){
        $configuration = new Configuration();
        foreach($settings as $key => $value){
            if($key == 'metadataDriverImpl' or $key == 'metadataCacheImpl'){
                $buildMethod = '_build'.ucfirst($key);
                $value = $this->$buildMethod($value);
            }
            $methodName = 'set'.ucfirst($key);
            if(method_exists($configuration, $methodName)){
                $configuration->$methodName($value);
            }else{
                throw new namespace\Exception('undefined method : Doctrine\ODM\MongoDB\Configuration::'.$methodName);
            }
        }
        return $configuration;
    }

    /**
     * @throws Exception
     * @param  array|\Doctrine\ODM\MongoDB\Mapping\Driver\Driver $options
     * @return \Doctrine\ODM\MongoDB\Mapping\Driver\Driver
     */
    protected function _buildMetadataDriverImpl($options){
        
        if($options instanceof Driver\Driver) return $options;
        if(! is_array($options) ){
            throw new namespace\Exception('incorrect "metadataDriverImpl" config : must be an instance of Driver or an array');
        }
        if( ! isset($options['type']) or ! is_string($options['type'])){
            throw new namespace\Exception('incorrect "metadataDriverImpl" config : "metadataDriverImpl[\'type\']" must be a string');
        }

        $path = isset($options['path']) ? $options['path'] : array();
        switch(strtolower($options['type'])){
            case 'annotation':
                $reader = new \Doctrine\Common\Annotations\AnnotationReader();
                $readerParams = isset($options['readerParams']) ? $options['readerParams'] : false;
                if($readerParams){
                    foreach($readerParams as $key => $value){
                        $methodName = 'set'.ucfirst($key);
                        $reader->$methodName($value);
                    }
                }
                $driver = new Driver\AnnotationDriver($reader, $path);
                break;
            case 'xml':
                $driver = new Driver\XmlDriver($path);
                break;
            case 'yaml':
                $driver = new Driver\YamlDriver($path);
                break;
            case 'chain':
                $driver = new Driver\DriverChain();
                $drivers = $options['drivers'];
                foreach($drivers as $namespace => $config){
                    $driver->addDriver($this->_buildMetadataDriverImpl($config), $namespace);
                }
                break;
            default :
                throw new namespace\Exception('incorrect metadata driver config : "metadataDriverImpl.type" must be "annotation", "xml" or "yaml" ');
        }

        $params = isset($options['params']) ? $options['params'] : false;
        if($params){
            foreach($params as $key => $value){
                $methodName = 'set'.ucfirst($key);
                $driver->$methodName($value);
            }
        }
        
        return $driver;
    }

    /**
     * @throws Exception
     * @param  array|\Doctrine\Common\Cache\Cache $options
     * @return \Doctrine\Common\Cache\Cache
     */
    protected function _buildMetadataCacheImpl($options){
        if($options instanceof \Doctrine\Common\Cache\Cache) return $options;
        if(is_string($options)){
            $bootstrap = $this->getBootstrap();
            if ($bootstrap instanceof \Zend_Application_Bootstrap_ResourceBootstrapper
                && $bootstrap->hasPluginResource('cachemanager')
            ) {
                $cacheManager = $bootstrap->bootstrap('cachemanager')
                    ->getResource('cachemanager');
                if (null !== $cacheManager && $cacheManager->hasCache($options)) {
                    $cache = $cacheManager->getCache($options);
                    if(false == $cache instanceof \Doctrine\Common\Cache\Cache ){
                        throw new namespace\Exception('error in "metadataCacheImpl" config : CacheManager\'s "'.$options.'" cache doesn\'t implement Doctrine\Common\Cache\Cache');
                    }
                    return $cache;
                }else{
                    throw new namespace\Exception('error in "metadataCacheImpl" config : either Bootstrap has no CacheManager resource, or CacheManager doesn\'t contain a cache named "'.$options.'"');
                }
            }else{
                throw new namespace\Exception('error in "metadataCacheImpl" config : Bootstrap has no registered CacheManager');
            }
        }
        if(! is_array($options) ){
            throw new namespace\Exception('incorrect "metadataCacheImpl" config : must be an instance of Doctrine\Common\Cache\Cache, a Zend_Cache_Manager cache name, or a Zend_Cache configuration array');
        }
        $cache = \Zend_Cache::factory(
                $options['frontend']['name'],
                $options['backend']['name'],
                isset($options['frontend']['options']) ? $options['frontend']['options'] : array(),
                isset($options['backend']['options']) ? $options['backend']['options'] : array(),
                isset($options['frontend']['customFrontendNaming']) ? $options['frontend']['customFrontendNaming'] : false,
                isset($options['backend']['customBackendNaming']) ? $options['backend']['customBackendNaming'] : false,
                isset($options['frontendBackendAutoload']) ? $options['frontendBackendAutoload'] : false
            );
        if(false == $cache instanceof \Doctrine\Common\Cache\Cache){
            throw new namespace\Exception('incorrect "metadataCacheImpl" config : frontend must be an instance of Doctrine\Common\Cache\Cache');
        }
        return $cache;
    }
}
