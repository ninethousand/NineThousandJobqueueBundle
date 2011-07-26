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

        $pageParam = 'hpage';
        if (!$page = $this->getRequest()->query->get($pageParam)) $page = 1;
        $offset = ($uiOptions['pagination']['limit'] * $page) - $uiOptions['pagination']['limit'];
        $history = new History($historyAdapter, $uiOptions['pagination']['limit'], $offset);
        $pagination = $this->getPagination($page, $uiOptions['pagination'], $history->getTotal(), $pageParam);
                
        return $this->render('NineThousandJobqueueBundle:History:index.html.twig', array(
            'history'           => $history,
            'pagination'        => $pagination,
        ));
    }
    
    public function getPagination($current, $options, $total, $param) {
        
        $pages = array();
        $pCount = floor($total / $options['limit']);
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
        );
    }
    
}


