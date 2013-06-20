<?php
namespace PayrollCalculator;

use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;


class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getConsoleUsage(Console $console)
    {
        return array(
            // Describe available commands
            'outputpaydays FileName.csv'    => 'Show pay days for the remainder of the year',

            // Describe expected parameters
            array( 'FileName.csv',            'A file where you would like your output saved' ),
        );
    }

}
