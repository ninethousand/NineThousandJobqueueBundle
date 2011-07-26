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
        ));
        
    }
}
