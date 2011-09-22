<?php
define('BASE_PATH', realpath(dirname(dirname(__FILE__))));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', BASE_PATH . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(BASE_PATH . '/library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$bootstrap = $application->bootstrap()->getBootstrap();

$cli = new \Symfony\Component\Console\Application(
    'Doctrine ODM Command Line Interface',
    \Doctrine\Common\Version::VERSION
);

try{
    $helperSet = array();
    $dm = $bootstrap->getResource('doctrineodm');
    $helperSet['dm'] = new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($dm);
}catch(Exception $e){
    $cli->renderException($e, new \Symfony\Component\Console\Output\ConsoleOutput());
}

$cli->setCatchExceptions(true);
$cli->setHelperSet(new \Symfony\Component\Console\Helper\HelperSet($helperSet));

$cli->addCommands(
  array(
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateHydratorsCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateProxiesCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateRepositoriesCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateDocumentsCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\QueryCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand(),
      new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand()
  )
);

$cli->run();
