<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NineThousand\Jobqueue\History\StandardHistory as History;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $queueControl = $this->get('jobqueue.control');
        
        return $this->render('NineThousandJobqueueBundle:Default:index.html.twig', array(
            'activeQueue'    => $queueControl->getActiveQueue(),
            'retryQueue'     => $queueControl->getRetryQueue(),
            'scheduleQueue'  => $queueControl->getScheduleQueue(),
            'history'        => $this->getHistory(),
        ));
        
    }
    
    private function getHistory()
    {
        $queueOptions = $this->container->getParameter('jobqueue.adapter.options');
        $historyAdapterClass = $queueOptions['history_adapter_class'];
        $historyAdapter = new $historyAdapterClass($this->container->getParameter('jobqueue.adapter.options'), 
                                                   $this->container->get('doctrine')->getEntityManager());
        return new History($historyAdapter, null, 0);
    }
}
