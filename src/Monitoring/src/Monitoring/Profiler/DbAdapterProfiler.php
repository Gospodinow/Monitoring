<?php

namespace Monitoring\Profiler;

use Monitoring\Profiler\Interfaces\ProfilerInterface;

use Zend\Db\Adapter\Profiler\ProfilerInterface;

class DbAdapterProfiler implements ProfilerInterface, ProfilerInterface
{
    protected $queries = [];
    
    protected $proxy;
    
    public function setProxy(ProfilerInterface $proxy)
    {
        $this->proxy = $proxy;
    }
    
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * @param string|\Zend\Db\Adapter\StatementContainerInterface $target
     * @return mixed
     */
    public function profilerStart($target)
    {
        $this->queries[] = [
            'start' => microtime(true),
            'end'   => null,
            'sql'   => $target->getSql(),
        ];
        
        if ($this->proxy) {
            call_user_func_array(
                [
                    $this->proxy,
                    'profilerStart'
                ],
                func_get_args()
            );
        }
    }
    public function profilerFinish()
    {
        $this->queries[count($this->queries) - 1]['end'] = microtime(true);
        if ($this->proxy) {
            call_user_func_array(
                [
                    $this->proxy,
                    'profilerFinish'
                ],
                func_get_args()
            );
        }
    }
}