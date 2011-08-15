<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Job;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Param;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Arg;
use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Tag;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\JobType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Doctrine\ORM\EntityManager;

/**
 * Job controller.
 *
 */
class JobController extends Controller
{
    /**
     * Lists all Job entities.
     *
     */
    public function indexAction()
    {
        $uiOptions = $this->container->getParameter('jobqueue.ui.options');
        $pageParam = 'jpage';
        $query['limit'] = $uiOptions['pagination']['limit'];
        $query['page'] = $this->getRequest()->query->get($pageParam) ?: 1;
        $query['status'] = $this->getRequest()->query->get('status') ?: null;
        $query['scheduled'] = $this->getRequest()->query->get('scheduled') ?: null;
        $query['reverse'] = $this->getRequest()->query->get('reverse') ?: null;
        $query['offset'] = ($query['limit'] * $query['page']) - $query['limit'];
        
        $em = $this->getDoctrine()->getEntityManager();

        $result = $em->getRepository('NineThousandJobqueueBundle:Job')->findAllByQuery($query);

        $pagination = $this->getPagination($query, $uiOptions['pagination'], $result['totalResults'], $pageParam);
        $forms = array();
        foreach ($result['entities'] as $entity) {
            $id = $entity->getId();
            $forms[$id]['deactivate_form'] = $this->createDeactivateForm()->createView();
            $forms[$id]['activate_form'] = $this->createActivateForm()->createView();
            $forms[$id]['retry_form'] = $this->createRetryForm()->createView();
        }
        
        return $this->render('NineThousandJobqueueBundle:Job:index.html.twig', array(
            'entities'    => $result['entities'],
            'forms'       => $forms,
            'pagination'  => $pagination,
        ));
    }

