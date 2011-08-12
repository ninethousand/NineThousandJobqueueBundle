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
use Doctrine\Common\Collections\ArrayCollection;

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
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('NineThousandJobqueueBundle:Job')->findAll();

        return $this->render('NineThousandJobqueueBundle:Job:index.html.twig', array(
            'entities' => $entities
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

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Job:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
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
     * Removes dynamically submitted objects that shouldn't be persisted.
     * @param Symfony\Component\Form\Form $form
     * @param obj $entity
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param Array $collections
     */
    public function sanitizeCollections(Form &$form, &$entity, Request &$request, Array $collections) {
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
                if (empty($submission[$collection])) {
                    $entity->set{ucwords($collection)} = new ArrayCollection();
                }
            }
        }
        $request->request->set($formName, $submission);
        $form->setData($entity);
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
            
            $this->sanitizeCollections($form, $entity, $request, array(
                'params' => array('key','value'),
                'args'   => array('value'),
                'tags'   => array('value'),
            ));
            
            $form->bindRequest($request);

            if ($form->isValid()) {
                $entity->setCreateDate(new \DateTime);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_job_show', array('id' => $entity->getId())));
                
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

        $form   = $this->createForm(new JobType(), $entity, $this->container->getParameter('jobqueue.adapter.options'));
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'action'      => $this->generateUrl('jobqueue_job_update', array('id' => $id)),
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
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_job_edit', array('id' => $id)));
            }
        }

        return $this->render('NineThousandJobqueueBundle:Job:new.html.twig', array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
            'action'      => $this->generateUrl('jobqueue_job_update', array('id' => $id)),
        ));
    }

    /**
     * Deletes a Job entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('NineThousandJobqueueBundle:Job')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Job entity.');
                }

                $em->remove($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_job'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
