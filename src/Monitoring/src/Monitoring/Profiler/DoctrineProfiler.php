<?php

namespace Monitoring\Profiler;

use Monitoring\Profiler\Interfaces\ProfilerInterface;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\DBAL\Logging\DebugStack;

class DoctrineProfiler extends DebugStack implements FactoryInterface, SQLLogger, ProfilerInterface
{
    public $queries = [];
    
    public function createService(ServiceLocatorInterface $sm)
    {
        return $this;
    }

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queries[] = [
            'start'     => microtime(true),
            'end'       => null,
            'sql'       => $sql,
            'params'    => $params
        ];  
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $this->queries[count($this->queries) - 1]['end'] = microtime(true);
    }
    
    public function getQueries()
    {
        return $this->queries;
    }
}
