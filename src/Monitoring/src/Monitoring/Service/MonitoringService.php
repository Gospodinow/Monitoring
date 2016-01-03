<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitoring\Service;

use Monitoring\Profiler\Interfaces\ProfilerInterface;

class MonitoringService
{
    const FOLDER = 'data/monitoring';
    
    protected static $instance;
    protected $folder;
    protected $params = [];
    protected $profilers = [];
    
    public function __construct()
    {
        $this->folder               = realpath(self::FOLDER);
        $this->params['start']      = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->params['end']        = $_SERVER['REQUEST_TIME_FLOAT'];
        $this->params['post']       = $_POST;
        $this->params['get']        = $_GET;
        $this->params['server']     = $_SERVER;
        $this->params['coockie']    = $_COOKIE;
        $this->params['queries']    = [];
    }

    public function shutdown()
    {
        $this->params['end'] = microtime(true);
        $this->params['loadavg'] = sys_getloadavg();
        $this->params['memory_usage'] = memory_get_usage();
        $this->params['memory_usage_peak'] = memory_get_peak_usage();
        $this->params['ram_usage'] = explode(PHP_EOL, file_get_contents('/proc/meminfo'));
        
        foreach ($this->profilers as $profiler) {
            $this->params['queries'] = array_merge(
                $this->params['queries'],
                $profiler->getQueries()
            );
        }
        
        if (!file_exists($this->folder)) {
            mkdir($this->folder, 0775, true);
        }
        $file = $this->folder . '/data.' . date('Y-m-d-H') . '.log';
        if (!file_exists($file)) {
            touch($file);
            chmod($file, 0775);
        }
        file_put_contents(
            $file,
            json_encode($this->params) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
    
    /**
     * 
     * @param ProfilerInterface $profiler
     * @return \Monitoring\MonitoringService
     */
    public function addProfiler(ProfilerInterface $profiler)
    {
        if (!in_array($profiler, $this->profilers)) {
            $this->profilers[] = $profiler;
        }
        return $this;
    }
    
    /**
     * 
     * @return MonitoringService
     */
    static public function getInstace()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}