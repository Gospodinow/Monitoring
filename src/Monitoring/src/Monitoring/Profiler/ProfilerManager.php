<?php

namespace Monitoring\Profiler;

use Monitoring\Service\MonitoringService;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ProfilerManager implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    protected $monitoring;
    
    /**
     * 
     * @param MonitoringService $monitoring
     */
    public function __construct(MonitoringService $monitoring)
    {
        $this->monitoring = $monitoring;
    }
    
    public function setProfilers()
    {
        if ($this->getServiceLocator()->has('Zend\Db\Adapter\Adapter')) {
            $db = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $profiler = new DbAdapterProfiler;
            if ($db->getProfiler()) {
                $profiler->setProxy($db->getProfiler());
            }
            $this->monitoring->addProfiler($profiler);
        }
        
        if (class_exists('Doctrine\DBAL\Logging\DebugStack')) {
            $this->monitoring->addProfiler(
                $this->getServiceLocator()->get('monitoring_sql_logger')
            );
        }
    }
}
