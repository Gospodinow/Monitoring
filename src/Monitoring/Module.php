<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitoring;

use Monitoring\Profiler\DbAdapterProfiler;
use Monitoring\Service\MonitoringService;
use Monitoring\Profiler\ProfilerManager;

use Zend\Mvc\MvcEvent;

require_once 'src/Monitoring/Service/MonitoringService.php';
register_shutdown_function([MonitoringService::getInstace(), 'shutdown']);

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $profilerNamager = new ProfilerManager(MonitoringService::getInstace());
        $profilerNamager->setServiceLocator($sm);
        $profilerNamager->setProfilers();
    }

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
}
