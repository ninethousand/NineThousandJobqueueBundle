<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use NineThousand\Bundle\NineThousandJobqueueBundle\Entity\Arg;
use NineThousand\Bundle\NineThousandJobqueueBundle\Form\ArgType;

/**
 * Arg controller.
 *
 */
class ArgController extends Controller
{
    /**
     * Lists all Arg entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('NineThousandJobqueueBundle:Arg')->findAll();

        return $this->render('NineThousandJobqueueBundle:Arg:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Arg entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Arg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Arg entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Arg:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Arg entity.
     *
     */
    public function newAction()
    {
        $entity = new Arg();
        $form   = $this->createForm(new ArgType(), $entity);

        return $this->render('NineThousandJobqueueBundle:Arg:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Arg entity.
     *
     */
    public function createAction()
    {
        $entity  = new Arg();
        $request = $this->getRequest();
        $form    = $this->createForm(new ArgType(), $entity);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_arg_show', array('id' => $entity->getId())));
                
            }
        }

        return $this->render('NineThousandJobqueueBundle:Arg:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Arg entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Arg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Arg entity.');
        }

        $editForm = $this->createForm(new ArgType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('NineThousandJobqueueBundle:Arg:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Arg entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('NineThousandJobqueueBundle:Arg')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Arg entity.');
        }

        $editForm   = $this->createForm(new ArgType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('jobqueue_arg_edit', array('id' => $id)));
            }
        }

        return $this->render('NineThousandJobqueueBundle:Arg:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Arg entity.
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
                $entity = $em->getRepository('NineThousandJobqueueBundle:Arg')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Arg entity.');
                }

                $em->remove($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('jobqueue_arg'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
