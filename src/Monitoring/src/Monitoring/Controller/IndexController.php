<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Monitoring\Controller;

use Monitoring\Service\MonitoringService;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        
        if ($this->params()->fromQuery('data')) {
            return $this->dataAction();
        }
        
        return new ViewModel();
    }
    
    public function dataAction()
    {
        $data   = [
            'balance' => [],
        ];
        $folder = realpath(MonitoringService::FOLDER);
        $hour   = date('Y-m-d-H');
        $h      = date('H');
        
        $defaultBalance = [
            'count' => 0,
            'time'  => 0,
            'queries_count' => 0,
            'queries_time' => 0,
            'loadavg' => 0,
            'cpu' => 0,
            'memory' => 0,
            'ram_total' => 0,
            'ram_free' => 0,
            'ram_used' => 0,
            'ram_used_php' => 0,
        ];
        
        $files  = scandir($folder, true);
        foreach ($files as $file) {
            if (strpos($file, $hour) === false) {
                continue;
            }

            $handle = fopen($folder . '/' . $file, "r");
            if (!$handle) {
                continue;
            }
            
            while (($line = fgets($handle)) !== false) {
                $array = @json_decode($line, true);
                if (!is_array($array)) {
                    continue;
                }
                
                $date = date('H:i', $array['start']);
                if (!isset($data['balance'][$date])) {
                    $data['balance'][$date] = $defaultBalance;
                }
                
                
                $data['balance'][$date]['count']++;
                $data['balance'][$date]['time']         += $array['end'] - $array['start'];
                $data['balance'][$date]['loadavg']      += $array['loadavg'][0];
                
                $ram = $this->getRamUsage($array);
                
                $data['balance'][$date]['ram_total']     += $ram['total'];
                $data['balance'][$date]['ram_free']      += $ram['free'];
                $data['balance'][$date]['ram_used']      += $ram['used'];
                
                
                $data['balance'][$date]['ram_used_php']  += $array['memory_usage_peak'] / 1048576;
                
                $data['balance'][$date]['queries_count'] += count($array['queries']);
                foreach ($array['queries'] as $query) {
                    $data['balance'][$date]['queries_time'] += $query['end'] - $query['start'];
                }
                
                
            }
            fclose($handle);
        }
        
        $avg = [
            'ram_total',
            'ram_free',
            'ram_used',
            'ram_used_php',
        ];
        foreach ($data['balance'] as &$b) {
            $b['cpu'] = round($b['loadavg'] / $b['count'], 2);
            foreach ($avg as $name) {
                $b[$name] = round($b[$name] / $b['count'], 2);
            }
            
            if ($b['queries_count']) {
                $b['queries_time'] = $b['queries_time'] / $b['queries_count'];
            }
        }
        
        
        for ($i = 0; $i <= 59; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            if (!isset($data['balance']["$h:$i"])) {
                $data['balance']["$h:$i"] = $defaultBalance; 
            }
        }
        
        ksort($data['balance']);
        
        
        return new JsonModel($data);
    }
    
    public function getRamUsage($array)
    {
        $total = (int) trim(explode(':', $array['ram_usage'][0])[1]) / 1024;
        $free  = (int) trim(explode(':', $array['ram_usage'][1])[1]) / 1024;
        
        return [
            'total' => $total,
            'free' => $free,
            'used' => $total - $free
        ];
    }
}
