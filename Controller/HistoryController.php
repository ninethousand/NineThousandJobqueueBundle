<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NineThousand\Jobqueue\History\StandardHistory as History;

/**
 * History controller.
 *
 */
class HistoryController extends Controller
{
    /**
     * Lists history records.
     *
     */
    public function indexAction()
    {   
        $queueOptions = $this->container->getParameter('jobqueue.adapter.options');
        $uiOptions = $this->container->getParameter('jobqueue.ui.options');

        $historyAdapterClass = $queueOptions['history_adapter_class'];
        $historyAdapter = new $historyAdapterClass($this->container->getParameter('jobqueue.adapter.options'), 
        $this->container->get('doctrine')->getEntityManager());
        $query = array();
        $pageParam = 'hpage';
        $query['page'] = $this->getRequest()->query->get($pageParam) ?: 1;
        $query['reverse'] = $this->getRequest()->query->get('reverse') ?: null;
        $query['job'] = $this->getRequest()->query->get('job') ?: null;
        $offset = ($uiOptions['pagination']['limit'] * $query['page']) - $uiOptions['pagination']['limit'];
        $history = new History($historyAdapter, $uiOptions['pagination']['limit'], $offset, $query['reverse'], $query['job']);
        $pagination = $this->getPagination($query, $uiOptions['pagination'], $history->getTotal(), $pageParam);
                
        return $this->render('NineThousandJobqueueBundle:History:index.html.twig', array(
            'history'           => $history,
            'pagination'        => $pagination,
        ));
    }
    
    public function getPagination($query, $options, $total, $param) {
        
        $pages = array();
        $current = $query['page'];
        unset($query['page']);
        
        $query = ($query = http_build_query($query)) ? '&'.$query : '';
        $pCount = floor($total / $options['limit']);
        if ($remainder = $total % $options['limit']) {
            $pCount++;
        }
        $start = (($n = $current-$options['pages_before']) > 1) ? $n : 1;
        $end = (($m = $current+$options['pages_after']) < $pCount) ? $m : $pCount;
        for ($i=$start;$i<=$end;$i++) {
           array_push($pages, $i);
        }
        
        return array(
            'current'   => $current,
            'pages'     => $pages,
            'last'      => $pCount,
            'param'     => $param,
            'query'     => $query,
        );
    }
    
}


