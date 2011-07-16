<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class JobController extends Controller
{
    public function indexAction()
    {
        return $this->render('JobqueueBundle:Default:index.html.twig');
    }
}