    /**
     * Finds and displays a Job entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $deactivateForm = $this->createDeactivateForm($id);
        $activateForm = $this->createActivateForm($id);
        $retryForm = $this->createRetryForm($id);

        return $this->render('NineThousandJobqueueBundle:Job:show.html.twig', array(
            'entity'          => $entity,
            'deactivate_form' => $deactivateForm->createView(),
            'activate_form'   => $activateForm->createView(),
            'retry_form'      => $retryForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Job entity.
     *
     */
    public function newAction()
    {
        $entity = new Job();
        $entity->setParams(array(new Param()));
        $entity->setArgs(array(new Arg()));
        $entity->setTags(array(new Tag()));
        $form   = $this->createForm(new JobType(), $entity, $this->container->getParameter('jobqueue.adapter.options'));

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'action' => $this->generateUrl('jobqueue_job_create'),
        ));
    }
    
    /**
     * Creates a new Job entity.
     *
     */
    public function createAction()
    {
        $entity = new Job();
        $request = $this->getRequest();
        $form    = $this->createForm(new JobType(), $entity, $this->container->getParameter('jobqueue.adapter.options'));

        if ('POST' === $request->getMethod()) {
            
            $this->sanitizeCollections($form, $request, array(
                'params' => array('key','value'),
                'args'   => array('value'),
                'tags'   => array('value'),
            ));
            
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $this->setCollectionInverse($em, $entity, array(
                    'params' => 'job',
                    'args'   => 'job',
                ));
                $entity->setCreateDate(new \DateTime);
                $entity->setActive(1);
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_job_show', array(
                    'id' => $entity->getId(),
                )));
                
            }
        }

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'action' => $this->generateUrl('jobqueue_job_create'),
        ));
    }

    /**
     * Displays a form to edit an existing Job entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        if (count($entity->getParams()) < 1) {
            $entity->setParams(array(new Param()));
        }
        
        if (count($entity->getArgs()) < 1) {
            $entity->setArgs(array(new Arg()));
        }
        
        if (count($entity->getTags()) < 1) {
            $entity->setTags(array(new Tag()));
        }

        $form   = $this->createForm(new JobType(), $entity, $this->container->getParameter('jobqueue.adapter.options'));
        $deactivateForm = $this->createDeactivateForm();
        $activateForm = $this->createActivateForm();
        $retryForm = $this->createRetryForm();

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity'          => $entity,
            'form'            => $form->createView(),
            'deactivate_form' => $deactivateForm->createView(),
            'activate_form'   => $activateForm->createView(),
            'retry_form'      => $retryForm->createView(),
            'action'          => $this->generateUrl('jobqueue_job_update', array('id' => $id)),
        ));
    }

    /**
     * Edits an existing Job entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);
        
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Job entity.');
        }

        $form   = $this->createForm(new JobType(), $entity, $this->container->getParameter('jobqueue.adapter.options'));
        $deactivateForm = $this->createDeactivateForm();
        $activateForm = $this->createActivateForm();
        $retryForm = $this->createRetryForm();

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $this->sanitizeCollections($form, $request, array(
                'params' => array('key','value'),
                'args'   => array('value'),
                'tags'   => array('value'),
            ));
            
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $this->setCollectionInverse($em, $entity, array(
                    'params' => 'job',
                    'args'   => 'job',
                ));
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_job_edit', array('id' => $id)));
            }
        }

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity'          => $entity,
            'form'            => $form->createView(),
            'deactivate_form' => $deactivateForm->createView(),
            'activate_form'   => $activateForm->createView(),
            'retry_form'      => $retryForm->createView(),
            'action'          => $this->generateUrl('jobqueue_job_update', array('id' => $id)),
        ));
    }

    /**
     * Makes a Job entity inactive.
     *
     */
    public function deactivateAction($id)
    {
        $form = $this->createDeactivateForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Job entity.');
                }
                
                $entity->setActive(0);
                $entity->setRetry(0);
                $entity->setSchedule(NULL);
                $em->persist($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_job_edit', array('id' => $id)));
    }

    private function createDeactivateForm()
    {
        return $this->createFormBuilder()
            ->getForm()
        ;
    }
    
    /**
     * Makes a Job entity active.
     *
     */
    public function activateAction($id)
    {
        $form = $this->createActivateForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Job entity.');
                }
                
                $entity->setActive(1);
                $entity->setStatus(NULL);
                $em->persist($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_job_edit', array('id' => $id)));
    }

    private function createActivateForm()
    {
        return $this->createFormBuilder()
            ->getForm()
        ;
    }
    
    /**
     * Queues a job entity for a single retry.
     *
     */
    public function retryAction($id)
    {
        $form = $this->createRetryForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Job entity.');
                }
                $maxRetries = $entity->getMaxRetries();
                $maxRetries = ($entity->getAttempts() >= $maxRetries) ? ($maxRetries+1) : $maxRetries;
                $entity->setActive(0);
                $entity->setAttempts(0);
                $entity->setRetry(1);
                $entity->setMaxRetries($maxRetries);
                $entity->setStatus('retry');
                $em->persist($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_job_edit', array('id' => $id)));
    }

    private function createRetryForm()
    {
        return $this->createFormBuilder()
            ->getForm()
        ;
    }
    
    /**
     * Removes submitted errant form fields
     * @param Symfony\Component\Form\Form $form
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Array $collections
     */
    public function sanitizeCollections(Form &$form, Request &$request, Array $collections) {
        $formName = $form->getName();
        $submission = $request->request->get($formName);
        foreach ($collections as $collection => $fields) {
            $total = count($submission[$collection])+1;
            for ($i = 0; $i < $total; $i++) {
                foreach ($fields as $field) {
                    if (empty($submission[$collection][$i][$field])) {
                        unset($submission[$collection][$i]);
                        break;
                    }
                }
            }
        }
        $request->request->set($formName, $submission);
    }
    
    /**
     * Populates the inverse of bidirectional collection relationships
     * @param Obj $entity
     * @param Array $collections
     * @param Doctrine\ORM\EntityManager
     */
    public function setCollectionInverse(EntityManager &$em, &$entity, Array $collections) {
        foreach ($collections as $name => $mappedBy) {
            $collection = $entity->{'get' . ucwords($name)}();
            foreach ($collection as $item) {
                $item->{'set' . ucwords($mappedBy)}($entity);
                $em->persist($item);
            }
        }
    }
    
    public function getPagination($query, $options, $total, $param) {
        
        $pages = array();
        $current = $query['page'];
        unset($query['page']);
        unset($query['offset']);
        unset($query['limit']);
        
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
