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
    public function indexAction($page)
    {   
        $queueOptions = $this->container->getParameter('jobqueue.adapter.options');
        $uiOptions = $this->container->getParameter('jobqueue.ui.options');

        $historyAdapterClass = $queueOptions['history_adapter_class'];
        $historyAdapter = new $historyAdapterClass($this->container->getParameter('jobqueue.adapter.options'), 
                                                   $this->container->get('doctrine')->getEntityManager());
        $limit = $uiOptions['pagination']['limit'];
        $offset = ($limit * $page) - $page;
        $history = new History($historyAdapter, $limit, $offset);
        $pages = floor($history->getTotal() / $limit);
        $route = $this->getRequest()->attributes->get('_route');

        return $this->render('NineThousandJobqueueBundle:History:index.html.twig', array(
            'history'           => $history,
            'currentPage'       => $page,
            'pages'             => $pages,
            'currentRoute'      => $route,
        ));
    }
    
}


