# Doctrine ODM, for Zend Framework 1.11.X

Use Doctrine ODM beta 3 right now in Zend Framework

## Features
### What it does

- Uses a fully configurable Zend_Application resource plugin
- Uses Zend Framework autoloader instead of Doctrine's
- Caching via a Zend Cache Core extension instead of Doctrine's cache, you can use your usual backends
- You can use a cache defined in Zend App's CacheManager resource plugin
- Works with models dispatched in modules (if you use the Annotation driver for mapping description)

### What it doesn't (yet)

- It's currently not possible to implement the Doctrine's event manager just by configuration. You have to code a bit.
- It's currently not possible to implement via configuration a custom mapping driver, you have to stick with Annotation, XML, Yaml or Chain

## Configuration in application.ini:

Don't forget to add pluginpath(s) and lib autoloading before resources definition

    pluginpaths.Axiomes\Application\Resource\ = APPLICATION_PATH "/../library/Axiomes/Application/Resource/"

    autoloadernamespaces[] = "Axiomes"
    autoloadernamespaces[] = "Doctrine"
    autoloadernamespaces[] = "Symfony"

You can of course setup the mongoDb connection if the default "localhost" config doesn't suit your needs

    resources.odm.connection.server = ..
    resources.odm.connection.options.connect = false
    --other options are explained on php.net's MongoDB documentation ---

Document Manager's config

    // proxies and hydrators
    resources.odm.configuration.proxyNamespace = "Proxies"
    resources.odm.configuration.proxyDir = "/path/to/proxies"
    resources.odm.configuration.autoGenerateProxyClasses = 0
    resources.odm.configuration.hydratorNamespace = "Hydrators"
    resources.odm.configuration.hydratorDir = "/path/to/proxies"
    resources.odm.configuration.autoGenerateHydratorClasses = 0

Mapping drivers

    //annotation driver
    resources.odm.configuration.metadataDriverImpl.type = "annotation"
    resources.odm.configuration.metadataDriverImpl.readerParams.defaultAnnotationNamespace = "Doctrine\ODM\MongoDB\Mapping\"
    resources.odm.configuration.metadataDriverImpl.path.1 = "/path/to/documents" //optional
    resources.odm.configuration.metadataDriverImpl.path.2 = "/other/path/to/documents" //optional

    //xml or yaml:
    resources.odm.configuration.metadataDriverImpl.type = "xml" //or yaml
    resources.odm.configuration.metadataDriverImpl.path.1 = "/path/to/mappings"
    resources.odm.configuration.metadataDriverImpl.path.2 = "/other/path/to/mappings"
    resources.odm.configuration.metadataDriverImpl.param.fileExtension = ".my.extension" //if you want to overrid defaults

    //driver chain
    resources.odm.configuration.metadataDriverImpl.type = "chain"
    resources.odm.configuration.metadataDriverImpl.drivers.my_namespace.type = //one of the above drivers types
    resources.odm.configuration.metadataDriverImpl.drivers.my_namespace.path = //just configure it as you would do it above
    ;resources.odm.configuration.metadataDriverImpl.drivers.my_second_namespace.type = //and add other drivers...
    ;resources.odm.configuration.metadataDriverImpl.drivers.my_second_namespace.path = ...

    //optional class metadata factory's name override
    resources.odm.configuration.classMetadataFactoryName  = "MyOwnMetadataFactory"//optional

Database naming settings (optional)

    resources.odm.configuration.defaultDB = "myDefaultDB"

Metadata Caching

    //if you use the CacheManager resource plugin, add a Doctrine Compatible cache :
    resources.cacheManager.myMetadataCacheName.frontend.name = "Axiomes_Cache_DoctrineCompatible"
    resources.cacheManager.myMetadataCacheName.frontend.customBackendNaming = true
    --other frontend options and backend options--

    //and just add this line to your doctrine config
    resources.odm.configuration.metadataCacheImpl = "myMetadataCacheName"

    //or you can build directly your own instance :
    resources.odm.configuration.metadataCacheImpl.frontend.name = "Axiomes_Cache_DoctrineCompatible"
    resources.odm.configuration.metadataCacheImpl.customBackendNaming = true
    --other frontend options and backend options--


## Hint for custom documents/repositories paths in modules

Put a module bootstrap in module's root and override it's resource autoloader

    /**
    * I like to have my modules like this :
    *
    * -mymodule
    *    -controllers
    *    -domain
    *       -documents
    *       -entities  //when i also use Doctrine 2 Orm
    *       -repositories
    *    -forms
    *    -services
    *    -views
    *    Bootstrap.php
    */

    class Blog_Bootstrap extends Zend_Application_Module_Bootstrap{

        public function initResourceLoader(){
            $resourceLoader = parent::getResourceLoader();
            $resourceLoader->addResourceTypes(
                array(
                    'documents' => array(
                        'namespace' => 'Document',
                        'path' => 'domain/documents'
                    ),
                    'entities' => array( //if you also use doctrine2 orm
                        'namepsace' => "Entity",
                        'path' => 'domain/entities"
                    ),
                    'repositories' => array(
                        'namespace' => 'Repository',
                        'path' => 'domain/repositories'
                    )
                )
            );
        }
    }

### notes

Feel free to fork, pulls are welcome

