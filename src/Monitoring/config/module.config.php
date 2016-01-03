<?php
namespace Monitoring;

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return [
    'doctrine' => array(
        'configuration' => array(
            'orm_default' => array(
                'sql_logger' => 'monitoring_sql_logger',
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'monitoring_sql_logger' => 'Monitoring\Profiler\DoctrineProfiler'
        ),
    ),
    'router' => array(
        'routes' => array(
            'monitoring' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/monitoring',
                    'defaults' => array(
                        'controller' => 'Monitoring\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Monitoring\Controller\Index'          => Controller\IndexController::class,
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
];

